<?php

namespace App\Http\Requests\Basecamp;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBasecampRequest extends FormRequest
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
            'mitra_id' => $prefix.'required|exists:mitras,id',
            'jalur_id' => $prefix.'required|exists:jalur_pendakians,id',
            'nama_basecamp' => $prefix.'required|string|max:255',
            'latitude' => $prefix.'required|string|max:255',
            'longitude' => $prefix.'required|string|max:255',
            'jam_operasional' => $prefix.'required|string|max:255',
        ];
    }
}
