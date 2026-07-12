<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DetailPesananResource',
    title: 'Detail Pesanan Resource',
    description: 'Order item detail resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'pesanan_id', type: 'integer', example: 10),
        new OA\Property(property: 'produk_id', type: 'integer', example: 2),
        new OA\Property(property: 'qty', type: 'integer', example: 2),
        new OA\Property(property: 'harga', type: 'number', format: 'float', example: 20000.00),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 40000.00),
        new OA\Property(property: 'status_operasional', type: 'string', example: 'pending'),
        new OA\Property(property: 'kode_tiket', type: 'string', nullable: true, example: 'TKT-CBD-001'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'produk', ref: '#/components/schemas/ProdukResource'),
    ]
)]
class DetailPesananResource extends JsonResource
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
            'produk_id' => $this->produk_id,
            'qty' => $this->qty,
            'harga' => (float) $this->harga,
            'subtotal' => (float) $this->subtotal,
            'status_operasional' => $this->status_operasional,
            'kode_tiket' => $this->kode_tiket,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'produk' => new ProdukResource($this->whenLoaded('produk')),
        ];
    }
}
