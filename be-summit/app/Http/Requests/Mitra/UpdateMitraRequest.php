<?php

namespace App\Http\Requests\Mitra;

use App\Models\Mitra;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMitraRequest extends FormRequest
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
        $mitraId = $this->route('id');
        $mitra = Mitra::find($mitraId);
        $userId = $mitra?->user_id;

        $isPatch = $this->isMethod('PATCH');
        $prefix = $isPatch ? 'sometimes|' : '';

        return [
            'email' => [
                $prefix.'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => 'sometimes|nullable|string|min:8',
            'nama_pemilik' => $prefix.'required|string|max:255',
            'telepon' => $prefix.'required|string|max:255',
            'alamat' => $prefix.'required|string',
            'deskripsi' => 'sometimes|nullable|string',
            'status' => $prefix.'required|string|in:aktif,suspend',
            'npwp' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('mitras', 'npwp')->ignore($mitraId),
            ],
            'nik' => [
                $prefix.'required',
                'string',
                'max:255',
                Rule::unique('mitras', 'nik')->ignore($mitraId),
            ],
            'rekening_bank' => [
                $prefix.'required',
                'string',
                'max:255',
                Rule::unique('mitras', 'rekening_bank')->ignore($mitraId),
            ],
            'nama_rekening' => $prefix.'required|string|max:255',
            'bank' => $prefix.'required|string|max:255',
            'ewallet' => 'sometimes|nullable|string|max:255',
        ];
    }
}
