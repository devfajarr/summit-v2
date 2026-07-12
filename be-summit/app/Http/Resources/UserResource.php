<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserResource",
    title: "User Resource",
    description: "User resource representation",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Jhon Doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "jhon@example.com"),
        new OA\Property(property: "role", type: "string", example: "pendaki"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-02T15:34:17+07:00"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-07-02T15:34:17+07:00")
    ]
)]
class UserResource extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
