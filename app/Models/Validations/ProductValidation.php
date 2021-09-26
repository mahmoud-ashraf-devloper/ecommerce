<?php

namespace App\Models\Validations;

use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductValidation 
{
    public function validator(array $atterebutes, $update = false)
    {
        return Validator::make($atterebutes,[
            'title'=> [Rule::when($update,'sometimes'),'required','min:6'],
            'main_image'=> [Rule::when($update,'sometimes'),'required','image','mimes:jpeg,png'], 
            'description'=> [Rule::when($update,'sometimes'),'required','min:10'],
            'more_images' => [Rule::when($update,'sometimes'),'required'],
            'more_images.*' => ['image','mimes:jpeg,png'],
        ]);
    }

    public function singleImageValidator(array $atterebutes)
    {
        return Validator::make($atterebutes,[
            'image'=> ['required','image','mimes:jpeg,png'], 
        ]);
    }


}
