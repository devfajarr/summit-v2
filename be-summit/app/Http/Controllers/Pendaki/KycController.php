<?php

namespace App\Http\Controllers\Pendaki;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kyc\SubmitKycRequest;
use App\Http\Resources\PendakiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class KycController extends Controller
{
    #[OA\Post(
        path: '/api/kyc/submit',
        summary: 'Submit KYC climber profile identity details and upload identity photo',
        tags: ['KYC (Climber)'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['nama_lengkap', 'jenis_identitas', 'nomor_identitas', 'foto_identitas', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'telepon', 'nama_kontak_darurat', 'telepon_darurat', 'hubungan_darurat'],
                    properties: [
                        new OA\Property(property: 'nama_lengkap', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'jenis_identitas', type: 'string', enum: ['ktp', 'paspor', 'sim', 'lainnya'], example: 'ktp'),
                        new OA\Property(property: 'nomor_identitas', type: 'string', example: '1234567890123456'),
                        new OA\Property(property: 'foto_identitas', type: 'string', format: 'binary', description: 'Upload file KTP/Passport (JPEG/PNG, max 2MB)'),
                        new OA\Property(property: 'tanggal_lahir', type: 'string', format: 'date', example: '2000-01-01'),
                        new OA\Property(property: 'jenis_kelamin', type: 'string', enum: ['l', 'p'], example: 'l'),
                        new OA\Property(property: 'alamat', type: 'string', example: 'Jl. Merdeka No. 45'),
                        new OA\Property(property: 'telepon', type: 'string', example: '081234567890'),
                        new OA\Property(property: 'nama_kontak_darurat', type: 'string', example: 'Jane Doe'),
                        new OA\Property(property: 'telepon_darurat', type: 'string', example: '081298765432'),
                        new OA\Property(property: 'hubungan_darurat', type: 'string', example: 'Istri'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'KYC identity submitted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'KYC identity submitted successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PendakiResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - only for role pendaki'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function submit(SubmitKycRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $request->user();
        $existingPendaki = $user->pendaki;

        // Handle file upload
        if ($request->hasFile('foto_identitas')) {
            // Delete old file if exists
            if ($existingPendaki && $existingPendaki->foto_identitas) {
                Storage::disk('local')->delete($existingPendaki->foto_identitas);
            }

            // Store in private/secured storage
            $path = $request->file('foto_identitas')->store('kyc_documents', 'local');
            $validatedData['foto_identitas'] = $path;
        }

        $pendaki = $user->pendaki()->updateOrCreate(
            [],
            array_merge($validatedData, [
                'status_verifikasi' => 'pending',
                'alasan_penolakan' => null,
            ])
        );

        return response()->json([
            'status' => 'success',
            'message' => 'KYC identity submitted successfully.',
            'data' => new PendakiResource($pendaki),
        ]);
    }

    #[OA\Get(
        path: '/api/kyc/status',
        summary: 'Get climber current KYC status and profile',
        tags: ['KYC (Climber)'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'KYC status retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'KYC status retrieved successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PendakiResource', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function status(Request $request): JsonResponse
    {
        $pendaki = $request->user()->pendaki;

        return response()->json([
            'status' => 'success',
            'message' => 'KYC status retrieved successfully.',
            'data' => $pendaki ? new PendakiResource($pendaki) : null,
        ]);
    }
}
