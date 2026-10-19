<?php

namespace Tests\Unit;

use App\Support\Csv;
use PHPUnit\Framework\TestCase;

/**
 * REA S-09: proteksi CSV/formula injection (OWASP CSV Injection).
 * Nilai peserta yang diawali karakter formula harus diprefix apostrof.
 */
class CsvSanitizeTest extends TestCase
{
    public function test_formula_prefixes_are_neutralized(): void
    {
        $this->assertSame("'=SUM(A1:A9)", Csv::sanitize('=SUM(A1:A9)'));
        $this->assertSame("'+2+cmd|' /C calc'!A0", Csv::sanitize("+2+cmd|' /C calc'!A0"));
        $this->assertSame("'-2+3+cmd|' /C calc'!A0", Csv::sanitize("-2+3+cmd|' /C calc'!A0"));
        $this->assertSame("'@SUM(1+1)", Csv::sanitize('@SUM(1+1)'));
        $this->assertSame("'\t=1+1", Csv::sanitize("\t=1+1"));
        $this->assertSame("'\r=1+1", Csv::sanitize("\r=1+1"));
    }

    public function test_normal_values_are_unchanged(): void
    {
        $this->assertSame('Budi Santoso', Csv::sanitize('Budi Santoso'));
        $this->assertSame('budi@example.com', Csv::sanitize('budi@example.com'));
        $this->assertSame('42', Csv::sanitize(42));
        $this->assertSame('1990-05-17', Csv::sanitize('1990-05-17'));
        $this->assertSame('10:30', Csv::sanitize('10:30'));
        $this->assertSame('', Csv::sanitize(''));
    }
}
