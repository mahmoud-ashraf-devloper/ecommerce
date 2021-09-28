<?php
namespace App\Traits;

use App\Models\Size;

trait SizeHelper{
    private function sizeExists($sizeId)
    {
        $size = Size::find($sizeId);
        if(!$size){
            return false;
        }
        return $size;
    }
}