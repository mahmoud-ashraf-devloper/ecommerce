<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Validations\OrderValidation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    use ApiResponse;
    

    public function instantiatingNewOrder(Request $request)
    {
        $validator = (new OrderValidation)->validator($request->all());
        
        if($validator->fails()){
            return $this->error($validator->errors(), 400, 'validation error');
        }

        dd(Order::ORDER_PAYED);
        $order = Order::create([
            'user_id' => auth()->user() ? auth()->user()->id : null,
            'billing_email' => $request->email,
            'billing_name' => $request->name,
            'billing_address' => $request->address,
            'billing_city' => $request->city,
            'billing_province' => $request->province,
            'billing_postalcode' => $request->postalcode,
            'billing_phone' => $request->phone,
            'billing_name_on_card' => $request->name_on_card,
            'billing_discount' => getNumbers()->get('discount'),
            'billing_discount_code' => getNumbers()->get('code'),
            'billing_subtotal' => getNumbers()->get('newSubtotal'),
            'billing_tax' => getNumbers()->get('newTax'),
            'billing_total' => getNumbers()->get('newTotal'),
            'error' => $error,
        ]);
    }


}
