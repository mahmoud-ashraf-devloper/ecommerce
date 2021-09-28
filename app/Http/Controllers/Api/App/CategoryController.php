<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\{
    CategoryResource,
    CategoryCollection,
    ProductCollection
};
use App\Models\Category;
use App\Traits\ApiResponse;
use App\Traits\CategoryHelper;
use App\Traits\ProductHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{
    use ApiResponse, CategoryHelper, ProductHelper;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $categories = new CategoryCollection(Category::with(['children'])->paginate(env('CATEGORY_PAGINATION',10)));
            return $this->success($categories);

        } catch (\Exception $ex) {
            return $this->error($ex->getMessage(), 404,'Something went Wrong');
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
        
        $validator = Validator::make($request->only(['category','parent_id']),[
            'category' => 'required|unique:categories|string|max:255',
            'parent_id' => 'exists:categories,id'
        ]);

        if($validator->fails()){
            return $this->error($validator->errors(), 400,'Validation Error');
        }
        try {
            $createdCategory = Category::create($validator->validated());
            if($createdCategory){
                return $this->success(new CategoryResource($createdCategory), 'Category Created Successfully');
            }else{
                return $this->error([], 500,'Server Error');
            }
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage(),500 ,'Something went Wrong');
        }
    }

    /**
     * add category to product.
     *
     * @param  $categoryId, $productId
     * @return \Illuminate\Http\Response
     */
    public function addCategoryToProduct($categoryId, $productId)
    {  
        try {
            $category = $this->categoryExists($categoryId);
            if(!$category){
                return $this->error([], 404,'Category Does not Exists');
            }

            $product = $this->productExists($productId);
            if(! $product){
                return $this->error([], 404,'Product Does not Exists');
            }

            if($this->productHasCategory($product, $categoryId)){
                return $this->error([], 400,'The Product Is already has this category');
            }

            $product->categories()->attach($categoryId);
            return $this->success([],'Category added to the product successfully');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage(),500 ,'Something went Wrong');
        }
    }

    /**
     * remove category from product.
     *
     * @param  $categoryId, $productId
     * @return \Illuminate\Http\Response
     */
    public function removeCategoryToProduct($categoryId, $productId)
    {  
        try {
            $category = $this->categoryExists($categoryId);
            if(!$category){
                return $this->error([], 404,'Category Does not Exists');
            }

            $product = $this->productExists($productId);
            if(! $product){
                return $this->error([], 404,'Product Does not Exists');
            }

            if(!$this->productHasCategory($product, $categoryId)){
                return $this->error([], 400,'The Product Is already does not has category');
            }

            $product->categories()->detach($categoryId);
            return $this->success([],'Category removed from the product successfully');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage(),500 ,'Something went Wrong');
        }
    }


    /**
     * Display a specified category with it's products.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function showCategoryWithProducts($categoryId)
    {
        try {
            $category = $this->categoryExists($categoryId);
            if(!$category){
                return $this->error([],404,'Category Not Found');
            }
            $category = $category->load(['products']);
            $response = [
                'category' => New CategoryResource($category),
                'products' => new ProductCollection($category->products()->paginate(env('PRODUCT_PAGINATION',10))),
            ]; 

            return $this->success($response);
        }
        catch (\Exception $ex) {
            return $this->error([], 500 ,$ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$categoryId)
    {
        try {
            $category = $this->categoryExists($categoryId);
            if(!$category){
                return $this->error('Category Does not exists',404,'category not found');
            }
            if(empty($request->only(['category', 'parent_id']))){
                return $this->error('There is nothing to update',400,'Validation error');
            }
            $validator = Validator::make($request->only(['category', 'parent_id']),[
                'category' => 'unique:categories|string|max:255',
                'parent_id' => 'exists:categories,id'
            ]);
            if($validator->fails()){
                return $this->error($validator->errors(),400,'validation error');
            }

            $updated = $category->update($validator->validated());
            if($updated){
                return $this->success(new CategoryResource(Category::find($categoryId)->load(['parent'])),'Category Updated successfully');
            }
            return $this->error([],500,'sorry something went wrong please try again later');

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($categoryId)
    {
        try {
            $category = $this->categoryExists($categoryId);
            if(!$category){
                return $this->error('Category Does not exists',404,'category not found');
            }
            if($category->delete()){
                return $this->success([],'Category Deleted Successfully');
            }
            return $this->error([],500,'sorry something went wrong please try again later');

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ $categoryId
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($categoryId)
    {
        try {
            $category = Category::withTrashed()->where('id',$categoryId)->first();
            if(!$category){
                return $this->error('Category Does not exists',404,'category not found');
            }
            if($category->forceDelete()){
                return $this->success([],'Category Deleted Successfully');
            }
            return $this->error([],500,'sorry something went wrong please try again later');

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
