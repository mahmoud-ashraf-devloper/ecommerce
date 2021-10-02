<?php

namespace App\Http\Controllers\Api\App\PaymentsGateWays;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Transaction;
use App\Models\Validations\OrderValidation;
use App\Traits\ApiResponse;
use App\Traits\ProductHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayWithPayPalController extends Controller
{
    use ProductHelper, ApiResponse;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->paypalClient = new PayPalClient;
    }

    public function create(Request $request)
    {
        $data = [];

        // order validation
        $validator = (new OrderValidation)->validator($request->all());
        if ($validator->fails()) {
            return $this->error($validator->errors(), 400, 'validation error');
        }

        $data['items'] = $this->getItems($validator->validated()['cart']);

        $orderInfo = $validator->validated();
        $data['total_price'] = $data['items']['total_price'];


        $this->paypalClient->setApiCredentials(config('paypal'));

        $token = $this->paypalClient->getAccessToken();

        $this->paypalClient->setAccessToken($token);
        
        $order = $this->paypalClient->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $data['total_price'],
                    ],
                ]
            ],
        ]);


        // do a check if creation is created
        if (isset($order['type']) && $order['type'] === 'error') {
            return $this->error(['message' => $order['message']], 500, 'Something went wrong with payapal');
        }

        $orderId = $order['id'];

        DB::transaction(function () use ($orderInfo, $orderId) {
            foreach ($orderInfo['cart'] as $key => $order) {
                $cartId = $order;
                $order = Order::create([
                    'user_id' => auth('user-api')->user()->id,
                    'cart_id' => $cartId,
                    'vendor_order_id' => $orderId,
                    'billing_email' => $orderInfo['billing_email'],
                    'billing_name' => $orderInfo['billing_name'],
                    'billing_address' => $orderInfo['billing_address'],
                    'billing_city' => $orderInfo['billing_city'],
                    'billing_province' => $orderInfo['billing_province'],
                    'billing_postalcode' => $orderInfo['billing_postalcode'],
                    'billing_phone' => $orderInfo['billing_phone'],
                    'billing_name_on_card' => $orderInfo['billing_name_on_card'],
                    'billing_discount' => $orderInfo['billing_discount'],
                    'billing_discount_code' => $orderInfo['billing_discount_code'],
                    'billing_subtotal' => $orderInfo['billing_subtotal'],
                    'billing_total' => $orderInfo['billing_total'],
                    'payment_gateway' => $orderInfo['payment_gateway'] ?? 'paypal',
                    'status' => Transaction::PENDING,
                ]);

                OrderProduct::create([
                    'order_id' => $order->id,
                    'cart_id'  => $cartId,
                ]);
            }
        });

        return $this->success($order, 'order created please navigate to paypal to provide your credentials to complete the order');
    }


    public function capture(Request $request)
    {
        $validator = Validator::make($request->only(['orderId', 'user_id']),[
            'orderId' => ['string'],
        ]);
        if($validator->fails()){
            return $this->error(['error' => $validator->errors()], 400, 'This order does not exists');
        }

        $orderId = $validator->validated()['orderId'];
        $this->paypalClient->setApiCredentials(config('paypal'));
        $token = $this->paypalClient->getAccessToken();
        $this->paypalClient->setAccessToken($token);
        $result = $this->paypalClient->capturePaymentOrder($orderId);

        if(isset($result['type']) && $result['type'] === 'error'){
            return $this->error(['error' => 'order did not aproved yet'], 400, 'please redirect the user to aproval link');
        }

        try {
            DB::beginTransaction();
            if ($result['status'] === "COMPLETED") {
                $transaction = new Transaction;
                $transaction->vendor_payment_id = $orderId;
                $transaction->payment_gateway_id  = $validator->validated()['payment_gateway_id'];
                $transaction->user_id  = auth('user-api')->user()->id;
                $transaction->status   = Transaction::COMPLETED;
                $transaction->save();
                $orders = Order::where('vendor_order_id', $orderId)->get();

                $orders->map(function($order) use($transaction){
                    $order->transaction_id = $transaction->id;
                    $order->status = Transaction::COMPLETED;
                    $order->save();
                });
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return $this->success($result);
    }

    private function getItems(array $cartIds)
    {
        $items = [];

        $items['total_price'] = 0;

        foreach ($cartIds as $key => $id) {
            $cart = Cart::find($id);
            $product = $cart->product()->select(['id', 'title', 'description', 'price', 'discount'])->get()->toArray();
            $size = $cart->size()->select(['id', 'size'])->get()->toArray();
            $color = $cart->color()->select(['id', 'color'])->get()->toArray();
            $count = $cart->count;
            $items[] = [
                'cart_id' => $id,
                'user_id' => Auth('user-api')->id(),
                'product' => $product,
                'size'    => $size,
                'color'    => $color,
                'count'    => $count,
            ];

            $items['total_price'] += ($product[0]['price'] * $count);
        }
        $items['total_price'] = number_format($items['total_price'], 2, '.', '');
        return  $items;
    }
}
