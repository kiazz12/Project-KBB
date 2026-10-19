<?php

namespace App\Support;

class Csv
{
    /**
     * Amankan nilai sel dari CSV/formula injection (OWASP: CSV Injection).
     *
     * Nilai yang diawali karakter formula (=, +, -, @, TAB, CR) diprefix
     * dengan apostrof agar tidak dieksekusi oleh aplikasi lembar kerja.
     */
    public static function sanitize(mixed $value): string
    {
        $value = (string) $value;

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
