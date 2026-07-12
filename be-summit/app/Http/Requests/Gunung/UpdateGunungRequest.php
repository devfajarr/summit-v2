<?php

namespace App\Http\Requests\Gunung;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGunungRequest extends FormRequest
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
            'nama_gunung' => $prefix.'required|string|max:255',
            'deskripsi' => $prefix.'required|string',
            'tinggi_mdpl' => $prefix.'required|integer|min:0',
            'lokasi' => $prefix.'required|string|max:255',
            'foto' => $prefix.'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => $prefix.'required|string|max:100',
        ];
    }
}
