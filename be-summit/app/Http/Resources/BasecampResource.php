<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BasecampResource',
    title: 'Basecamp Resource',
    description: 'Basecamp profile resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'mitra_id', type: 'integer', example: 2),
        new OA\Property(property: 'jalur_id', type: 'integer', example: 3),
        new OA\Property(property: 'nama_basecamp', type: 'string', example: 'Basecamp Merbabu Selo'),
        new OA\Property(property: 'latitude', type: 'string', example: '-7.441234'),
        new OA\Property(property: 'longitude', type: 'string', example: '110.421234'),
        new OA\Property(property: 'jam_operasional', type: 'string', example: '24 Jam'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'mitra', ref: '#/components/schemas/MitraResource'),
        new OA\Property(property: 'jalur', ref: '#/components/schemas/JalurPendakianResource'),
        new OA\Property(property: 'produks', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProdukResource'), description: 'List of products sold by this basecamp'),
    ]
)]
class BasecampResource extends JsonResource
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
            'mitra_id' => $this->mitra_id,
            'jalur_id' => $this->jalur_id,
            'nama_basecamp' => $this->nama_basecamp,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'jam_operasional' => $this->jam_operasional,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'mitra' => new MitraResource($this->whenLoaded('mitra')),
            'jalur' => new JalurPendakianResource($this->whenLoaded('jalur')),
            'produks' => ProdukResource::collection($this->whenLoaded('produks')),
        ];
    }
}
