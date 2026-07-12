<?php

namespace App\Http\Requests\Jalur;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJalurRequest extends FormRequest
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
        $isPatch = $this->isMethod('PATCH');
        $prefix = $isPatch ? 'sometimes|' : '';

        return [
            'gunung_id' => $prefix.'required|exists:gunungs,id',
            'nama_jalur' => $prefix.'required|string|max:255',
            'deskripsi' => $prefix.'required|string',
            'titik_awal_mdpl' => $prefix.'required|string|max:50',
            'titik_akhir_mdpl' => $prefix.'required|string|max:50',
            'waktu_tempuh' => $prefix.'required|string|max:100',
            'status' => $prefix.'required|in:open,close',
            'panjang_jalur' => $prefix.'required|string|max:50',
            'tingkat_kesulitan' => $prefix.'required|in:mudah,sedang,sulit,ekstrem',
        ];
    }
}
