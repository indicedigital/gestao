<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    public const DEFAULT_DAILY_HOURS_GOAL = 8.0;

    public const SECTOR_TECNICO = 'tecnico';

    public const SECTOR_COMERCIAL = 'comercial';

    /** @return array<string, string> */
    public static function sectorLabels(): array
    {
        return [
            self::SECTOR_TECNICO => 'Técnico',
            self::SECTOR_COMERCIAL => 'Comercial',
        ];
    }

    protected $fillable = [
        'company_id',
        'type',
        'sector',
        'name',
        'email',
        'phone',
        'document',
        'position',
        'role',
        'hire_date',
        'dismissal_date',
        'salary',
        'status',
        'address',
        'notes',
        'daily_hours_goal',
        'monthly_hours_goal',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'daily_hours_goal' => 'decimal:2',
        'monthly_hours_goal' => 'decimal:2',
        'hire_date' => 'date',
        'dismissal_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function scopeForOperationalMetrics(Builder $query): Builder
    {
        return $query->where('sector', self::SECTOR_TECNICO);
    }

    public function isTecnico(): bool
    {
        return $this->sector === self::SECTOR_TECNICO;
    }

    public function resolveDailyHoursGoal(): float
    {
        return (float) ($this->daily_hours_goal ?? self::DEFAULT_DAILY_HOURS_GOAL);
    }

    public function resolveMonthlyHoursGoal(int $businessDaysInMonth): float
    {
        if ($this->monthly_hours_goal !== null) {
            return (float) $this->monthly_hours_goal;
        }

        return $businessDaysInMonth * $this->resolveDailyHoursGoal();
    }

    public function dailyProgress(float $hours): int
    {
        $target = $this->resolveDailyHoursGoal();

        if ($target <= 0) {
            return 0;
        }

        return min(100, (int) round(($hours / $target) * 100));
    }

    /**
     * Empresa
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Contratos do funcionário
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Projetos do funcionário
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_employees')
            ->withPivot('role', 'hourly_rate', 'allocated_hours', 'start_date', 'end_date', 'is_active')
            ->withTimestamps();
    }

    /**
     * Contas a pagar (salários)
     */
    public function payables()
    {
        return $this->hasMany(Payable::class);
    }

    /**
     * Custos de projetos
     */
    public function projectCosts()
    {
        return $this->hasMany(ProjectCost::class);
    }
}
