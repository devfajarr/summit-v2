<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PendakiResource',
    title: 'Pendaki Resource',
    description: 'Climber profile KYC resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'nama_lengkap', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'jenis_identitas', type: 'string', example: 'ktp'),
        new OA\Property(property: 'nomor_identitas', type: 'string', example: '1234567890123456'),
        new OA\Property(property: 'foto_identitas', type: 'string', example: 'kyc_documents/xyz.jpg'),
        new OA\Property(property: 'tanggal_lahir', type: 'string', format: 'date', example: '2000-01-01'),
        new OA\Property(property: 'jenis_kelamin', type: 'string', example: 'l'),
        new OA\Property(property: 'alamat', type: 'string', example: 'Jl. Merdeka No. 45'),
        new OA\Property(property: 'telepon', type: 'string', example: '081234567890'),
        new OA\Property(property: 'nama_kontak_darurat', type: 'string', example: 'Jane Doe'),
        new OA\Property(property: 'telepon_darurat', type: 'string', example: '081298765432'),
        new OA\Property(property: 'hubungan_darurat', type: 'string', example: 'Istri'),
        new OA\Property(property: 'status_verifikasi', type: 'string', example: 'pending'),
        new OA\Property(property: 'alasan_penolakan', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'verified_at', type: 'string', format: 'date-time', nullable: true, example: null),
        new OA\Property(property: 'verified_by', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-03T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-03T12:00:00+07:00'),
    ]
)]
class PendakiResource extends JsonResource
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
            'nama_lengkap' => $this->nama_lengkap,
            'jenis_identitas' => $this->jenis_identitas,
            'nomor_identitas' => $this->nomor_identitas,
            'foto_identitas' => $this->foto_identitas,
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat,
            'telepon' => $this->telepon,
            'nama_kontak_darurat' => $this->nama_kontak_darurat,
            'telepon_darurat' => $this->telepon_darurat,
            'hubungan_darurat' => $this->hubungan_darurat,
            'status_verifikasi' => $this->status_verifikasi,
            'alasan_penolakan' => $this->alasan_penolakan,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'verified_by' => $this->verified_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
