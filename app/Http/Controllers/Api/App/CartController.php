<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Traits\ApiResponse;
use App\Traits\ProductHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use ApiResponse, ProductHelper;

    public function index()
    {
        try {
            $user = Auth('user-api')->user();
            $cartItems = $user
                    ->cartProducts()
                    ->with(['product' => fn($q) => $q->with(['categories', 'productImages']),'color','size'])
                    ->latest()
                    ->get();
            $totalPrice = 0;
            foreach($cartItems as $key => $cartItem){
                $totalPrice += ($cartItem->product->price * $cartItem->count);
            }
    
            $response = [
                'userInfo' => $user,
                'products' => CartResource::collection($cartItems),
                'totalPrice' => number_format($totalPrice,2),
                'countOfItems' => count($cartItems->toArray()),
            ];
    
            return $this->success($response);
        } catch (\Throwable $th) {
            return $this->error(['codeError' => $th->getCode()],500, $th->getMessage());
        }
    }

    public function addToCart(Request $request,$productId)
    {
        try{
            $userId = Auth('user-api')->id();

            $validator = Validator::make($request->all(),[
                'color_id' => ['required','exists:colors,id'],
                'size_id' => ['required','exists:sizes,id'],
                'count' => ['integer'],
            ]);

            if($validator->fails()){
                return $this->error($validator->errors(),400, 'validation error');
            }

            // validate that the product exists
            $product = $this->productExists($productId);
            if(!$product){
                return $this->error([], 400, 'This product does not exists');
            }
            // validate that the product have the size
            if(! $this->productHasSize($product, $validator->validated()['size_id'])){
                return $this->error([], 400, 'the product does not have this size');
            }
            // validate that the product have the color
            if(! $this->productHasColor($product, $validator->validated()['color_id'])){
                return $this->error([], 400, 'the product does not have this color');
            }

            // if product exists on the cart
            if($this->productExistsInTheCart($productId, $userId)){
                return $this->error([], 400, 'the product is already in the cart');
            }
            // add that product to the user cart
            $data = array_merge($validator->validated() ,['user_id' => $userId, 'product_id' => $productId]);
            
            
            return $this->success(
                CartResource::make(Cart::create($data))
            );
        } catch (\Throwable $th) {
            return $this->error(['codeError' => $th->getCode()],500, $th->getMessage());
        }
    }

    public function removeFromTheCart($productId)
    {
        try {
            $userId = Auth('user-api')->id();
            // if product exists on the cart
            $cartItem = $this->productExistsInTheCart($productId, $userId);
            if(!$cartItem){
                return $this->error([], 400, 'the product is already does not exists in the cart');
            }

            $cartItem->delete();

            return $this->success([],'product deleted successfully');

        } catch (\Throwable $th) {
            return $this->error(['codeError' => $th->getCode()],500, $th->getMessage());
        }
    }
}
