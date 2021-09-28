<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use App\Models\Product;
use App\Traits\ApiResponse;
use App\Traits\ColorHelper;
use App\Traits\ProductHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class ColorController extends Controller
{   
    use ApiResponse, ColorHelper, ProductHelper;

    /**
     * get all colors
     * 
     * @param null
     * 
     * @return JsonResponse
     **/ 
    public function index()
    {
        return $this->success(
            ColorResource::collection(Color::where('parent_id', null)->with(['children'])->get()) ?? null
        );
    }

    /**
     * get all colors for specific product
     * 
     * @param $productId
     * 
     * @return JsonResponse
     **/ 
    public function getAllAvilableColorsForProduct($productId)
    {
        $product = Product::find($productId);
        if(!$product){
            return $this->error([],404, 'Product Does not Exist');
        }
        return $this->success(
            ColorResource::collection($product->colors)
        );
    }
    
    /**
     * Add new color
     * 
     * @param Request $request
     * 
     * @return JsonResponse
     **/ 
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->only(['color','parent_id']),[
                'color' => ['required','unique:colors,color','string'],
                'parent_id' => ['integer','exists:colors,id'],
            ]);
    
            if($validator->fails()){
                return $this->error($validator->errors(),400,'validation error');
            }
    
            $created = Color::create($validator->validated());
    
            if($created){
                return $this->success(ColorResource::make($created), 'Color Created successfully');
            }
        } catch (\Throwable $th) {
            return $this->error(['code' => $th->getCode()], 500, $th->getMessage());
        }
    }
    /**
     * Soft delete color
     * 
     * @param Request $request
     * 
     * @return JsonResponse
     **/ 
    public function addToTrash($colorId)
    {
        try{
            $color = $this->colorExists($colorId);
            if(!$color){
                return $this->error([],404, 'Color Does not exists');
            }

            DB::transaction(function() use($color){
                $color->children->map(function($q){
                    $q->delete();
                });
                $color->delete();
            });

            return $this->success(ColorResource::make($color),'The Color Moved To trash successfully');

        } catch (\Exception $e){
            return $this->error(['code' => $e->getCode()], 500,$e->getMessage());
        }
    }

    /**
     * get deleted color
     * 
     * @param null
     * 
     * @return JsonResponse
     **/ 
    public function getTrashedColors()
    {
        return $this->success(
            ColorResource::collection(
                Color::onlyTrashed()->get()
            )
        );
    }


    /**
     * Force delete color
     * 
     * @param $colorId
     * 
     * @return JsonResponse
     **/ 
    public function forceDelete($colorId)
    {
        try{
            $color = $this->colorExists($colorId);
            if(!$color){
                return $this->error([],404, 'Color Does not exists');
            }

            $color->forceDelete();

            return $this->success(ColorResource::make($color),'The Color Deleted successfully');

        } catch (\Exception $e){
            return $this->error(['code' => $e->getCode()], 500,$e->getMessage());
        }
    }

    
    /**
     * Add new color to aproduct
     * 
     * @param $productId
     * 
     * @return JsonResponse
     **/ 
    public function addColorsToProduct($productId, $colorId) 
    {
        try{

            $product = $this->productExists($productId);
            if(!$product){
                return $this->error([],404, 'Product Does not Exist');
            }

            $color = $this->colorExists($colorId);
            if(!$color){
                return $this->error([],404, 'Color Does not exists');
            }

            if($this->productHasColor($product ,$colorId)){
                return $this->error([],404, 'Color is Already Exists On the product');
            }
    
            $product->colors()->attach($colorId);

            return $this->success(ColorResource::make($color),'The Color Added successfully to the product');
        } catch (\Exception $e){
            return $this->error(['code' => $e->getCode()], 500,$e->getMessage());
        }
    }

    /**
     * Add new color to aproduct
     * 
     * @param $productId
     * 
     * @return JsonResponse
     **/ 
    public function removeColorFromProduct($productId, $colorId)
    {
        try{
         
            $product = $this->productExists($productId);
            if(!$product){
                return $this->error([],404, 'Product Does not Exist');
            }

            $color = $this->colorExists($colorId);
            if(!$color){
                return $this->error([],404, 'Color Does not exists');
            }

            if(! $this->productHasColor($product ,$colorId)){
                return $this->error([],404, 'Color is Already does not Exists On the product');
            }

            $product->colors()->detach($colorId);
            
            return $this->success([],'color removed successfully');

        } catch (\Exception $e){
            return $this->error(['code' => $e->getCode()], 500,$e->getMessage());
        }
    }

    public function restoreTrashedColor($colorId)
    {
        try{
            $color = Color::withTrashed()->where('id',$colorId)->first();
            if(!$color){
                return $this->error([],404, 'Color Does not exists');
            }
            if(! $color->trashed()){
                return $this->error([],400, 'Color Is not trashed');
            }
            
            $color->restore();
            return $this->success([],'color restored successfully');

        } catch (\Exception $e){
            return $this->error(['code' => $e->getCode()], 500,$e->getMessage());
        }
    }



}
