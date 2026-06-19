<?php

namespace App\Support;

class DurationFormatter
{
    public const MIN_HOURS = 0.25;

    public const MAX_HOURS = 24.0;

    public const MIN_MINUTES = 15;

    public const MAX_MINUTES = 1440;

    /**
     * Converte valor informado pelo usuário para horas decimais (formato armazenado no banco).
     */
    public static function toHours(float $value, string $unit): float
    {
        if ($unit === 'minutes') {
            return round($value / 60, 2);
        }

        return round($value, 2);
    }

    /**
     * Exibe duração de forma legível: valores abaixo de 1h em minutos; acima, horas e minutos.
     */
    public static function format(float $hours): string
    {
        if ($hours <= 0) {
            return '0 min';
        }

        $totalMinutes = (int) round($hours * 60);

        if ($totalMinutes < 60) {
            return $totalMinutes.' min';
        }

        $wholeHours = intdiv($totalMinutes, 60);
        $remainingMinutes = $totalMinutes % 60;

        if ($remainingMinutes === 0) {
            return $wholeHours.'h';
        }

        return $wholeHours.'h '.$remainingMinutes.' min';
    }

    /**
     * Valida limites após conversão para horas.
     *
     * @return array{valid: bool, message: ?string}
     */
    public static function validateHours(float $hours, string $unit): array
    {
        if ($hours < self::MIN_HOURS) {
            return [
                'valid' => false,
                'message' => $unit === 'minutes'
                    ? 'Informe no mínimo '.self::MIN_MINUTES.' minutos.'
                    : 'Informe no mínimo '.number_format(self::MIN_HOURS, 2, ',', '.').' horas (15 min).',
            ];
        }

        if ($hours > self::MAX_HOURS) {
            return [
                'valid' => false,
                'message' => $unit === 'minutes'
                    ? 'O máximo permitido é '.self::MAX_MINUTES.' minutos (24 h).'
                    : 'O máximo permitido é '.number_format(self::MAX_HOURS, 0, ',', '.').' horas.',
            ];
        }

        return ['valid' => true, 'message' => null];
    }
}
