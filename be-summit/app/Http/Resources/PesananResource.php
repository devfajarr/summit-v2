<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PesananResource',
    title: 'Pesanan Resource',
    description: 'Booking order resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'invoice', type: 'string', example: 'INV/20260706/ABC12'),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'basecamp_id', type: 'integer', example: 2),
        new OA\Property(property: 'jalur_id', type: 'integer', example: 3),
        new OA\Property(property: 'status', type: 'string', example: 'pending'),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 90000.00),
        new OA\Property(property: 'tanggal_booking', type: 'string', format: 'date', example: '2026-07-10'),
        new OA\Property(property: 'diskon', type: 'number', format: 'float', example: 0.00),
        new OA\Property(property: 'biaya_layanan_user', type: 'number', format: 'float', example: 5000.00),
        new OA\Property(property: 'komisi_admin', type: 'number', format: 'float', example: 9000.00),
        new OA\Property(property: 'pendapatan_mitra', type: 'number', format: 'float', example: 81000.00),
        new OA\Property(property: 'total_bayar', type: 'number', format: 'float', example: 95000.00),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-06T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-06T12:00:00+07:00'),
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'basecamp', ref: '#/components/schemas/BasecampResource'),
        new OA\Property(property: 'jalur', ref: '#/components/schemas/JalurPendakianResource'),
        new OA\Property(property: 'anggotas', type: 'array', items: new OA\Items(ref: '#/components/schemas/PesananAnggotaResource')),
        new OA\Property(property: 'details', type: 'array', items: new OA\Items(ref: '#/components/schemas/DetailPesananResource')),
        new OA\Property(property: 'pembayaran', ref: '#/components/schemas/PembayaranResource'),
    ]
)]
class PesananResource extends JsonResource
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
            'invoice' => $this->invoice,
            'user_id' => $this->user_id,
            'basecamp_id' => $this->basecamp_id,
            'jalur_id' => $this->jalur_id,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'tanggal_booking' => $this->tanggal_booking instanceof Carbon ? $this->tanggal_booking->toDateString() : ($this->tanggal_booking ? date('Y-m-d', strtotime($this->tanggal_booking)) : null),
            'diskon' => (float) $this->diskon,
            'biaya_layanan_user' => (float) $this->biaya_layanan_user,
            'komisi_admin' => (float) $this->komisi_admin,
            'pendapatan_mitra' => (float) $this->pendapatan_mitra,
            'total_bayar' => (float) $this->total_bayar,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
            'basecamp' => new BasecampResource($this->whenLoaded('basecamp')),
            'jalur' => new JalurPendakianResource($this->whenLoaded('jalur')),
            'anggotas' => PesananAnggotaResource::collection($this->whenLoaded('anggotas')),
            'details' => DetailPesananResource::collection($this->whenLoaded('details')),
            'pembayaran' => new PembayaranResource($this->whenLoaded('pembayaran')),
        ];
    }
}
