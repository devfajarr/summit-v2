<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'JalurPendakianResource',
    title: 'Jalur Pendakian Resource',
    description: 'Trail profile resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'gunung_id', type: 'integer', example: 1),
        new OA\Property(property: 'nama_jalur', type: 'string', example: 'Jalur Cibodas'),
        new OA\Property(property: 'deskripsi', type: 'string', example: 'Jalur legendaris berbatu dengan pemandangan air panas.'),
        new OA\Property(property: 'titik_awal_mdpl', type: 'string', example: '1300 MDPL'),
        new OA\Property(property: 'titik_akhir_mdpl', type: 'string', example: '2958 MDPL'),
        new OA\Property(property: 'waktu_tempuh', type: 'string', example: '7 Jam'),
        new OA\Property(property: 'status', type: 'string', enum: ['open', 'close'], example: 'open'),
        new OA\Property(property: 'panjang_jalur', type: 'string', example: '9.7 Km'),
        new OA\Property(property: 'tingkat_kesulitan', type: 'string', enum: ['mudah', 'sedang', 'sulit', 'ekstrem'], example: 'sedang'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-03T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-03T12:00:00+07:00'),
    ]
)]
class JalurPendakianResource extends JsonResource
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
            'gunung_id' => $this->gunung_id,
            'nama_jalur' => $this->nama_jalur,
            'deskripsi' => $this->deskripsi,
            'titik_awal_mdpl' => $this->titik_awal_mdpl,
            'titik_akhir_mdpl' => $this->titik_akhir_mdpl,
            'waktu_tempuh' => $this->waktu_tempuh,
            'status' => $this->status,
            'panjang_jalur' => $this->panjang_jalur,
            'tingkat_kesulitan' => $this->tingkat_kesulitan,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
