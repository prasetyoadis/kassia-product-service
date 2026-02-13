<?php

namespace App\Http\Controllers;

use App\Helpers\GeneralResponse;
use App\Http\Requests\ProductImage\StoreImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductImageController extends Controller
{
    public function __construct(
        protected ProductImageService $productImageService
    ) {}
    
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImageRequest $request, Product $product)
    {
        try {
            $payload = $request->validated();

            $result = $this->productImageService->store(
                productId: $product->id,
                productName: $product->name,
                image: $payload['image'],
                alt: $payload['alt'] ?? null
            );

            return GeneralResponse::success(
                statusCode: 201,
                errorCode: 30,
                data: $result
            );
        } catch (\Throwable $th) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '44'
            );
        }
    }

    /**
     * Display the specified resource.
     */
    // public function show(ProductImage $productImage)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, ProductImage $productImage)
    {
        try {
            $payload = $request->validated();

            $image = $this->productImageService->update(
                productId: $product->id,
                productImage: $productImage,
                image: $payload['image'],
                alt: $payload['alt'] ?? null
            );

            return GeneralResponse::success(
                statusCode: 200,
                errorCode: 32,
                data: ['id' => $image->id]
            );
        } catch (\Throwable $th) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '44'
            );
        }
    }

    /**
     * Set is_default true the specified resource in storage.
     */
    public function setDefault(Product $product, ProductImage $productImage)
    {
        try {
            $image = $this->productImageService->setDefault(
                productId: $product->id,
                image: $productImage
            );

            return GeneralResponse::success(
                statusCode: 200,
                errorCode: 32,
                data: ['id' => $image->id]
            );
        } catch (\Throwable $th) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '44'
            );
        }

        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, ProductImage $productImage)
    {
        try {

            $this->productImageService->destroy(
                productId: $product->id,
                image: $productImage
            );

            return GeneralResponse::success(
                statusCode: 201,
                errorCode: 33,
            );
        } catch (\Throwable $th) {
            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '44'
            );
        }
    }
}
