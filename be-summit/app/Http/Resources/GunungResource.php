<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GunungResource',
    title: 'Gunung Resource',
    description: 'Mountain profile resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nama_gunung', type: 'string', example: 'Gunung Gede'),
        new OA\Property(property: 'deskripsi', type: 'string', example: 'Gunung api aktif di Jawa Barat yang populer.'),
        new OA\Property(property: 'tinggi_mdpl', type: 'integer', example: 2958),
        new OA\Property(property: 'lokasi', type: 'string', example: 'Cianjur, Jawa Barat'),
        new OA\Property(property: 'foto', type: 'string', example: 'http://be-summit.test/storage/gunungs/gede.jpg'),
        new OA\Property(property: 'status', type: 'string', example: 'aktif'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-03T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-03T12:00:00+07:00'),
        new OA\Property(
            property: 'jalurs',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/JalurPendakianResource'),
            description: 'List of trails associated with this mountain'
        ),
    ]
)]
class GunungResource extends JsonResource
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
            'nama_gunung' => $this->nama_gunung,
            'deskripsi' => $this->deskripsi,
            'tinggi_mdpl' => $this->tinggi_mdpl,
            'lokasi' => $this->lokasi,
            'foto' => $this->foto ? (filter_var($this->foto, FILTER_VALIDATE_URL) ? $this->foto : asset('storage/'.$this->foto)) : null,
            'status' => $this->status,
            'jalurs' => JalurPendakianResource::collection($this->whenLoaded('jalurs')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
