<?php

namespace App\Traits;


/**
 * For image upload
 */
trait Upload
{
    public function uploadMultiImage($images, $path)
    {
        try {            
            $imagesUrls = [];
            foreach($images as $image){
                $name = date('YmdHis') . rand(1, 999999) . '.' . $image->getClientOriginalExtension();
                $imageUrl = $image->storeAs($path, $name, 'public');
                $imagesUrls[] = $imageUrl;
            }
            return (count($images) === count($imagesUrls)) ? $imagesUrls : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function uploadSingleImage($image, $path)
    {
        try {            
            $name = date('YmdHis') . rand(1, 999999) . '.' . $image->getClientOriginalExtension();;
            $imageUrl = $image->storeAs($path, $name, 'public');
            return $imageUrl;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function removeImages($path)
    {
        if(is_array($path)){
            foreach($path as $singlePath){
                unlink(storage_path('/app/public/'.$singlePath));
            }
            return true;
        }
        unlink(storage_path('/app/public/' .$path));
        return true;
    }
}

