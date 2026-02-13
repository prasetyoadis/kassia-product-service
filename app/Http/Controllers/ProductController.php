<?php

namespace App\Http\Controllers;

use App\Helpers\GeneralResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\BulkDestroyProductRequest;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\AllProductResource;
use App\Http\Resources\Product\SingleProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(IndexProductRequest $request)
    {
        $perPage = $request->integer('per_page', 7);
        $userId = $request->get('jwt_payload')->get('sub');
        $outletId = Cache::get("active_outlet:user:{$userId}") ?? env('TEST_ACTIVE_OUTLET');

        $products = Product::query()
            ->with([
                'categories', 
                'variants.inventoryItem', 
                'defaultImage',
            ])
            ->where('products.outlet_id', $outletId)
            ->withStockSummary()
            ->filter($request->validated())
            ->paginate($perPage);

        return GeneralResponse::success(
            data: AllProductResource::collection($products->items()),
            paginator: $products,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $result = $this->productService->store(
                userId: $request->get('jwt_payload')->get('sub'),
                payload: $request->validated()
            );

            return GeneralResponse::success(
                statusCode: 201,
                errorCode: '30',
                data: ['id' => $result->id]
            );
        } catch (\Throwable $th) {
            return GeneralResponse::error(
                statusCode: 500,
                statusDescription: $th->getMessage(),
                errorCode: '43'
            );
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
        $product->loadMissing([
            'categories', 
            'variants.inventoryItem',
            'variants.inventoryItem.logs:id,inventory_item_id,quantity,total,type,note,created_at',
            'variants.transactionItem:id,product_variant_id,harga_jual',
            'images' => function ($q) {
                $q->orderByDesc('is_default')
                  ->orderByDesc('updated_at');
            },
        ]);

        return GeneralResponse::success(
            data: new SingleProductResource($product),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $product = $this->productService->update(
                product: $product,
                payload: $request->validated()
            );

            return GeneralResponse::success(
                statusCode: 200,
                errorCode: '32',
                data: ['id' => $product->id]
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return GeneralResponse::error(
                statusCode: 404,
                errorCode: '40'
            );

        } catch (\Throwable $e) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '44'
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product = $this->productService->destroy(
                product: $product,
            );

            return GeneralResponse::success(
                statusCode: 200,
                errorCode: '33',
                data: ['id' => $product->id]
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return GeneralResponse::error(
                statusCode: 404,
                errorCode: '40'
            );

        } catch (\Throwable $e) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '45'
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDestroy(BulkDestroyProductRequest $request)
    {
        try {
            $productIds = $request->validated()['product_ids'];

            $this->productService->bulkDestroy(
                productIds: $productIds,
            );

            return GeneralResponse::success(
                statusCode: 200,
                errorCode: '33',
                data: ['id' => array_values($productIds)]
            );

        } catch (\Throwable $e) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '45'
            );
        }
    }
}
