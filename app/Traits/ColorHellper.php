
<?php
namespace App\Traits;

use App\Models\Color;

trait ColorHelper{
    private function colorExists($colorId)
    {
        $color = Color::find($colorId);
        if(!$color){
            return false;
        }
        return $color;
    }

}