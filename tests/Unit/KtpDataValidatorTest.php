<?php

namespace Tests\Unit;

use App\Services\Ocr\KtpDataValidator;
use PHPUnit\Framework\TestCase;

class KtpDataValidatorTest extends TestCase
{
    private KtpDataValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new KtpDataValidator();
    }

    // ==========================================
    // NIK Validation
    // ==========================================

    public function test_valid_nik_passes(): void
    {
        $errors = $this->validator->validate(['nik' => '6471012345678901']);
        $this->assertArrayNotHasKey('nik', $errors);
    }

    public function test_nik_with_letters_fails(): void
    {
        $errors = $this->validator->validate(['nik' => '6471ABC345678901']);
        $this->assertArrayHasKey('nik', $errors);
    }

    public function test_nik_too_short_fails(): void
    {
        $errors = $this->validator->validate(['nik' => '123456789012345']); // 15 digit
        $this->assertArrayHasKey('nik', $errors);
    }

    public function test_nik_too_long_fails(): void
    {
        $errors = $this->validator->validate(['nik' => '64710123456789012']); // 17 digit
        $this->assertArrayHasKey('nik', $errors);
    }

    public function test_null_nik_passes_validation(): void
    {
        $errors = $this->validator->validate(['nik' => null]);
        $this->assertArrayNotHasKey('nik', $errors);
    }

    // ==========================================
    // Birth Date Validation
    // ==========================================

    public function test_valid_birth_date_passes(): void
    {
        $errors = $this->validator->validate(['birth_date' => '1995-05-12']);
        $this->assertArrayNotHasKey('birth_date', $errors);
    }

    public function test_future_birth_date_fails(): void
    {
        $futureDate = date('Y-m-d', strtotime('+1 year'));
        $errors = $this->validator->validate(['birth_date' => $futureDate]);
        $this->assertArrayHasKey('birth_date', $errors);
    }

    public function test_birth_date_before_1900_fails(): void
    {
        $errors = $this->validator->validate(['birth_date' => '1899-01-01']);
        $this->assertArrayHasKey('birth_date', $errors);
    }

    public function test_invalid_date_string_fails(): void
    {
        $errors = $this->validator->validate(['birth_date' => 'bukan-tanggal']);
        $this->assertArrayHasKey('birth_date', $errors);
    }

    // ==========================================
    // Gender Validation
    // ==========================================

    public function test_male_gender_passes(): void
    {
        $errors = $this->validator->validate(['gender' => 'male']);
        $this->assertArrayNotHasKey('gender', $errors);
    }

    public function test_female_gender_passes(): void
    {
        $errors = $this->validator->validate(['gender' => 'female']);
        $this->assertArrayNotHasKey('gender', $errors);
    }

    public function test_invalid_gender_fails(): void
    {
        $errors = $this->validator->validate(['gender' => 'LAKI-LAKI']); // belum dinormalisasi
        $this->assertArrayHasKey('gender', $errors);
    }

    public function test_null_gender_passes(): void
    {
        $errors = $this->validator->validate(['gender' => null]);
        $this->assertArrayNotHasKey('gender', $errors);
    }

    // ==========================================
    // Minimum Data Check
    // ==========================================

    public function test_has_minimum_data_with_2_fields(): void
    {
        $data = ['nik' => '6471012345678901', 'name' => 'BUDI', 'birth_date' => null, 'gender' => null];
        $this->assertTrue($this->validator->hasMinimumData($data));
    }

    public function test_has_minimum_data_fails_with_1_field(): void
    {
        $data = ['nik' => '6471012345678901', 'name' => null, 'birth_date' => null, 'gender' => null];
        $this->assertFalse($this->validator->hasMinimumData($data));
    }

    public function test_count_filled_fields(): void
    {
        $data = [
            'nik'         => '6471012345678901',
            'name'        => 'BUDI',
            'birth_date'  => null,
            'gender'      => 'male',
            'address'     => null,
        ];
        $this->assertEquals(3, $this->validator->countFilledFields($data));
    }
}
