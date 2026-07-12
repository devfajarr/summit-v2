<?php

namespace App\Http\Controllers\Pendaki;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProdukResource;
use App\Models\Produk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/products',
        summary: 'List all active products for climbers (Pendaki)',
        tags: ['Product (Climber)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'basecamp_id', in: 'query', description: 'Filter by basecamp ID', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'kategori', in: 'query', description: 'Filter by product category', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number for pagination', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Products fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Active products fetched successfully.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProdukResource')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Produk::where('is_active', true)->with(['basecamp', 'opentrip', 'tiket.kuotas']);

        if ($request->filled('basecamp_id')) {
            $query->where('basecamp_id', $request->query('basecamp_id'));
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->query('kategori'));
        }

        $products = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Active products fetched successfully.',
            'data' => ProdukResource::collection($products)->response()->getData(true),
        ]);
    }

    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Get details of an active product for climbers (Pendaki)',
        tags: ['Product (Climber)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Product ID', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product details fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product details fetched successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/ProdukResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $produk = Produk::where('is_active', true)
            ->with(['basecamp', 'opentrip', 'tiket.kuotas'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Product details fetched successfully.',
            'data' => new ProdukResource($produk),
        ]);
    }
}
