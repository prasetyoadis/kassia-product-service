<?php

namespace App\Http\Controllers;

use App\Helpers\GeneralResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductVariant\AdjustVariantStockRequest;
use App\Http\Requests\ProductVariant\IndexVariantRequest;
use App\Http\Requests\ProductVariant\StoreVariantRequest;
use App\Http\Requests\ProductVariant\UpdateVariantRequest;
use App\Http\Requests\ProductVariant\UpdateVariantStockRequest;
use App\Http\Resources\Cashier\AllVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ProductVariantController extends Controller
{
    public function __construct(
        protected ProductVariantService $productVariantService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(IndexVariantRequest $request)
    {
        $perPage = $request->integer('per_page', 7);
        $userId = $request->get('jwt_payload')->get('sub');
        $outletId = Cache::get("active_outlet:user:{$userId}") ?? env('TEST_ACTIVE_OUTLET');

        $variants = ProductVariant::query()
            ->with([
                'product:id,outlet_id,name,is_variant',
                'product.categories:id,name',
                'product.defaultImage:id,product_id,url,alt',
                'inventoryItem:id,product_variant_id,current_stock,min_stock',
            ])
            ->where('is_active', true)
            ->whereHas('product', fn ($q) =>
                $q->where('outlet_id', $outletId)
            )
            ->filter($request->validated())
            ->paginate($perPage);
        
        return GeneralResponse::success(
            data: AllVariantResource::collection($variants->items()),
            paginator: $variants,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVariantRequest $request, Product $product)
    {
        //
        try {
            $result = $this->productVariantService->store(
                product: $product,
                outletId: (string) $product->outlet_id,
                payload: $request->validated()
            );

            return GeneralResponse::success(
                statusCode: 201,
                data: [
                    'id' => ($result instanceof ProductVariant) 
                        ? $result->id 
                        : Arr::pluck($result, 'id')
                ],
                errorCode: '30'
            );
        } catch (\Throwable $th) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '43'
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product, ProductVariant $productVariant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVariantRequest $request, Product $product, ProductVariant $productVariant)
    {
        try {
            $variant = $this->productVariantService->update(
                product: $product,
                variant: $productVariant,
                payload: $request->validated()
            );

            return GeneralResponse::success(
                statusCode: 200,
                data: ['id' => $variant->id],
                errorCode: '32'
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return GeneralResponse::error(
                statusCode: 404,
                errorCode: '40'
            );

        } catch (\DomainException $e) {
            return GeneralResponse::error(
                statusCode: 409,
                errorCode: '48' // Data locked / conflict
            );

        } catch (\Throwable $e) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '44'
            );
        }
    }
    
    public function updateStock(UpdateVariantStockRequest $request, Product $product, ProductVariant $productVariant)
    {
        try {
            $inventory = $this->productVariantService->updateStock(
                variantId: $productVariant->id,
                userId: $request->get('jwt_payload')->get('sub'),
                payload: $request->validated()
            );

            return GeneralResponse::success(
                statusCode: 200,
                errorCode: '32',
                data: ['id' => $productVariant->id, 'stock' => $inventory->current_stock]
            );

        } catch (\DomainException $e) {
            return GeneralResponse::error(
                statusCode: 409,
                errorCode: '69' // Stock insufficient
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return GeneralResponse::error(
                statusCode: 404,
                errorCode: '40'
            );

        } catch (\Throwable $e) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '46'
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function adjustStock(AdjustVariantStockRequest $request, ProductVariant $productVariant)
    {
        try {
            $inventory = $this->productVariantService->adjustStock(
                variantId: $productVariant->id,
                userId: $request->get('jwt_payload')->get('sub'),
                payload: $request->validated()
            );

            return GeneralResponse::success(
                statusCode: 200,
                errorCode: '32',
                data: ['id' => $productVariant->id, 'stock' => $inventory->current_stock]
            );

        } catch (\DomainException $e) {
            return GeneralResponse::error(
                statusCode: 409,
                errorCode: '69' // Stock insufficient
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return GeneralResponse::error(
                statusCode: 404,
                errorCode: '40'
            );

        } catch (\Throwable $e) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '46'
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, ProductVariant $productVariant)
    {
        try {
            $variant = $this->productVariantService->destroy(
                product: $product,
                variant: $productVariant
            );

            return GeneralResponse::success(
                statusCode: 200,
                data: ['id' => $variant->id],
                errorCode: '33'
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return GeneralResponse::error(
                statusCode: 404,
                errorCode: '40'
            );

        } catch (\DomainException $e) {
            return GeneralResponse::error(
                statusCode: 409,
                errorCode: '48'
            );

        } catch (\Throwable $e) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '45'
            );
        }
    }
}
