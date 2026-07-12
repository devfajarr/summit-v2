<x-mail::message>
# Halo {{ $name }},

Terima kasih telah mendaftar di **BE Summit**. Untuk menyelesaikan proses verifikasi email Anda, silakan gunakan kode OTP berikut:

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

Kode OTP ini berlaku selama **10 menit**. Jangan membagikan kode ini kepada siapa pun demi keamanan akun Anda.

Jika Anda tidak merasa mendaftar di aplikasi kami, silakan abaikan email ini.

Salam,<br>
Tim {{ config('app.name') }}
</x-mail::message>
