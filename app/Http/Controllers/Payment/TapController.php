<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use App\Http\Controllers\OrderController;
use App\Models\Cart;
use App\Models\Product;

class TapController extends Controller
{
    public function handelCheckout()
    {
        // Minumum order amount check
        $cartOrders = Cart::where('user_id', auth()->user()->id)->get();

        $subtotal = 0;
        foreach ($cartOrders as $key => $cartItem) {
            $product = Product::find($cartItem['product_id']);
            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
        }

        (new OrderController)->store(request());

        session()->put('payment_type', 'cart_payment');

        $data['combined_order_id'] = session()->get('combined_order_id');
        session()->put('payment_data', $data);
    }

    /*================================================================= */
    public function pay(Request $request)
    {
        $this->handelCheckout();

        $order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
        $amount = $order->grand_total;
        $tapPublicKey = 'pk_test_AygM9tQ6UBXNkRYu857GfKxq'; //env('TAP_PUBLIC_KEY');
        $shippingDataObject = json_decode($order->shipping_address);

        $phone = $shippingDataObject->phone;
        $email = $shippingDataObject->email;

        $responseArray = [
            "publicKey" => $tapPublicKey,
            "currency" => "KWD",
            "amount" => $amount,
            "phoneCode" => substr($phone, 1, 3),
            "phoneNumber" => substr($phone, 3),
            "email" => $email,
            "successRedirect" => route("tap.callBack"),
            "webhook" => route("tap.webHook")
        ];

        return response()->json($responseArray, 200);
    }
    /*========================================================================================== */
    public function callBack()
    {
        try {
            if (session()->has("tapStatus") && session()->get("tapStatus") == "CAPTURED") {
                session()->flash("tapStatus");
                $payment = ["status" => "Success"];
                return (new CheckoutController)->checkout_done(session()->get('combined_order_id'), json_encode($payment));
            } else {
                flash(translate('Payment failed'))->error();
                return redirect()->route('home');
            }
        } catch (\Exception $e) {
            flash(translate('Payment failed'))->error();
            return redirect()->route('home');
        }
    }
    /*========================================================================================== */
    public function webhook()
    {
        $webhookDataJson = file_get_contents('php://input');
        $webhookData = json_decode($webhookDataJson);
        session()->put("tapStatus", $webhookData->status);
    }
    /*========================================================================================== */
}
