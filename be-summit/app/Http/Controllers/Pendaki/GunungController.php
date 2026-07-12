<?php

namespace App\Http\Controllers\Pendaki;

use App\Http\Controllers\Controller;
use App\Http\Resources\GunungResource;
use App\Models\Gunung;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class GunungController extends Controller
{
    #[OA\Get(
        path: '/api/gunung',
        summary: 'List all mountains with their trails (Climber)',
        tags: ['Mountain (Climber)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number for pagination', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mountains fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Mountains fetched successfully.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/GunungResource')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        // Eager load jalurs to avoid N+1 query problems
        $gunungs = Gunung::with('jalurs')->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Mountains fetched successfully.',
            'data' => GunungResource::collection($gunungs)->response()->getData(true),
        ]);
    }

    #[OA\Get(
        path: '/api/gunung/{id}',
        summary: 'Get details of a specific mountain and its trails (Climber)',
        tags: ['Mountain (Climber)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Mountain ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mountain details fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Mountain details fetched successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/GunungResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Mountain not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $gunung = Gunung::with('jalurs')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Mountain details fetched successfully.',
            'data' => new GunungResource($gunung),
        ]);
    }
}
