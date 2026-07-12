<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProdukOpentripResource',
    title: 'Produk Opentrip Resource',
    description: 'Open trip product detail representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'produk_id', type: 'integer', example: 2),
        new OA\Property(property: 'tanggal_berangkat', type: 'string', format: 'date', example: '2026-08-01'),
        new OA\Property(property: 'tanggal_pulang', type: 'string', format: 'date', example: '2026-08-03'),
        new OA\Property(property: 'meeting_point', type: 'string', example: 'Basecamp Selo'),
        new OA\Property(property: 'minimal_peserta', type: 'integer', example: 5),
        new OA\Property(property: 'maksimal_peserta', type: 'integer', example: 15),
        new OA\Property(property: 'sisa_kursi', type: 'integer', example: 15),
    ]
)]
class ProdukOpentripResource extends JsonResource
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
            'produk_id' => $this->produk_id,
            'tanggal_berangkat' => $this->tanggal_berangkat?->toDateString(),
            'tanggal_pulang' => $this->tanggal_pulang?->toDateString(),
            'meeting_point' => $this->meeting_point,
            'minimal_peserta' => $this->minimal_peserta,
            'maksimal_peserta' => $this->maksimal_peserta,
            'sisa_kursi' => $this->sisa_kursi,
        ];
    }
}
