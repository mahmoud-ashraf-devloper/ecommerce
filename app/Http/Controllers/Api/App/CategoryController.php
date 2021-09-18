<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCollection;
use App\Models\Category;
use App\Traits\ApiResponse;
use Exception;
use Facade\FlareClient\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends Controller
{
    use ApiResponse;

/**
*   if ($exception instanceof ModelNotFoundException && $request->wantsJson()) {
*   return response()->json([
*        'error' => 'Resource not found'
*      ], 404);
*   }
*/
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $categories = new CategoryCollection(Category::with('children')->paginate(env('CATEGORY_PAGINATION',10)));
            return $this->success($categories);

        } catch (\Exception $ex) {
            return $this->error($ex->getMessage(), 'Something went Wrong', $ex->getCode());
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $rules = [
            'category' => 'required|unique:categories|string|max:255',
            'parent_id' => 'exists:categories,id'
        ];
        
        $validator = Validator::make($request->only(['category','parent_id']),$rules);

        if($validator->fails()){
            return $this->error($validator->errors(),'Validation Error', 400);
        }
        try {
            $createdUpdated = Category::create($request->all());
            if($createdUpdated){
                return $this->success(new CategoryResource($createdUpdated), 'Category Created Successfully');
            }else{
                return $this->error([],'Server Error', 500);
            }
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage(), 'Something went Wrong', $ex->getCode());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function showCategoryWithProducts($category)
    {
        try {
            $category = Category::find($category)
                        ->load(['products']);
            if(!$category){
                return $this->error([],'Category Not Found');
            }

            $response = [
                'category' => New CategoryResource($category),
                'products' => new ProductCollection($category->products()->paginate(env('PRODUCT_PAGINATION',10))),
            ]; 

            return $this->success($response);
        }
        catch (\Exception $ex) {
            return $this->error([], $ex->getMessage(),$ex->getCode());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        //
    }
}
