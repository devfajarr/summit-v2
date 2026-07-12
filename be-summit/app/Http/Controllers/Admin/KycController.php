<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kyc\VerifyKycRequest;
use App\Http\Resources\PendakiResource;
use App\Models\Pendaki;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KycController extends Controller
{
    #[OA\Get(
        path: '/api/admin/kyc',
        summary: 'List all climber KYC profiles (Admin)',
        tags: ['KYC (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', description: 'Filter by verification status', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'disetujui', 'ditolak'])),
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number for pagination', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'KYC profiles fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'KYC profiles fetched successfully.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PendakiResource')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - only for admin'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Pendaki::query()->with('user');

        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->query('status'));
        }

        $pendakis = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'KYC profiles fetched successfully.',
            'data' => PendakiResource::collection($pendakis)->response()->getData(true),
        ]);
    }

    #[OA\Get(
        path: '/api/admin/kyc/{id}',
        summary: 'Get details of a specific KYC profile (Admin)',
        tags: ['KYC (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'KYC / Pendaki Profile ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'KYC profile fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'KYC profile details fetched successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PendakiResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - only for admin'),
            new OA\Response(response: 404, description: 'KYC profile not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $pendaki = Pendaki::with('user')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'KYC profile details fetched successfully.',
            'data' => new PendakiResource($pendaki),
        ]);
    }

    #[OA\Get(
        path: '/api/admin/kyc/{id}/download-document',
        summary: 'Download climber uploaded identity document file securely (Admin)',
        tags: ['KYC (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'KYC / Pendaki Profile ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Secure file download response',
                content: new OA\MediaType(mediaType: 'application/octet-stream')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - only for admin'),
            new OA\Response(response: 404, description: 'KYC profile or document file not found'),
        ]
    )]
    public function downloadDocument(int $id): BinaryFileResponse
    {
        $pendaki = Pendaki::findOrFail($id);

        if (! $pendaki->foto_identitas || ! Storage::disk('local')->exists($pendaki->foto_identitas)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        return Storage::disk('local')->download($pendaki->foto_identitas);
    }

    #[OA\Post(
        path: '/api/admin/kyc/{id}/verify',
        summary: 'Verify (approve or reject) a climber KYC profile (Admin)',
        tags: ['KYC (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'KYC / Pendaki Profile ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status_verifikasi'],
                properties: [
                    new OA\Property(property: 'status_verifikasi', type: 'string', enum: ['disetujui', 'ditolak'], example: 'disetujui'),
                    new OA\Property(property: 'alasan_penolakan', type: 'string', example: 'Foto KTP buram dan tidak terbaca.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'KYC profile verified successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'KYC profile verified successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PendakiResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - only for admin'),
            new OA\Response(response: 404, description: 'KYC profile not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function verify(VerifyKycRequest $request, int $id): JsonResponse
    {
        $pendaki = Pendaki::findOrFail($id);
        $validated = $request->validated();

        if ($validated['status_verifikasi'] === 'disetujui') {
            $pendaki->update([
                'status_verifikasi' => 'disetujui',
                'verified_at' => now(),
                'verified_by' => $request->user()->id,
                'alasan_penolakan' => null,
            ]);
        } else {
            $pendaki->update([
                'status_verifikasi' => 'ditolak',
                'alasan_penolakan' => $validated['alasan_penolakan'],
                'verified_at' => null,
                'verified_by' => $request->user()->id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'KYC profile verified successfully.',
            'data' => new PendakiResource($pendaki),
        ]);
    }
}
