<?php
namespace App\Traits;

use App\Models\Cart;
use App\Models\Product;

trait ProductHelper{
    private function productExists($productId)
    {
        $product = Product::find($productId);
        if(!$product){
            return false;
        }
        return $product;
    }

    private function productHasCategory($product, $categoryId)
    {
        if(in_array($categoryId, $product->categories->pluck('id')->toArray())){
            return true;
        }
        return false;
    }

    private function productHasColor($product, $colorId)
    {
        if(in_array($colorId, $product->colors->pluck('id')->toArray())){
            return true;
        }
        return false;
    }

    private function productHasSize($product, $sizeId)
    {
        if(in_array($sizeId, $product->sizes->pluck('id')->toArray())){
            return true;
        }
        return false;
    }

    private function productExistsInTheCart($productId, $userId)
    {
        $exists =  Cart::where(['product_id' => $productId, 'user_id' => $userId])->first();
        if(! $exists){
            return false;
        }
        return $exists;
    }
}