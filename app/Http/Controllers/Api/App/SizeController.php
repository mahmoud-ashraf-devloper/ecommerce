<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\SizeCollection;
use App\Models\Product;
use App\Models\Size;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SizeController extends Controller
{
use ApiResponse;

public function addNewSize(Request $request)
{
    try {
        $validator = Validator::make($request->only(['size','parent_id']),[
            'size' => 'required|unique:sizes,size',
            'parent_id' => 'exists:sizes,id'
        ]);
        if($validator->fails()){
            return $this->error($validator->errors(),400,'validation error');
        }

        $createdSize = Size::create($validator->validated());
        if($createdSize){
            return $this->success($createdSize, 'Size created successfully');
        }
        return $this->error([],500);

    } catch (\Exception $e) {
        return $this->error($e->getMessage(),500);
    }
}

public function editSize(Request $request,$sizeId)
{
    try {
        $size = Size::find($sizeId);
        if(! $size){
            return $this->error([],404,'The size that you\'re trying to edit is not exists');
        }
        $validator = Validator::make($request->only(['size','parent_id']),[
            'size' => 'required|unique:sizes,size',
            'parent_id' => 'exists:sizes,id'
        ]);
        if($validator->fails()){
            return $this->error($validator->errors(),400,'validation error');
        }

        $updated = $size->update($validator->validated());
        if($updated){
            return $this->success([], 'Size Updated successfully');
        }
        return $this->error([],500);

    } catch (\Exception $e) {
        return $this->error($e->getMessage(),500);
    }
}

public function getAllSizes()
{
    try {
        return $this->success(new SizeCollection(Size::with('children')->where('parent_id','=', null)->get()));
    } catch (\Exception $e) {
        return $this->error($e->getMessage(), 500);
    }
}

/**
 * Add Size to a Product
 * 
 * @return JsonResponse
 **/

 public function avilableSizesForProduct($productId)
 {
     try {
         $product = Product::find($productId);
         if(!$product){
             return $this->error([],404, 'Product Does not Exists');
         }
         return $this->success(new SizeCollection($product->sizes));
     } catch (\Exception $e) {
        return $this->error($e->getMessage());
     }
 }
 public function addSizeToProduct($productId, $sizeId, Request $request)
 {
    try{
        $product = Product::find($productId);
        if(!$product){
            return $this->error('This Product Does not exists',404);
        }

        $size = Size::find($sizeId);
        if(! $size){
            return $this->error('This Size Does not exists',404);
        }
        if(in_array($sizeId, $product->sizes->pluck('id')->toArray())){
            return $this->error('This Size Is Already Attached to the product',400);
        }
        $product->sizes()->attach($sizeId);
        
        return $this->success([],'Size added successfully');
    }catch(\Exception $e){
        return $this->error(['code'=> $e->getCode()],500, $e->getMessage());
    }

}

public function deleteSizeFromProduct($productId, $sizeId)
{
    try {    
        // if the product does not exists
        $product = Product::find($productId);
        if(!$product){
            return $this->error([],404,'Product Does not Exists');
        }
        // if the size does not exists on the product
        $size = $product->sizes->where('id', $sizeId)->first();
        if(!$size){
            return $this->error([],404, 'Size does not exists');
        }
        // delete the size and return success
        if($size->delete()){
            return $this->success([],'size deleted successfully');
        }
        return $this->error([]);
    } catch (\Exception $e) {
        return $this->error($e->getMessage(),500);
    }
}
}
