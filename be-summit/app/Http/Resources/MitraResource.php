<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MitraResource',
    title: 'Mitra Resource',
    description: 'Partner profile resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 2),
        new OA\Property(property: 'nama_pemilik', type: 'string', example: 'Budi Santoso'),
        new OA\Property(property: 'telepon', type: 'string', example: '081234567890'),
        new OA\Property(property: 'alamat', type: 'string', example: 'Jl. Raya Summit No. 10'),
        new OA\Property(property: 'deskripsi', type: 'string', nullable: true, example: 'Pemilik Basecamp Merbabu Indah'),
        new OA\Property(property: 'status', type: 'string', example: 'aktif'),
        new OA\Property(property: 'npwp', type: 'string', nullable: true, example: '12.345.678.9-012.000'),
        new OA\Property(property: 'nik', type: 'string', example: '3201234567890001'),
        new OA\Property(property: 'rekening_bank', type: 'string', example: '1234567890'),
        new OA\Property(property: 'nama_rekening', type: 'string', example: 'Budi Santoso'),
        new OA\Property(property: 'bank', type: 'string', example: 'Bank BCA'),
        new OA\Property(property: 'ewallet', type: 'string', nullable: true, example: '081234567890'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
    ]
)]
class MitraResource extends JsonResource
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
            'user_id' => $this->user_id,
            'nama_pemilik' => $this->nama_pemilik,
            'telepon' => $this->telepon,
            'alamat' => $this->alamat,
            'deskripsi' => $this->deskripsi,
            'status' => $this->status,
            'npwp' => $this->npwp,
            'nik' => $this->nik,
            'rekening_bank' => $this->rekening_bank,
            'nama_rekening' => $this->nama_rekening,
            'bank' => $this->bank,
            'ewallet' => $this->ewallet,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
