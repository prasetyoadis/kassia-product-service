<?php

namespace App\Http\Controllers;

use App\Helpers\GeneralResponse;
use App\Http\Requests\ProductVariant\AdjustVariantStockRequest;
use App\Http\Requests\ProductVariant\UpdateVariantStockRequest;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\VariantInventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InventoryItemController extends Controller
{
    public function __construct(
        protected VariantInventoryService $variantInventoryService
    ) {}

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    public function summary(Request $request)
    {
        $userId = $request->get('jwt_payload')->get('sub');
        $outletId = Cache::get("active_outlet:user:{$userId}") ?? env('TEST_ACTIVE_OUTLET');
        $today    = Carbon::today();

        try {
            $summary = InventoryItem::query()
                ->where('inventory_items.outlet_id', $outletId)
                ->selectRaw('
                    COUNT(*) as total_products,
                    COUNT(CASE 
                        WHEN current_stock <= min_stock AND current_stock > 0 
                        THEN 1 END) as low_stock,
                    COUNT(CASE 
                        WHEN current_stock = 0 
                        THEN 1 END) as out_of_stock
                ')
                ->selectSub(function ($q) use ($outletId, $today) {
                    $q->from('inventory_logs')
                        ->selectRaw('COALESCE(SUM(quantity),0)')
                        ->where('inventory_logs.outlet_id', $outletId)
                        ->where('inventory_logs.type', InventoryLog::TYPE_IN)
                        ->whereDate('inventory_logs.created_at', $today);
                }, 'restock_today')
                ->selectSub(function ($q) use ($outletId, $today) {
                    $q->from('inventory_logs')
                        ->selectRaw('COALESCE(SUM(quantity),0)')
                        ->where('inventory_logs.outlet_id', $outletId)
                        ->where('inventory_logs.type', InventoryLog::TYPE_OUT)
                        ->whereDate('inventory_logs.created_at', $today);
                }, 'reduce_today')
                ->first();
            
            return GeneralResponse::success(
                errorCode: '31',
                data: [
                    'total_products' => (int) $summary->total_products,
                    'low_stock' => (int) $summary->low_stock,
                    'out_of_stock' => (int) $summary->out_of_stock,
                    'restock_today' => (int) $summary->restock_today,
                    'reduce_today' => (int) $summary->reduce_today,
                ]
            );
        }  catch (\Throwable $e) {
            Log::error('kassia-product-service.InventoryItemController@summary error', [
                'service' => 'InventoryItemController',
                'action'  => 'summary',
                'payload' => ['outlet_id' => $outletId],
                'error'   => $e->getMessage(),
            ]);

            return GeneralResponse::error(
                statusCode: 500,
                errorCode: '70'
            );
        }
        
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    // public function show(InventoryItem $inventoryItem)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function updateStock(UpdateVariantStockRequest $request, Product $product, ProductVariant $productVariant)
    {
        try {
            $inventory = $this->variantInventoryService->updateStock(
                productId: $product->id,
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
    public function adjustStock(AdjustVariantStockRequest $request, Product $product, ProductVariant $productVariant)
    {
        try {
            $inventory = $this->variantInventoryService->adjustStock(
                productId: $product->id,
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
    // public function destroy(InventoryItem $inventoryItem)
    // {
    //     //
    // }
}
