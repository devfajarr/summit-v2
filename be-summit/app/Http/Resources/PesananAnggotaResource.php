<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PesananAnggotaResource',
    title: 'Pesanan Anggota Resource',
    description: 'Order member resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'pesanan_id', type: 'integer', example: 10),
        new OA\Property(property: 'nama_anggota', type: 'string', example: 'Jane Doe'),
        new OA\Property(property: 'nik_identitas', type: 'string', example: '1234567890123456'),
        new OA\Property(property: 'telepon', type: 'string', nullable: true, example: '081234567890'),
        new OA\Property(property: 'telepon_darurat', type: 'string', nullable: true, example: '081298765432'),
        new OA\Property(property: 'hubungan_darurat', type: 'string', nullable: true, example: 'Istri'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
    ]
)]
class PesananAnggotaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pesanan_id' => $this->pesanan_id,
            'nama_anggota' => $this->nama_anggota,
            'nik_identitas' => $this->nik_identitas,
            'telepon' => $this->telepon,
            'telepon_darurat' => $this->telepon_darurat,
            'hubungan_darurat' => $this->hubungan_darurat,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
