<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class FiscalExitNote extends Model
{
    protected $fillable = [
        'company_id',
        'receivable_id',
        'receivable_payment_id',
        'receivable_description',
        'client_id',
        'person_type',
        'client_name',
        'client_email',
        'client_phone',
        'document',
        'document_type',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'amount_received',
        'received_date',
        'payment_method',
        'is_issued',
        'issued_at',
        'document_file_path',
        'document_file_original_name',
        'document_file_mime',
        'internal_notes',
    ];

    protected $casts = [
        'amount_received' => 'decimal:2',
        'received_date' => 'date',
        'is_issued' => 'boolean',
        'issued_at' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function receivable(): BelongsTo
    {
        return $this->belongsTo(Receivable::class);
    }

    public function receivablePayment(): BelongsTo
    {
        return $this->belongsTo(ReceivablePayment::class, 'receivable_payment_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeNotIssued($query)
    {
        return $query->where('is_issued', false);
    }

    public function scopeReceivedInMonth($query, int $year, int $month)
    {
        return $query->whereYear('received_date', $year)->whereMonth('received_date', $month);
    }

    public function documentFileUrl(): ?string
    {
        if (! $this->document_file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->document_file_path);
    }

    /**
     * Dados espelhados de conta a receber + cliente + pagamento (para criar ou atualizar NF pendente).
     *
     * @return array<string, mixed>|null
     */
    public static function snapshotAttributesFromReceivablePayment(ReceivablePayment $payment): ?array
    {
        $receivable = $payment->receivable;
        if (! $receivable || ! $receivable->client_id) {
            return null;
        }

        $client = $receivable->client;
        if (! $client) {
            return null;
        }

        return [
            'company_id' => $receivable->company_id,
            'receivable_id' => $receivable->id,
            'receivable_payment_id' => $payment->id,
            'receivable_description' => $receivable->description,
            'client_id' => $client->id,
            'person_type' => $client->type ?? 'pf',
            'client_name' => $client->name,
            'client_email' => $client->email,
            'client_phone' => $client->phone,
            'document' => $client->document,
            'document_type' => $client->document_type,
            'address' => $client->address,
            'city' => $client->city,
            'state' => $client->state,
            'zip_code' => $client->zip_code,
            'country' => $client->country ?? 'Brasil',
            'amount_received' => $payment->amount,
            'received_date' => $payment->paid_date,
            'payment_method' => $payment->payment_method,
        ];
    }

    /**
     * Campos que podem ser sobrescritos na sincronização (não inclui emissão nem observações internas).
     *
     * @return list<string>
     */
    public static function syncableSnapshotKeys(): array
    {
        return [
            'company_id',
            'receivable_id',
            'receivable_description',
            'client_id',
            'person_type',
            'client_name',
            'client_email',
            'client_phone',
            'document',
            'document_type',
            'address',
            'city',
            'state',
            'zip_code',
            'country',
            'amount_received',
            'received_date',
            'payment_method',
        ];
    }

    /**
     * Cria NF de saída pendente para um recebimento registrado em contas a receber.
     */
    public static function createFromReceivablePayment(ReceivablePayment $payment): ?self
    {
        if (self::query()->where('receivable_payment_id', $payment->id)->exists()) {
            return null;
        }

        $attrs = self::snapshotAttributesFromReceivablePayment($payment);
        if ($attrs === null) {
            return null;
        }

        $attrs['is_issued'] = false;

        return self::create($attrs);
    }

    /**
     * Cria NF faltante e atualiza dados das NF ainda não emitidas a partir do cliente / recebimento / pagamento.
     *
     * @return array{created: int, updated: int, skipped_issued: int}
     */
    public static function syncFromReceivablePaymentsForCompany(int $companyId): array
    {
        $created = 0;
        $updated = 0;
        $skippedIssued = 0;

        $syncKeys = self::syncableSnapshotKeys();

        ReceivablePayment::query()
            ->whereHas('receivable', function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->whereNotNull('client_id');
            })
            ->with(['receivable.client', 'fiscalExitNote'])
            ->orderBy('id')
            ->chunkById(200, function ($payments) use (&$created, &$updated, &$skippedIssued, $syncKeys) {
                foreach ($payments as $payment) {
                    $payload = self::snapshotAttributesFromReceivablePayment($payment);
                    if ($payload === null) {
                        continue;
                    }

                    $note = $payment->fiscalExitNote;
                    if (! $note) {
                        if (self::createFromReceivablePayment($payment)) {
                            $created++;
                        }

                        continue;
                    }

                    if ($note->is_issued) {
                        $skippedIssued++;

                        continue;
                    }

                    $note->fill(Arr::only($payload, $syncKeys));
                    if ($note->isDirty()) {
                        $note->save();
                        $updated++;
                    }
                }
            });

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped_issued' => $skippedIssued,
        ];
    }

    public static function countPaymentsPendingSyncForCompany(int $companyId): int
    {
        return ReceivablePayment::query()
            ->whereHas('receivable', function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->whereNotNull('client_id');
            })
            ->whereDoesntHave('fiscalExitNote')
            ->count();
    }
}
