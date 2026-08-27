<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\owner_profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KtpOcrTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();

        $this->user = User::factory()->create([
            'name'   => 'Test User',
            'email'  => 'test@example.com',
            'phone'  => '08123456789',
            'gender' => 'male',
        ]);
    }

    // ==========================================
    // Upload endpoint tests
    // ==========================================

    public function test_unauthenticated_user_cannot_upload_ktp(): void
    {
        $file = UploadedFile::fake()->image('ktp.jpg', 800, 500);

        $response = $this->postJson('/owner/register/ktp-upload', [
            'ktp_photo' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_upload_valid_jpg(): void
    {
        $file = UploadedFile::fake()->image('ktp.jpg', 800, 500);

        $response = $this->actingAs($this->user)
            ->postJson('/owner/register/ktp-upload', [
                'ktp_photo' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'status' => 'processing'])
            ->assertJsonStructure(['job_id']);

        Queue::assertPushed(\App\Jobs\ProcessKtpOcrJob::class);
    }

    public function test_authenticated_user_can_upload_valid_png(): void
    {
        $file = UploadedFile::fake()->image('ktp.png', 800, 500);

        $response = $this->actingAs($this->user)
            ->postJson('/owner/register/ktp-upload', [
                'ktp_photo' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_pdf_file_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson('/owner/register/ktp-upload', [
                'ktp_photo' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ktp_photo']);
    }

    public function test_txt_file_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('ktp.txt', 10, 'text/plain');

        $response = $this->actingAs($this->user)
            ->postJson('/owner/register/ktp-upload', [
                'ktp_photo' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_oversized_file_is_rejected(): void
    {
        // > 5MB
        $file = UploadedFile::fake()->image('ktp.jpg', 5000, 5000)->size(6000);

        $response = $this->actingAs($this->user)
            ->postJson('/owner/register/ktp-upload', [
                'ktp_photo' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ktp_photo']);
    }

    public function test_missing_file_is_rejected(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/owner/register/ktp-upload', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ktp_photo']);
    }

    // ==========================================
    // OCR Status polling tests
    // ==========================================

    public function test_polling_returns_processing_when_job_not_done(): void
    {
        $jobId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $response = $this->actingAs($this->user)
            ->getJson("/owner/register/ocr-status/{$jobId}");

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'status' => 'processing']);
    }

    public function test_polling_returns_completed_when_cache_has_result(): void
    {
        $jobId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        Cache::put("ocr_result_{$jobId}", [
            'status'   => 'completed',
            'data'     => ['nik' => '6471012345678901', 'name' => 'BUDI SANTOSO'],
            'errors'   => [],
            'partial'  => false,
            'ktp_photo_path' => 'owner/ktp_temp/test.jpg',
        ], 600);

        $response = $this->actingAs($this->user)
            ->getJson("/owner/register/ocr-status/{$jobId}");

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'status' => 'completed'])
            ->assertJsonPath('data.nik', '6471012345678901');
    }

    public function test_polling_with_invalid_job_id_format_returns_400(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/owner/register/ocr-status/INVALID-ID-FORMAT!!!');

        $response->assertStatus(400);
    }

    // ==========================================
    // Submit final tests
    // ==========================================

    public function test_store_owner_data_saves_user_and_profile(): void
    {
        // Simpan file temp dulu
        Storage::disk('local')->put('owner/ktp_temp/test-ktp.jpg', 'fake-image-content');

        $response = $this->actingAs($this->user)->post('/owner/register/submit', [
            'name'               => 'Budi Santoso',
            'email'              => 'test@example.com',
            'phone'              => '08123456789',
            'gender'             => 'male',
            'place_of_birth_code' => '6471',
            'date_of_birth'      => '1995-05-12',
            'national_id'        => '6471012345678901',
            'religion'           => 'Islam',
            'marital_status'     => 'Belum Kawin',
            'occupation'         => 'Karyawan Swasta',
            'nationality'        => 'WNI',
            'province_code'      => '64',
            'city_code'          => '6471',
            'district_code'      => '647101',
            'village_code'       => '6471010001',
            'postal_code'        => '75117',
            'address'            => 'JL. CONTOH NO. 10 RT 001 RW 002',
            'ktp_temp_path'      => 'owner/ktp_temp/test-ktp.jpg',
        ]);

        $response->assertRedirect(route('owner.verification'));

        $this->assertDatabaseHas('owner_profiles', [
            'user_id'     => $this->user->id,
            'national_id' => '6471012345678901',
            'religion'    => 'Islam',
            'occupation'  => 'Karyawan Swasta',
            'status'      => 'pending',
        ]);

        $this->assertDatabaseHas('users', [
            'id'   => $this->user->id,
            'name' => 'Budi Santoso',
        ]);
    }

    public function test_store_owner_data_requires_valid_nik(): void
    {
        $response = $this->actingAs($this->user)->post('/owner/register/submit', [
            'name'               => 'Budi',
            'email'              => 'test@example.com',
            'phone'              => '08123456789',
            'gender'             => 'male',
            'place_of_birth_code' => '6471',
            'date_of_birth'      => '1995-05-12',
            'national_id'        => '123',  // terlalu pendek
            'province_code'      => '64',
            'city_code'          => '6471',
            'district_code'      => '647101',
            'village_code'       => '6471010001',
            'postal_code'        => '75117',
            'address'            => 'JL. CONTOH',
            'ktp_temp_path'      => 'owner/ktp_temp/test.jpg',
        ]);

        $response->assertSessionHasErrors(['national_id']);
    }
}
