<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserOtp;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Generate a new OTP for the user.
     */
    public function generateOtp(User $user): UserOtp
    {
        // Deactivate existing active OTPs for the user to prevent multiple valid OTPs
        $user->otps()->where('is_used', false)->update(['is_used' => true]);

        // Generate a 6-digit random code
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return $user->otps()->create([
            'otp' => $code,
            'is_used' => false,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    /**
     * Send the OTP mail to the user.
     */
    public function sendOtp(User $user, UserOtp $userOtp): void
    {
        Mail::to($user->email)->send(new SendOtpMail($user->name, $userOtp->otp));
    }

    /**
     * Generate and send OTP in one method.
     */
    public function generateAndSend(User $user): void
    {
        $otp = $this->generateOtp($user);
        $this->sendOtp($user, $otp);
    }

    /**
     * Verify the OTP for the user.
     */
    public function verifyOtp(User $user, string $code): bool
    {
        $validOtp = UserOtp::valid($user->id, $code)->first();

        if (!$validOtp) {
            return false;
        }

        // Mark OTP as used
        $validOtp->update(['is_used' => true]);

        // Mark user email as verified
        $user->markEmailAsVerified();

        return true;
    }
}
