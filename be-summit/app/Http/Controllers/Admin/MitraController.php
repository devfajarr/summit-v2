<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mitra\StoreMitraRequest;
use App\Http\Requests\Mitra\UpdateMitraRequest;
use App\Http\Resources\MitraResource;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class MitraController extends Controller
{
    #[OA\Get(
        path: '/api/admin/mitra',
        summary: 'List all partner (mitra) profiles (Admin)',
        tags: ['Partner (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number for pagination', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Partners fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Partners fetched successfully.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MitraResource')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $mitras = Mitra::with('user')->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Partners fetched successfully.',
            'data' => MitraResource::collection($mitras)->response()->getData(true),
        ]);
    }

    #[OA\Post(
        path: '/api/admin/mitra',
        summary: 'Create a new partner (mitra) profile and user account (Admin)',
        tags: ['Partner (Admin)'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'nama_pemilik', 'telepon', 'alamat', 'status', 'nik', 'rekening_bank', 'nama_rekening', 'bank'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'mitra1@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'nama_pemilik', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'telepon', type: 'string', example: '081234567890'),
                    new OA\Property(property: 'alamat', type: 'string', example: 'Jl. Raya Summit No. 10'),
                    new OA\Property(property: 'deskripsi', type: 'string', example: 'Pemilik Basecamp Merbabu Indah'),
                    new OA\Property(property: 'status', type: 'string', enum: ['aktif', 'suspend'], example: 'aktif'),
                    new OA\Property(property: 'npwp', type: 'string', example: '12.345.678.9-012.000'),
                    new OA\Property(property: 'nik', type: 'string', example: '3201234567890001'),
                    new OA\Property(property: 'rekening_bank', type: 'string', example: '1234567890'),
                    new OA\Property(property: 'nama_rekening', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'bank', type: 'string', example: 'Bank BCA'),
                    new OA\Property(property: 'ewallet', type: 'string', example: '081234567890'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Partner created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Partner profile and user account created successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MitraResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreMitraRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $mitra = DB::transaction(function () use ($validated) {
            // Create user account
            $user = User::create([
                'name' => $validated['nama_pemilik'],
                'email' => $validated['email'],
                'password' => $validated['password'], // User model cast handles hashing
                'role' => 'mitra',
            ]);

            // Auto-verify email for admin-created users
            $user->markEmailAsVerified();

            // Create partner profile
            return Mitra::create(array_merge($validated, [
                'user_id' => $user->id,
            ]));
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Partner profile and user account created successfully.',
            'data' => new MitraResource($mitra->load('user')),
        ], 201);
    }

    #[OA\Get(
        path: '/api/admin/mitra/{id}',
        summary: 'Get details of a specific partner (mitra) profile (Admin)',
        tags: ['Partner (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Partner Profile ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Partner details fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Partner details fetched successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MitraResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Partner not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $mitra = Mitra::with('user')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Partner details fetched successfully.',
            'data' => new MitraResource($mitra),
        ]);
    }

    #[OA\Put(
        path: '/api/admin/mitra/{id}',
        summary: 'Update an existing partner (mitra) profile (Admin)',
        tags: ['Partner (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Partner Profile ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'nama_pemilik', 'telepon', 'alamat', 'status', 'nik', 'rekening_bank', 'nama_rekening', 'bank'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'mitra1.updated@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newpassword123'),
                    new OA\Property(property: 'nama_pemilik', type: 'string', example: 'Budi Santoso Updated'),
                    new OA\Property(property: 'telepon', type: 'string', example: '081234567891'),
                    new OA\Property(property: 'alamat', type: 'string', example: 'Jl. Raya Summit No. 12'),
                    new OA\Property(property: 'deskripsi', type: 'string', example: 'Pemilik Basecamp Merbabu Indah Baru'),
                    new OA\Property(property: 'status', type: 'string', enum: ['aktif', 'suspend'], example: 'aktif'),
                    new OA\Property(property: 'npwp', type: 'string', example: '12.345.678.9-012.000'),
                    new OA\Property(property: 'nik', type: 'string', example: '3201234567890001'),
                    new OA\Property(property: 'rekening_bank', type: 'string', example: '1234567890'),
                    new OA\Property(property: 'nama_rekening', type: 'string', example: 'Budi Santoso Updated'),
                    new OA\Property(property: 'bank', type: 'string', example: 'Bank Mandiri'),
                    new OA\Property(property: 'ewallet', type: 'string', example: '081234567891'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Partner updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Partner profile updated successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MitraResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Partner not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateMitraRequest $request, int $id): JsonResponse
    {
        $mitra = Mitra::findOrFail($id);
        $validated = $request->validated();

        $mitra = DB::transaction(function () use ($mitra, $validated) {
            // Update user details
            $user = $mitra->user;
            $userPayload = [
                'name' => $validated['nama_pemilik'] ?? $user->name,
                'email' => $validated['email'] ?? $user->email,
            ];

            if (! empty($validated['password'])) {
                $userPayload['password'] = $validated['password'];
            }

            $user->update($userPayload);

            // Update partner profile
            $mitra->update($validated);

            return $mitra;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Partner profile updated successfully.',
            'data' => new MitraResource($mitra->load('user')),
        ]);
    }

    #[OA\Delete(
        path: '/api/admin/mitra/{id}',
        summary: 'Delete a partner profile and user account (Admin)',
        tags: ['Partner (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Partner Profile ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Partner deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Partner profile and user account deleted successfully.'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Partner not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $mitra = Mitra::findOrFail($id);

        DB::transaction(function () use ($mitra) {
            // Delete user which cascades and deletes the mitra profile due to foreign key
            $mitra->user->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Partner profile and user account deleted successfully.',
            'data' => null,
        ]);
    }
}
