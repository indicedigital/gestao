<?php

namespace App\Support;

class BrazilianDocument
{
    /**
     * Formata CPF ou CNPJ apenas com dígitos (com máscara brasileira).
     */
    public static function format(?string $document, ?string $documentType = null): string
    {
        if ($document === null || trim($document) === '') {
            return '—';
        }

        $digits = preg_replace('/\D+/', '', $document);
        $type = strtolower((string) $documentType);

        if ($type === 'cpf' || strlen($digits) === 11) {
            if (strlen($digits) === 11) {
                return sprintf(
                    '%s.%s.%s-%s',
                    substr($digits, 0, 3),
                    substr($digits, 3, 3),
                    substr($digits, 6, 3),
                    substr($digits, 9, 2)
                );
            }
        }

        if ($type === 'cnpj' || strlen($digits) === 14) {
            if (strlen($digits) === 14) {
                return sprintf(
                    '%s.%s.%s/%s-%s',
                    substr($digits, 0, 2),
                    substr($digits, 2, 3),
                    substr($digits, 5, 3),
                    substr($digits, 8, 4),
                    substr($digits, 12, 2)
                );
            }
        }

        return trim($document);
    }

    public static function label(?string $documentType): string
    {
        return match (strtolower((string) $documentType)) {
            'cpf' => 'CPF',
            'cnpj' => 'CNPJ',
            default => 'CPF / CNPJ',
        };
    }
}
