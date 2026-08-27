<?php

namespace Tests\Unit;

use App\Services\Ocr\KtpFieldParser;
use PHPUnit\Framework\TestCase;

class KtpFieldParserTest extends TestCase
{
    private KtpFieldParser $parser;

    protected function setUp(): void
    {
        $this->parser = new KtpFieldParser();
    }

    // ==========================================
    // NIK Tests
    // ==========================================

    public function test_nik_valid_16_digits(): void
    {
        $raw = "NIK : 6471012345678901\nNAMA : BUDI SANTOSO";
        $result = $this->parser->parse($raw);
        $this->assertEquals('6471012345678901', $result['nik']);
    }

    public function test_nik_ocr_error_O_to_0(): void
    {
        // OCR membaca O sebagai O bukan 0
        $raw = "NIK: 647101234567890O\nNAMA: BUDI";
        $result = $this->parser->parse($raw);
        $this->assertEquals('6471012345678900', $result['nik']);
    }

    public function test_nik_ocr_error_I_to_1(): void
    {
        $raw = "NIK: 64710I2345678901\nNAMA: BUDI";
        $result = $this->parser->parse($raw);
        $this->assertEquals('6471012345678901', $result['nik']);
    }

    public function test_nik_ocr_error_l_to_1(): void
    {
        $raw = "NIK: 647101234567890l\nNAMA: BUDI";
        $result = $this->parser->parse($raw);
        $this->assertNotNull($result['nik']);
        $this->assertEquals(16, strlen($result['nik']));
    }

    public function test_nik_too_short_returns_null(): void
    {
        $raw = "NIK: 12345\nNAMA: BUDI";
        $result = $this->parser->parse($raw);
        $this->assertNull($result['nik']);
    }

    public function test_nik_with_letters_not_substituted_returns_null(): void
    {
        $raw = "NIK: ABCDEF1234567890\nNAMA: BUDI";
        $result = $this->parser->parse($raw);
        // Jika tidak bisa dinormalisasi menjadi 16 digit, harus null
        $this->assertTrue($result['nik'] === null || preg_match('/^\d{16}$/', $result['nik']) === 1);
    }

    // ==========================================
    // Tanggal Lahir Tests
    // ==========================================

    public function test_birth_date_format_dash(): void
    {
        $raw = "Tempat/Tgl Lahir : SAMARINDA 12-05-1995";
        $result = $this->parser->parse($raw);
        $this->assertEquals('1995-05-12', $result['birth_date']);
    }

    public function test_birth_date_format_slash(): void
    {
        $raw = "Tempat/Tgl Lahir : SAMARINDA 12/05/1995";
        $result = $this->parser->parse($raw);
        $this->assertEquals('1995-05-12', $result['birth_date']);
    }

    public function test_birth_date_format_dot(): void
    {
        $raw = "Tempat/Tgl Lahir : SAMARINDA 12.05.1995";
        $result = $this->parser->parse($raw);
        $this->assertEquals('1995-05-12', $result['birth_date']);
    }

    public function test_birth_date_invalid_month_returns_null(): void
    {
        $raw = "Tempat/Tgl Lahir : SAMARINDA 12-13-1995"; // bulan 13 tidak valid
        $result = $this->parser->parse($raw);
        $this->assertNull($result['birth_date']);
    }

    public function test_birth_date_year_before_1900_returns_null(): void
    {
        $raw = "Tempat/Tgl Lahir : SAMARINDA 12-05-1800";
        $result = $this->parser->parse($raw);
        $this->assertNull($result['birth_date']);
    }

    // ==========================================
    // Gender Tests
    // ==========================================

    public function test_gender_laki_laki_maps_to_male(): void
    {
        $raw = "JENIS KELAMIN : LAKI-LAKI";
        $result = $this->parser->parse($raw);
        $this->assertEquals('male', $result['gender']);
    }

    public function test_gender_perempuan_maps_to_female(): void
    {
        $raw = "JENIS KELAMIN : PEREMPUAN";
        $result = $this->parser->parse($raw);
        $this->assertEquals('female', $result['gender']);
    }

    public function test_gender_not_found_returns_null(): void
    {
        $raw = "NIK: 1234567890123456\nNAMA: BUDI";
        $result = $this->parser->parse($raw);
        $this->assertNull($result['gender']);
    }

    // ==========================================
    // Field Kosong Tests
    // ==========================================

    public function test_missing_field_returns_null_not_empty_string(): void
    {
        $raw = "NIK: 6471012345678901";
        $result = $this->parser->parse($raw);
        $this->assertNull($result['name']);
        $this->assertNull($result['religion']);
        $this->assertNull($result['occupation']);
    }

    public function test_partial_result_returns_available_fields(): void
    {
        $raw = "NIK: 6471012345678901\nNAMA: BUDI SANTOSO\nJENIS KELAMIN: LAKI-LAKI";
        $result = $this->parser->parse($raw);

        $this->assertEquals('6471012345678901', $result['nik']);
        $this->assertEquals('BUDI SANTOSO', $result['name']);
        $this->assertEquals('male', $result['gender']);
        // Field yang tidak ada tetap null
        $this->assertNull($result['birth_date']);
        $this->assertNull($result['address']);
    }

    // ==========================================
    // RT/RW Tests (sekarang digabung ke address)
    // ==========================================

    public function test_rt_rw_included_in_address(): void
    {
        // RT/RW seharusnya digabungkan ke dalam field address dengan format "RT n RW n"
        $raw = "ALAMAT: JL. MERDEKA NO. 5\nRT/RW: 001/002\nKEL/DESA: CONTOH";
        $result = $this->parser->parse($raw);
        // Tidak ada field rt_rw lagi
        $this->assertArrayNotHasKey('rt_rw', $result);
        // RT/RW seharusnya masuk ke address dalam format "RT 1 RW 2"
        $this->assertNotNull($result['address']);
        $this->assertStringContainsString('RT 1 RW 2', $result['address']);
    }

    public function test_rt_rw_not_in_output_keys(): void
    {
        $raw = "NIK: 6471012345678901";
        $result = $this->parser->parse($raw);
        // Field rt_rw tidak boleh ada di output
        $this->assertArrayNotHasKey('rt_rw', $result);
    }

    // ==========================================
    // Nationality Tests
    // ==========================================

    public function test_nationality_defaults_to_wni(): void
    {
        $raw = "KEWARGANEGARAAN : WNI";
        $result = $this->parser->parse($raw);
        $this->assertEquals('WNI', $result['nationality']);
    }

    public function test_nationality_wna_detected(): void
    {
        $raw = "KEWARGANEGARAAN : WNA";
        $result = $this->parser->parse($raw);
        $this->assertEquals('WNA', $result['nationality']);
    }
}
