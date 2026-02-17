<?php

namespace App\Http\Controllers;

use App\Helpers\GeneralResponse;
use App\Http\Requests\Category\IndexCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCategoryRequest $request)
    {
        $userId = $request->get('jwt_payload')->get('sub');

        # Get outlet user select now
        $outletId = Cache::get("active_outlet:user:{$userId}");
        
        $perPage = $request->integer('per_page', 7);
        # Get category by outlet user select
        $categories = Category::where('outlet_id', $outletId)
            ->filter($request->validated())
            ->orderBy('created_at', 'asc')
            ->get();

        if (!$categories) {
            return GeneralResponse::error(
                statusCode: 404,
                errorCode: '40',
            );
        }
        # Return json request data $categories
        return GeneralResponse::success(
            data: CategoryResource::collection($categories),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        # Validate formrequest with StoreCategoryRequest.
        $dataValid = $request->validated();

        $category = Category::create($dataValid);

        # Return json request data $category->id.
        return GeneralResponse::success(
            statusCode: 201,
            errorCode: '30',
            data: ['id' => $category->id]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
        return GeneralResponse::success(
            errorCode: '31',
            data: new CategoryResource($category)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        # Validate formrequest with UpdateCategoryRequest.
        $dataValid = $request->validated();

        $category->update($dataValid);

        return GeneralResponse::success(
            errorCode: '32',
            data: ['id' => $category->id]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
        $category->delete();

        return GeneralResponse::success(
            statusCode: 200,
            errorCode: '33',
            data: ['id' => $category->id]
        );
    }
}
