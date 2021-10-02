<?php

namespace App\Models\Validations;

use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderValidation 
{
    public function validator(array $atterebutes, $update = false)
    {
        return Validator::make($atterebutes,[
            'cart'       => [Rule::when($update,'sometimes'),'required','exists:carts,id'],
            'billing_email' => [Rule::when($update,'sometimes'),'required','exists:users,email'],
            'billing_name'  => [Rule::when($update,'sometimes'),'required','string'],
            'billing_address'   => [Rule::when($update,'sometimes'),'required', 'string'],
            'billing_city'      => [Rule::when($update,'sometimes'),'required', 'string'],
            'billing_province'  => [Rule::when($update,'sometimes'),'required'],
            'billing_postalcode'=> [Rule::when($update,'sometimes'),'required', 'integer'],
            'billing_phone'     => [Rule::when($update,'sometimes'),'required'],
            'billing_name_on_card'   => [Rule::when($update,'sometimes'),'required', 'string'],
            'billing_discount'       => [''],
            'billing_discount_code'  => ['required_with:billing_discount'],
            'billing_subtotal' => [Rule::when($update,'sometimes'),'required'],
            'billing_total'    => [Rule::when($update,'sometimes'),'required'],
            'payment_gateway'  => [Rule::when($update,'sometimes'),'required'],
        ]);
    }




}
