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
     * Display the Only Categories.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show($category)
    {
        try {
            $category = Category::find($category);
            if(!$category){
                return $this->error([],404,'Category Not Found');
            }

            $response = [
                'category' => New CategoryResource($category),
            ]; 

            return $this->success($response);
        }
        catch (\Exception $ex) {
            return $this->error([], $ex->getMessage(),$ex->getCode());
        }
    }

    /**
     * Display a specified category with it's products.
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
    public function edit(Request $request,$categoryId)
    {
        try {
            $category = Category::find($categoryId);
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
            $category = Category::find($categoryId);
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
