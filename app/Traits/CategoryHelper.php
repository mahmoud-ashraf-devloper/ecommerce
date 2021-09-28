<?php
namespace App\Traits;

use App\Models\Category;

trait CategoryHelper{

    private function categoryExists($categoryId)
    {
        $category = Category::find($categoryId);
        if(!$category){
            return false;
        }
        return $category;
    }
}