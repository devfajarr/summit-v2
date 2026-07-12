<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProdukTiketResource',
    title: 'Produk Tiket Resource',
    description: 'Ticket product detail representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'produk_id', type: 'integer', example: 2),
        new OA\Property(property: 'jalur_id', type: 'integer', example: 3),
        new OA\Property(property: 'jam_buka', type: 'string', example: '07:00:00'),
        new OA\Property(property: 'jam_tutup', type: 'string', example: '17:00:00'),
        new OA\Property(property: 'jalur', ref: '#/components/schemas/JalurPendakianResource'),
        new OA\Property(property: 'kuotas', type: 'array', items: new OA\Items(ref: '#/components/schemas/KuotaHarianTiketResource')),
    ]
)]
class ProdukTiketResource extends JsonResource
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
            'jalur_id' => $this->jalur_id,
            'jam_buka' => $this->jam_buka,
            'jam_tutup' => $this->jam_tutup,
            'jalur' => new JalurPendakianResource($this->whenLoaded('jalur')),
            'kuotas' => KuotaHarianTiketResource::collection($this->whenLoaded('kuotas')),
        ];
    }
}
