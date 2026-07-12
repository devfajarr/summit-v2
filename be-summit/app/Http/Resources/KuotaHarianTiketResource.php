<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'KuotaHarianTiketResource',
    title: 'Kuota Harian Tiket Resource',
    description: 'Daily ticket quota representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'produk_tiket_id', type: 'integer', example: 2),
        new OA\Property(property: 'tanggal', type: 'string', format: 'date', example: '2026-07-10'),
        new OA\Property(property: 'kuota_total', type: 'integer', example: 100),
        new OA\Property(property: 'kuota_tersisa', type: 'integer', example: 100),
    ]
)]
class KuotaHarianTiketResource extends JsonResource
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
            'produk_tiket_id' => $this->produk_tiket_id,
            'tanggal' => $this->tanggal?->toDateString(),
            'kuota_total' => $this->kuota_total,
            'kuota_tersisa' => $this->kuota_tersisa,
        ];
    }
}
