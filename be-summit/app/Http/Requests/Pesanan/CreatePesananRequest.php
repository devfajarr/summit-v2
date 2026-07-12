<?php

namespace App\Http\Requests\Pesanan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePesananRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'basecamp_id' => ['required', 'integer', 'exists:basecamps,id'],
            'jalur_id' => ['required', 'integer', 'exists:jalur_pendakians,id'],
            'tanggal_booking' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],

            // Climber members array
            'anggotas' => ['required', 'array', 'min:1'],
            'anggotas.*.nama_anggota' => ['required', 'string', 'max:255'],
            'anggotas.*.nik_identitas' => ['required', 'string', 'size:16'],
            'anggotas.*.telepon' => ['nullable', 'string', 'max:20'],
            'anggotas.*.telepon_darurat' => ['nullable', 'string', 'max:20'],
            'anggotas.*.hubungan_darurat' => ['nullable', 'string', 'max:50'],

            // Ordered items array
            'items' => ['required', 'array', 'min:1'],
            'items.*.produk_id' => ['required', 'integer', 'exists:produks,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'basecamp_id.required' => 'ID basecamp wajib diisi.',
            'basecamp_id.exists' => 'Basecamp tidak ditemukan.',
            'jalur_id.required' => 'ID jalur pendakian wajib diisi.',
            'jalur_id.exists' => 'Jalur pendakian tidak ditemukan.',
            'tanggal_booking.required' => 'Tanggal booking wajib diisi.',
            'tanggal_booking.date' => 'Tanggal booking tidak valid.',
            'tanggal_booking.after_or_equal' => 'Tanggal booking minimal hari ini.',
            'anggotas.required' => 'Anggota pendaki wajib diisi minimal 1 orang.',
            'anggotas.*.nama_anggota.required' => 'Nama anggota wajib diisi.',
            'anggotas.*.nik_identitas.required' => 'NIK identitas wajib diisi.',
            'anggotas.*.nik_identitas.size' => 'NIK identitas harus 16 digit.',
            'items.required' => 'Item produk yang dipesan wajib diisi minimal 1 item.',
            'items.*.produk_id.required' => 'ID produk wajib diisi.',
            'items.*.produk_id.exists' => 'Produk tidak ditemukan.',
            'items.*.qty.required' => 'Jumlah pembelian wajib diisi.',
            'items.*.qty.min' => 'Jumlah pembelian minimal 1.',
        ];
    }
}
