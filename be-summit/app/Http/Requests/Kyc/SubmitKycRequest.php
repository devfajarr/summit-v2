<?php

namespace App\Http\Requests\Kyc;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitKycRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'pendaki';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pendakiId = $this->user()->pendaki?->id;

        return [
            'nama_lengkap' => 'required|string|max:255',
            'jenis_identitas' => 'required|in:ktp,paspor,sim,lainnya',
            'nomor_identitas' => 'required|string|max:50|unique:pendakis,nomor_identitas,'.($pendakiId ?? 'NULL'),
            'foto_identitas' => $pendakiId ? 'nullable|image|mimes:jpeg,png,jpg|max:2048' : 'required|image|mimes:jpeg,png,jpg|max:2048',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:l,p',
            'alamat' => 'required|string|max:1000',
            'telepon' => 'required|string|max:20',
            'nama_kontak_darurat' => 'required|string|max:255',
            'telepon_darurat' => 'required|string|max:20',
            'hubungan_darurat' => 'required|string|max:100',
        ];
    }
}
