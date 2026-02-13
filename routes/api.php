<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductVariantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.verify'])->group(function () {
    Route::prefix('categories')
        ->middleware(['checkRole:owner,admin,cashier'])
        ->group(function () {
            # View semua category
            Route::get('/', [CategoryController::class, 'index'])
                ->middleware('checkPermission')
                ->name('category.view');
            # View single category
            Route::get('/{category}', [CategoryController::class, 'show'])
                ->middleware('checkPermission')
                ->name('category.view');
        });
    Route::prefix('categories')
        ->middleware(['checkRole:owner,admin'])
        ->group(function () {
            # Create new category
            Route::post('/', [CategoryController::class, 'store'])
                ->middleware('checkPermission')
                ->name('category.create');
            # Update category
            Route::patch('/{category}', [CategoryController::class, 'update'])
                ->middleware('checkPermission')
                ->name('category.update');
            # Delete single category
            Route::delete('/{category}', [CategoryController::class, 'destroy'])
                ->middleware('checkPermission')
                ->name('category.delete');
        });
    Route::prefix('products')
        ->middleware('checkRole:owner,admin,cashier')
        ->group(function (){
            # View semua product
            Route::get('/', [ProductController::class, 'index'])
                ->middleware('checkPermission')
                ->name('product.view');
            // /** GET DATA FOR CASHIER */
            // Route::get('/simple', [ProductController::class, 'indexSimple'])
            //     ->middleware('checkPermission')
            //     ->name('product.view');
            # View single product
            Route::get('/{product}', [ProductController::class, 'show'])
                ->middleware('checkPermission')
                ->name('product.view');
        });

    Route::prefix('products')
        ->middleware(['checkRole:owner,admin'])
        ->group(function () {
            # Create new product
            Route::post('/', [ProductController::class, 'store'])
                ->middleware('checkPermission')
                ->name('product.create');

             /** CREATE PRODUCT VARIANT */
            # Create new variant product
            Route::post('/{product}/variants', [ProductVariantController::class, 'store'])
                ->middleware('checkPermission')
                ->name('product.create');

             /** CREATE PRODUCT IMAGE */
            # Create new product_image
            Route::post('/{product}/images/', [ProductImageController::class, 'store'])
                ->middleware('checkPermission')
                ->name('product.create');
            
            # Update single product
            Route::patch('/{product}', [ProductController::class, 'update'])
                ->middleware('checkPermission')
                ->name('product.update');
           
            /** UPDATE DATA VARIANT */
            # Update single product_variant
            Route::patch('/{product}/variants/{productVariant}', [ProductVariantController::class, 'update'])
                ->middleware('checkPermission')
                ->name('product.update');

            /** UPDATE INVENTORY VARIANT */
            # Update stock in/out
            // Route::patch('/{product}/variants/{productVariant}/stock', [InventoryItemController::class, 'updateStock'])
            //     ->middleware('checkPermission')
            //     ->name('inventory.update');
            # Update stock correction
            // Route::patch('/{product}/variants/{productVariant}/stock/adjust', [InventoryItemController::class, 'adjustStock'])
            //     ->middleware('checkPermission')
            //     ->name('inventory.update');
            
            /** UPDATE IMAGE PRODUCT */
            # Update single product_image
            Route::patch('/{product}/images/{productImage}', [ProductImageController::class, 'update'])
                ->middleware('checkPermission')
                ->name('product.update');
            # Set defalut image product
            Route::patch('/{product}/images/{productImage}/set-default', [ProductImageController::class, 'setDefault'])
                ->middleware('checkPermission')
                ->name('product.update');

            /** ROUTE DELETE */
            # Soft Delete single product
            Route::delete('/{product}', [ProductController::class, 'destroy'])
                ->middleware('checkPermission')
                ->name('product.delete');
            # Soft Delete single product_variant
            Route::delete('/{product}/variants/{productVariant}', [ProductVariantController::class, 'destroy'])
                ->middleware('checkPermission')
                ->name('product.delete');
            # Hard Delete single product_image
            Route::delete('/{product}/images/{productImage}', [ProductImageController::class, 'destroy'])
                ->middleware('checkPermission')
                ->name('product.delete');
            # Soft Delete bulk product
            Route::delete('/', [ProductController::class, 'bulkDestroy'])
                ->middleware('checkPermission')
                ->name('product.delete');
        });

    /** STOCK OVERVIEW */
    Route::prefix('variants')
        ->middleware(['checkRole:owner,admin'])
        ->group(function () {
            Route::get('/', [ProductVariantController::class, 'index'])
                ->middleware('checkPermission')
                ->name('product.variant.view');
            # Update stock in/out
            Route::patch('/{productVariant}/stock', [ProductVariantController::class, 'updateStock'])
                ->middleware('checkPermission')
                ->name('inventory.update');
            # Update stock correction
            Route::patch('/{productVariant}/stock/adjust', [ProductVariantController::class, 'adjustStock'])
                ->middleware('checkPermission')
                ->name('inventory.update');
        });

    Route::GET('/inventories/summary', [InventoryItemController::class, 'summary'])
        ->middleware(['checkRole:owner,admin', 'checkPermission'])
        ->name('inventory.view');
});

