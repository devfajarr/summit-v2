<?php

namespace App\Http\Requests\Mitra;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMitraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'nama_pemilik' => 'required|string|max:255',
            'telepon' => 'required|string|max:255',
            'alamat' => 'required|string',
            'deskripsi' => 'nullable|string',
            'status' => 'required|string|in:aktif,suspend',
            'npwp' => 'nullable|string|max:255|unique:mitras,npwp',
            'nik' => 'required|string|max:255|unique:mitras,nik',
            'rekening_bank' => 'required|string|max:255|unique:mitras,rekening_bank',
            'nama_rekening' => 'required|string|max:255',
            'bank' => 'required|string|max:255',
            'ewallet' => 'nullable|string|max:255',
        ];
    }
}
