<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PembayaranResource',
    title: 'Pembayaran Resource',
    description: 'Payment resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'pesanan_id', type: 'integer', example: 10),
        new OA\Property(property: 'metode', type: 'string', nullable: true, example: 'qris'),
        new OA\Property(property: 'provider', type: 'string', nullable: true, example: 'QRIS'),
        new OA\Property(property: 'xendit_invoice_id', type: 'string', nullable: true, example: 'xendit-inv-12345'),
        new OA\Property(property: 'checkout_url', type: 'string', nullable: true, example: 'https://checkout.xendit.co/v2/invoice/abc'),
        new OA\Property(property: 'status', type: 'string', example: 'pending'),
        new OA\Property(property: 'biaya_gateway', type: 'number', format: 'float', example: 750.00),
        new OA\Property(property: 'paid_at', type: 'string', format: 'date-time', nullable: true, example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'expired_at', type: 'string', format: 'date-time', nullable: true, example: '2026-07-05T12:10:00+07:00'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-05T12:00:00+07:00'),
    ]
)]
class PembayaranResource extends JsonResource
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
            'metode' => $this->metode,
            'provider' => $this->provider,
            'xendit_invoice_id' => $this->xendit_invoice_id,
            'checkout_url' => $this->checkout_url,
            'status' => $this->status,
            'biaya_gateway' => (float) $this->biaya_gateway,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'expired_at' => $this->expired_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
