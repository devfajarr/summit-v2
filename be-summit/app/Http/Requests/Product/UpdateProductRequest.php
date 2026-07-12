<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'mitra';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $mitraId = $this->user()->mitra?->id;
        $isPatch = $this->isMethod('PATCH');
        $prefix = $isPatch ? 'sometimes|' : '';

        return [
            'basecamp_id' => [
                $prefix.'required',
                Rule::exists('basecamps', 'id')->where('mitra_id', $mitraId),
            ],
            'nama_produk' => $prefix.'required|string|max:255',
            'kategori' => $prefix.'required|string|in:ticket,rental,opentrip,guide,porter,transport,parkir,merchandise,kuliner',
            'deskripsi' => 'sometimes|nullable|string',
            'harga' => $prefix.'required|numeric|min:0',
            'stok' => 'sometimes|nullable|integer|min:0',
            'satuan' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'gambar' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Validasi Kondisional untuk Open Trip
            'tanggal_berangkat' => 'required_if:kategori,opentrip|nullable|date|after_or_equal:today',
            'tanggal_pulang' => 'required_if:kategori,opentrip|nullable|date|after_or_equal:tanggal_berangkat',
            'meeting_point' => 'required_if:kategori,opentrip|nullable|string|max:255',
            'minimal_peserta' => 'required_if:kategori,opentrip|nullable|integer|min:1',
            'maksimal_peserta' => 'required_if:kategori,opentrip|nullable|integer|min:1',

            // Validasi Kondisional untuk Tiket
            'jalur_id' => 'required_if:kategori,ticket|nullable|exists:jalur_pendakians,id',
            'jam_buka' => 'required_if:kategori,ticket|nullable|date_format:H:i',
            'jam_tutup' => 'required_if:kategori,ticket|nullable|date_format:H:i',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                if ($this->kategori === 'opentrip') {
                    $min = (int) $this->minimal_peserta;
                    $max = (int) $this->maksimal_peserta;
                    if ($max < $min) {
                        $validator->errors()->add('maksimal_peserta', 'Maksimal peserta tidak boleh lebih kecil dari minimal peserta.');
                    }
                }
            },
        ];
    }
}
