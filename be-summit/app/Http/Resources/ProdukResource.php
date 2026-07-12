<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProdukResource',
    title: 'Produk Resource',
    description: 'General product resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'basecamp_id', type: 'integer', example: 2),
        new OA\Property(property: 'nama_produk', type: 'string', example: 'Sewa Tenda Dome'),
        new OA\Property(property: 'kategori', type: 'string', example: 'rental'),
        new OA\Property(property: 'deskripsi', type: 'string', nullable: true, example: 'Tenda dome double layer kapasitas 4 orang.'),
        new OA\Property(property: 'harga', type: 'number', format: 'float', example: 35000.00),
        new OA\Property(property: 'stok', type: 'integer', nullable: true, example: 15),
        new OA\Property(property: 'satuan', type: 'string', nullable: true, example: 'hari'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'gambar', type: 'string', nullable: true, example: 'http://be-summit.test/storage/products/tenda.jpg'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'basecamp', ref: '#/components/schemas/BasecampResource'),
        new OA\Property(property: 'opentrip', ref: '#/components/schemas/ProdukOpentripResource'),
        new OA\Property(property: 'tiket', ref: '#/components/schemas/ProdukTiketResource'),
    ]
)]
class ProdukResource extends JsonResource
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
            'basecamp_id' => $this->basecamp_id,
            'nama_produk' => $this->nama_produk,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
            'harga' => (float) $this->harga,
            'stok' => $this->stok,
            'satuan' => $this->satuan,
            'is_active' => (bool) $this->is_active,
            'gambar' => $this->gambar ? (filter_var($this->gambar, FILTER_VALIDATE_URL) ? $this->gambar : asset('storage/'.$this->gambar)) : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'basecamp' => new BasecampResource($this->whenLoaded('basecamp')),
            'opentrip' => new ProdukOpentripResource($this->whenLoaded('opentrip')),
            'tiket' => new ProdukTiketResource($this->whenLoaded('tiket')),
        ];
    }
}
