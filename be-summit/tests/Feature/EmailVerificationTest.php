<?php

use App\Mail\SendOtpMail;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(LazilyRefreshDatabase::class);

test('user registration sends an email with OTP code', function () {
    Mail::fake();

    $response = $this->postJson(route('register'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'status' => 'success',
        'message' => 'Registration successful. Please check your email for the OTP verification code.',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    $user = User::where('email', 'john@example.com')->first();
    $this->assertDatabaseHas('user_otps', [
        'user_id' => $user->id,
        'is_used' => false,
    ]);

    Mail::assertSent(SendOtpMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('user cannot login if email is not verified', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => null,
    ]);

    $response = $this->postJson(route('login'), [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(403);
    $response->assertJson([
        'status' => 'error',
        'message' => 'Your email address is not verified.',
    ]);
});

test('user can verify their email with a valid OTP and receive a token', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    $otp = UserOtp::create([
        'user_id' => $user->id,
        'otp' => '123456',
        'is_used' => false,
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson(route('verify-otp'), [
        'email' => 'john@example.com',
        'otp' => '123456',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'status' => 'success',
        'message' => 'Email verified successfully.',
    ]);
    $response->assertJsonStructure(['token']);
    $response->assertCookie('auth_token');

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();

    $otp->refresh();
    expect($otp->is_used)->toBeTrue();
});

test('user cannot verify their email with an invalid OTP', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    UserOtp::create([
        'user_id' => $user->id,
        'otp' => '123456',
        'is_used' => false,
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson(route('verify-otp'), [
        'email' => 'john@example.com',
        'otp' => '000000',
    ]);

    $response->assertStatus(400);
    $response->assertJson([
        'status' => 'error',
        'message' => 'Invalid or expired OTP code.',
    ]);

    $user->refresh();
    expect($user->email_verified_at)->toBeNull();
});

test('user cannot verify their email with an expired OTP', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    UserOtp::create([
        'user_id' => $user->id,
        'otp' => '123456',
        'is_used' => false,
        'expires_at' => now()->subMinutes(1),
    ]);

    $response = $this->postJson(route('verify-otp'), [
        'email' => 'john@example.com',
        'otp' => '123456',
    ]);

    $response->assertStatus(400);
    $response->assertJson([
        'status' => 'error',
        'message' => 'Invalid or expired OTP code.',
    ]);
});

test('user can resend OTP code if email is not verified', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    // Create an old OTP
    $oldOtp = UserOtp::create([
        'user_id' => $user->id,
        'otp' => '111111',
        'is_used' => false,
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson(route('resend-otp'), [
        'email' => 'john@example.com',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'status' => 'success',
        'message' => 'A new OTP verification code has been sent to your email.',
    ]);

    $oldOtp->refresh();
    expect($oldOtp->is_used)->toBeTrue(); // The old OTP should be marked as used (deactivated)

    $newOtp = UserOtp::where('user_id', $user->id)->where('is_used', false)->first();
    expect($newOtp)->not->toBeNull();
    expect($newOtp->otp)->not->toBe('111111');

    Mail::assertSent(SendOtpMail::class, function ($mail) use ($user, $newOtp) {
        return $mail->hasTo($user->email) && $mail->otp === $newOtp->otp;
    });
});

test('user cannot resend OTP code if email is already verified', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson(route('resend-otp'), [
        'email' => 'john@example.com',
    ]);

    $response->assertStatus(400);
    $response->assertJson([
        'status' => 'error',
        'message' => 'Email is already verified.',
    ]);
});

test('user can login successfully and receive auth_token cookie', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson(route('login'), [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'status' => 'success',
        'message' => 'Login successful',
    ]);
    $response->assertJsonStructure(['token']);
    $response->assertCookie('auth_token');
});

test('user can logout successfully and clear auth_token cookie', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->postJson(route('logout'));

    $response->assertSuccessful();
    $response->assertJson([
        'status' => 'success',
        'message' => 'Logout successful',
    ]);
    $response->assertCookieExpired('auth_token');
});
