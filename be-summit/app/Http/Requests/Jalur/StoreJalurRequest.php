<?php

namespace App\Http\Requests\Jalur;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJalurRequest extends FormRequest
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
            'gunung_id' => 'required|exists:gunungs,id',
            'nama_jalur' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'titik_awal_mdpl' => 'required|string|max:50',
            'titik_akhir_mdpl' => 'required|string|max:50',
            'waktu_tempuh' => 'required|string|max:100',
            'status' => 'required|in:open,close',
            'panjang_jalur' => 'required|string|max:50',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit,ekstrem',
        ];
    }
}
