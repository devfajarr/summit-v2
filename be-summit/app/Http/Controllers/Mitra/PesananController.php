<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Resources\PesananResource;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class PesananController extends Controller
{
    #[OA\Get(
        path: '/api/mitra/pesanan',
        summary: 'List all incoming bookings for this partner (Mitra) and query revenue metrics',
        tags: ['Pesanan (Mitra)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', description: 'Filter by order status', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'paid', 'cancelled', 'refunded'])),
            new OA\Parameter(name: 'basecamp_id', in: 'query', description: 'Filter by specific basecamp ID owned by Mitra', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'tanggal_booking', in: 'query', description: 'Filter by booking date (Y-m-d)', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'search', in: 'query', description: 'Search by invoice number, user name or member name', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number for pagination', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Orders and revenue metrics retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Mitra orders fetched successfully.'),
                        new OA\Property(property: 'meta', type: 'object', properties: [
                            new OA\Property(property: 'total_pendapatan_bersih', type: 'number', format: 'float', example: 250000.00),
                            new OA\Property(property: 'total_transaksi_paid', type: 'integer', example: 5),
                        ]),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PesananResource')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - only for role mitra'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $mitra = $request->user()->mitra;

        if (! $mitra) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil Mitra tidak ditemukan.',
            ], 403);
        }

        $basecampIds = $mitra->basecamps->pluck('id');

        $query = Pesanan::whereIn('basecamp_id', $basecampIds)
            ->with(['user', 'basecamp', 'jalur', 'anggotas', 'details.produk', 'pembayaran']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('basecamp_id')) {
            // Ensure basecamp_id belongs to the Mitra
            if ($basecampIds->contains($request->query('basecamp_id'))) {
                $query->where('basecamp_id', $request->query('basecamp_id'));
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk basecamp ini.',
                ], 403);
            }
        }

        if ($request->filled('tanggal_booking')) {
            $query->whereDate('tanggal_booking', $request->query('tanggal_booking'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('anggotas', function ($aq) use ($search) {
                        $aq->where('nama_anggota', 'like', "%{$search}%");
                    });
            });
        }

        // Calculate Revenue Metrics for PAID transactions
        // Note: We use a separate query on the filtered basecamp set to sum up paid revenue metrics
        $paidRevenueStats = Pesanan::whereIn('basecamp_id', $basecampIds)
            ->where('status', 'paid')
            ->selectRaw('SUM(pendapatan_mitra) as total_revenue, COUNT(id) as paid_count')
            ->first();

        // Paginate results
        $pesanans = $query->latest()->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Mitra orders fetched successfully.',
            'meta' => [
                'total_pendapatan_bersih' => (float) ($paidRevenueStats->total_revenue ?? 0.00),
                'total_transaksi_paid' => (int) ($paidRevenueStats->paid_count ?? 0),
            ],
            'data' => PesananResource::collection($pesanans)->response()->getData(true),
        ]);
    }

    #[OA\Get(
        path: '/api/mitra/pesanan/{id}',
        summary: 'Get details of a specific incoming booking (Mitra)',
        tags: ['Pesanan (Mitra)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Order (Pesanan) ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order details fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Order details fetched successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PesananResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - only for role mitra or owned basecamp'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $pesanan = Pesanan::with(['user', 'basecamp', 'jalur', 'anggotas', 'details.produk', 'pembayaran'])
            ->findOrFail($id);

        Gate::authorize('view', $pesanan);

        return response()->json([
            'status' => 'success',
            'message' => 'Order details fetched successfully.',
            'data' => new PesananResource($pesanan),
        ]);
    }

    #[OA\Patch(
        path: '/api/mitra/pesanan/{pesananId}/items/{itemId}',
        summary: 'Update the operational status of an item in a booking (Mitra)',
        tags: ['Pesanan (Mitra)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'pesananId', in: 'path', description: 'Order ID', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'itemId', in: 'path', description: 'Detail Pesanan (Item) ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status_operasional'],
                properties: [
                    new OA\Property(property: 'status_operasional', type: 'string', enum: ['pending', 'ready', 'active', 'completed', 'cancelled'], example: 'ready'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Operational status updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Item operational status updated successfully.'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Order or Item not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateItemStatus(Request $request, int $pesananId, int $itemId): JsonResponse
    {
        $pesanan = Pesanan::findOrFail($pesananId);

        Gate::authorize('update', $pesanan);

        $detail = DetailPesanan::where('pesanan_id', $pesanan->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $request->validate([
            'status_operasional' => ['required', 'string', 'in:pending,ready,active,completed,cancelled'],
        ], [
            'status_operasional.in' => 'Status operasional tidak valid.',
        ]);

        $detail->update([
            'status_operasional' => $request->input('status_operasional'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item operational status updated successfully.',
        ]);
    }
}
