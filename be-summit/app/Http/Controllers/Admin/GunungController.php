<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gunung\StoreGunungRequest;
use App\Http\Requests\Gunung\UpdateGunungRequest;
use App\Http\Resources\GunungResource;
use App\Models\Gunung;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class GunungController extends Controller
{
    #[OA\Post(
        path: '/api/admin/gunung',
        summary: 'Create a new mountain profile (Admin)',
        tags: ['Mountain (Admin)'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['nama_gunung', 'deskripsi', 'tinggi_mdpl', 'lokasi', 'foto', 'status'],
                    properties: [
                        new OA\Property(property: 'nama_gunung', type: 'string', example: 'Gunung Gede'),
                        new OA\Property(property: 'deskripsi', type: 'string', example: 'Gunung berapi aktif di Jawa Barat.'),
                        new OA\Property(property: 'tinggi_mdpl', type: 'integer', example: 2958),
                        new OA\Property(property: 'lokasi', type: 'string', example: 'Cianjur/Sukabumi/Bogor, Jawa Barat'),
                        new OA\Property(property: 'foto', type: 'string', format: 'binary', description: 'Mountain picture file (JPEG/PNG, max 2MB)'),
                        new OA\Property(property: 'status', type: 'string', example: 'aktif'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Mountain created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Mountain created successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/GunungResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreGunungRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('gunungs', 'public');
            $validated['foto'] = $path;
        }

        $gunung = Gunung::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Mountain created successfully.',
            'data' => new GunungResource($gunung),
        ], 201);
    }

    #[OA\Post(
        path: '/api/admin/gunung/{id}',
        summary: 'Update an existing mountain profile (Admin) [Uses POST with _method=PUT]',
        tags: ['Mountain (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Mountain ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['nama_gunung', 'deskripsi', 'tinggi_mdpl', 'lokasi', 'status'],
                    properties: [
                        new OA\Property(property: '_method', type: 'string', default: 'PUT'),
                        new OA\Property(property: 'nama_gunung', type: 'string', example: 'Gunung Gede Baru'),
                        new OA\Property(property: 'deskripsi', type: 'string', example: 'Gunung berapi di Jawa Barat dengan pemandangan indah.'),
                        new OA\Property(property: 'tinggi_mdpl', type: 'integer', example: 2958),
                        new OA\Property(property: 'lokasi', type: 'string', example: 'Cianjur, Jawa Barat'),
                        new OA\Property(property: 'foto', type: 'string', format: 'binary', description: 'Optional new mountain picture file (JPEG/PNG, max 2MB)'),
                        new OA\Property(property: 'status', type: 'string', example: 'aktif'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mountain updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Mountain updated successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/GunungResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Mountain not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateGunungRequest $request, int $id): JsonResponse
    {
        $gunung = Gunung::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            // Delete old file if exists in public storage
            if ($gunung->foto) {
                Storage::disk('public')->delete($gunung->foto);
            }

            $path = $request->file('foto')->store('gunungs', 'public');
            $validated['foto'] = $path;
        }

        $gunung->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Mountain updated successfully.',
            'data' => new GunungResource($gunung->fresh()),
        ]);
    }

    #[OA\Delete(
        path: '/api/admin/gunung/{id}',
        summary: 'Delete a mountain profile (Admin)',
        tags: ['Mountain (Admin)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Mountain ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mountain deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Mountain deleted successfully.'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Mountain not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $gunung = Gunung::findOrFail($id);

        // Delete photo from storage if exists
        if ($gunung->foto) {
            Storage::disk('public')->delete($gunung->foto);
        }

        $gunung->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Mountain deleted successfully.',
            'data' => null,
        ]);
    }
}
