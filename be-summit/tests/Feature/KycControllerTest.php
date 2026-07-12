<?php

use App\Models\Pendaki;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('pendaki can submit KYC profile and upload identity document', function () {
    Storage::fake('local');

    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    $file = UploadedFile::fake()->image('ktp.jpg', 600, 400);

    $response = $this->actingAs($user)
        ->postJson(route('kyc.submit'), [
            'nama_lengkap' => 'Climber Satu',
            'jenis_identitas' => 'ktp',
            'nomor_identitas' => '1234567890123456',
            'foto_identitas' => $file,
            'tanggal_lahir' => '1995-05-15',
            'jenis_kelamin' => 'l',
            'alamat' => 'Jl. Pendakian Indah No. 10',
            'telepon' => '081234567890',
            'nama_kontak_darurat' => 'Sobat Mendaki',
            'telepon_darurat' => '081298765432',
            'hubungan_darurat' => 'Teman',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'message' => 'KYC identity submitted successfully.',
    ]);

    $this->assertDatabaseHas('pendakis', [
        'user_id' => $user->id,
        'nomor_identitas' => '1234567890123456',
        'status_verifikasi' => 'pending',
    ]);

    $pendaki = $user->fresh()->pendaki;
    Storage::disk('local')->assertExists($pendaki->foto_identitas);
});

test('pendaki can check their KYC status', function () {
    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    // Check status when no KYC has been submitted yet
    $response = $this->actingAs($user)->getJson(route('kyc.status'));
    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'data' => null,
    ]);

    // Create a fake profile
    $pendaki = Pendaki::create([
        'user_id' => $user->id,
        'nama_lengkap' => $user->name,
        'jenis_identitas' => 'paspor',
        'nomor_identitas' => 'A1234567',
        'foto_identitas' => 'kyc_documents/fake.jpg',
        'tanggal_lahir' => '1990-01-01',
        'jenis_kelamin' => 'p',
        'alamat' => 'Alamat Pendaki',
        'telepon' => '081234567890',
        'nama_kontak_darurat' => 'Kontak',
        'telepon_darurat' => '081234567891',
        'hubungan_darurat' => 'Orang Tua',
        'status_verifikasi' => 'pending',
    ]);

    $user->refresh();

    $response = $this->actingAs($user)->getJson(route('kyc.status'));
    $response->assertStatus(200);
    $response->assertJsonPath('data.nomor_identitas', 'A1234567');
    $response->assertJsonPath('data.status_verifikasi', 'pending');
});

test('only admin can access admin KYC routes', function () {
    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    $response = $this->actingAs($user)->getJson(route('admin.kyc.index'));
    $response->assertStatus(403);
});

test('admin can view KYC list and verify user profiles', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'pendaki']);

    $pendaki = Pendaki::create([
        'user_id' => $user->id,
        'nama_lengkap' => $user->name,
        'jenis_identitas' => 'ktp',
        'nomor_identitas' => '9999999999999999',
        'foto_identitas' => 'kyc_documents/fake.jpg',
        'tanggal_lahir' => '1990-01-01',
        'jenis_kelamin' => 'p',
        'alamat' => 'Alamat Pendaki',
        'telepon' => '081234567890',
        'nama_kontak_darurat' => 'Kontak',
        'telepon_darurat' => '081234567891',
        'hubungan_darurat' => 'Orang Tua',
        'status_verifikasi' => 'pending',
    ]);

    // Admin lists KYC
    $response = $this->actingAs($admin)->getJson(route('admin.kyc.index', ['status' => 'pending']));
    $response->assertStatus(200);
    $response->assertJsonPath('data.data.0.nomor_identitas', '9999999999999999');

    // Admin views single KYC
    $response = $this->actingAs($admin)->getJson(route('admin.kyc.show', ['id' => $pendaki->id]));
    $response->assertStatus(200);
    $response->assertJsonPath('data.nomor_identitas', '9999999999999999');

    // Admin rejects KYC
    $response = $this->actingAs($admin)->postJson(route('admin.kyc.verify', ['id' => $pendaki->id]), [
        'status_verifikasi' => 'ditolak',
        'alasan_penolakan' => 'Foto KTP tidak jelas.',
    ]);
    $response->assertStatus(200);
    $this->assertDatabaseHas('pendakis', [
        'id' => $pendaki->id,
        'status_verifikasi' => 'ditolak',
        'alasan_penolakan' => 'Foto KTP tidak jelas.',
    ]);

    // Admin approves KYC
    $response = $this->actingAs($admin)->postJson(route('admin.kyc.verify', ['id' => $pendaki->id]), [
        'status_verifikasi' => 'disetujui',
    ]);
    $response->assertStatus(200);
    $this->assertDatabaseHas('pendakis', [
        'id' => $pendaki->id,
        'status_verifikasi' => 'disetujui',
        'alasan_penolakan' => null,
    ]);
});

test('middleware kyc.verified blocks transactions if user is not verified', function () {
    // Dynamically register a test route using the kyc.verified middleware
    Route::middleware(['auth:sanctum', 'kyc.verified'])->get('/api/test-transaction', function () {
        return response()->json(['status' => 'success', 'message' => 'Transaction allowed']);
    });

    $user = User::factory()->create(['role' => 'pendaki']);
    $user->markEmailAsVerified();

    // 1. Without profile, should be blocked
    $response = $this->actingAs($user)->getJson('/api/test-transaction');
    $response->assertStatus(403);
    $response->assertJson([
        'status' => 'error',
        'message' => 'Anda harus melakukan verifikasi identitas (KYC) terlebih dahulu sebelum mengakses fitur ini.',
    ]);

    // 2. With profile pending, should be blocked
    $pendaki = Pendaki::create([
        'user_id' => $user->id,
        'nama_lengkap' => $user->name,
        'jenis_identitas' => 'ktp',
        'nomor_identitas' => '7777777777777777',
        'foto_identitas' => 'kyc_documents/fake.jpg',
        'tanggal_lahir' => '1990-01-01',
        'jenis_kelamin' => 'l',
        'alamat' => 'Alamat Pendaki',
        'telepon' => '081234567890',
        'nama_kontak_darurat' => 'Kontak',
        'telepon_darurat' => '081234567891',
        'hubungan_darurat' => 'Teman',
        'status_verifikasi' => 'pending',
    ]);

    $user->refresh();

    $response = $this->actingAs($user)->getJson('/api/test-transaction');
    $response->assertStatus(403);

    // 3. With profile approved, should be allowed
    $pendaki->update(['status_verifikasi' => 'disetujui']);

    $user->refresh();

    $response = $this->actingAs($user)->getJson('/api/test-transaction');
    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'message' => 'Transaction allowed',
    ]);
});
