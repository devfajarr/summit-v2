<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Jalur\StoreJalurRequest;
use App\Http\Requests\Jalur\UpdateJalurRequest;
use App\Http\Resources\JalurPendakianResource;
use App\Models\JalurPendakian;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class JalurController extends Controller
{
    #[OA\Post(
        path: '/api/admin/jalur',
        summary: 'Create a new trail for a mountain (Admin)',
        tags: ['Trail (Admin)'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['gunung_id', 'nama_jalur', 'deskripsi', 'titik_awal_mdpl', 'titik_akhir_mdpl', 'waktu_tempuh', 'status', 'panjang_jalur', 'tingkat_kesulitan'],
                properties: [
                    new OA\Property(property: 'gunung_id', type: 'integer', example: 1),
                    new OA\Property(property: 'nama_jalur', type: 'string', example: 'Jalur Cibodas'),
                    new OA\Property(property: 'deskripsi', type: 'string', example: 'Jalur pendakian yang berbatu dan terdapat air panas.'),
                    new OA\Property(property: 'titik_awal_mdpl', type: 'string', example: '1300 MDPL'),
                    new OA\Property(property: 'titik_akhir_mdpl', type: 'string', example: '2958 MDPL'),
                    new OA\Property(property: 'waktu_tempuh', type: 'string', example: '7 Jam'),
                    new OA\Property(property: 'status', type: 'string', enum: ['open', 'close'], example: 'open'),
                    new OA\Property(property: 'panjang_jalur', type: 'string', example: '9.7 Km'),
                    new OA\Property(property: 'tingkat_kesulitan', type: 'string', enum: ['mudah', 'sedang', 'sulit', 'ekstrem'], example: 'sedang'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Trail created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Trail created successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/JalurPendakianResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreJalurRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $jalur = JalurPendakian::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Trail created successfully.',
            'data' => new JalurPendakianResource($jalur),
        ], 201);
    }

    #[OA\Put(
        path: '/api/admin/jalur/{id}',
        summary: 'Update an existing trail (Admin)',
        tags: ['Trail (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Trail ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['gunung_id', 'nama_jalur', 'deskripsi', 'titik_awal_mdpl', 'titik_akhir_mdpl', 'waktu_tempuh', 'status', 'panjang_jalur', 'tingkat_kesulitan'],
                properties: [
                    new OA\Property(property: 'gunung_id', type: 'integer', example: 1),
                    new OA\Property(property: 'nama_jalur', type: 'string', example: 'Jalur Cibodas Baru'),
                    new OA\Property(property: 'deskripsi', type: 'string', example: 'Jalur dengan fasilitas lengkap.'),
                    new OA\Property(property: 'titik_awal_mdpl', type: 'string', example: '1300 MDPL'),
                    new OA\Property(property: 'titik_akhir_mdpl', type: 'string', example: '2958 MDPL'),
                    new OA\Property(property: 'waktu_tempuh', type: 'string', example: '6 Jam'),
                    new OA\Property(property: 'status', type: 'string', enum: ['open', 'close'], example: 'open'),
                    new OA\Property(property: 'panjang_jalur', type: 'string', example: '9.7 Km'),
                    new OA\Property(property: 'tingkat_kesulitan', type: 'string', enum: ['mudah', 'sedang', 'sulit', 'ekstrem'], example: 'sedang'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Trail updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Trail updated successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/JalurPendakianResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Trail not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateJalurRequest $request, int $id): JsonResponse
    {
        $jalur = JalurPendakian::findOrFail($id);
        $validated = $request->validated();

        $jalur->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Trail updated successfully.',
            'data' => new JalurPendakianResource($jalur->fresh()),
        ]);
    }

    #[OA\Delete(
        path: '/api/admin/jalur/{id}',
        summary: 'Delete a trail (Admin)',
        tags: ['Trail (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Trail ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Trail deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Trail deleted successfully.'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Trail not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $jalur = JalurPendakian::findOrFail($id);
        $jalur->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Trail deleted successfully.',
            'data' => null,
        ]);
    }
}
