<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProfileResource',
    title: 'Profile Resource',
    description: 'User profile representation with role-specific details',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'mitra', 'pendaki'], example: 'pendaki'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-02T15:34:17+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-02T15:34:17+07:00'),
        new OA\Property(property: 'pendaki', ref: '#/components/schemas/PendakiResource', nullable: true),
        new OA\Property(property: 'mitra', ref: '#/components/schemas/MitraResource', nullable: true),
    ]
)]
class ProfileResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'pendaki' => $this->when($this->role === 'pendaki' && $this->relationLoaded('pendaki'), function () {
                return $this->pendaki ? new PendakiResource($this->pendaki) : null;
            }),
            'mitra' => $this->when($this->role === 'mitra' && $this->relationLoaded('mitra'), function () {
                return $this->mitra ? new MitraResource($this->mitra) : null;
            }),
        ];
    }
}
