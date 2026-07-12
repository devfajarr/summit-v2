<?php

namespace App\Http\Controllers\Pendaki;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pesanan\CreatePesananRequest;
use App\Http\Resources\PesananResource;
use App\Services\PesananService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class PesananController extends Controller
{
    /**
     * Inject PesananService.
     */
    public function __construct(
        protected PesananService $pesananService
    ) {}

    #[OA\Post(
        path: '/api/pesanan',
        summary: 'Create a new climber booking order (Pesanan)',
        tags: ['Pesanan (Climber)'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['basecamp_id', 'jalur_id', 'tanggal_booking', 'anggotas', 'items'],
                properties: [
                    new OA\Property(property: 'basecamp_id', type: 'integer', example: 1),
                    new OA\Property(property: 'jalur_id', type: 'integer', example: 1),
                    new OA\Property(property: 'tanggal_booking', type: 'string', format: 'date', example: '2026-07-10'),
                    new OA\Property(
                        property: 'anggotas',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'nama_anggota', type: 'string', example: 'Jane Doe'),
                                new OA\Property(property: 'nik_identitas', type: 'string', example: '1234567890123456'),
                                new OA\Property(property: 'telepon', type: 'string', example: '081234567890'),
                                new OA\Property(property: 'telepon_darurat', type: 'string', example: '081298765432'),
                                new OA\Property(property: 'hubungan_darurat', type: 'string', example: 'Istri'),
                            ]
                        )
                    ),
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'produk_id', type: 'integer', example: 1),
                                new OA\Property(property: 'qty', type: 'integer', example: 2),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Order created successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PesananResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error / Out of stock or quota'),
        ]
    )]
    public function store(CreatePesananRequest $request): JsonResponse
    {
        try {
            $pesanan = $this->pesananService->createPesanan(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully.',
                'data' => new PesananResource($pesanan),
            ], 201);
        } catch (LockTimeoutException $e) {
            throw ValidationException::withMessages([
                'transaction' => ['Proses pemesanan sedang berlangsung, silakan tunggu beberapa saat.'],
            ]);
        }
    }
}
