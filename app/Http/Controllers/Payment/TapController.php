<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use App\Http\Controllers\OrderController;
use App\Models\Cart;
use App\Models\Product;
use App\Models\LogPayment;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Str;


class TapController extends Controller
{

     private $orderID;

    public function handelCheckout()
    {
        // Minumum order amount chec
        $cartOrders = Cart::where('user_id', auth()->user()->id)->get();

        $subtotal = 0;
        foreach ($cartOrders as $key => $cartItem) {
            $product = Product::find($cartItem['product_id']);
            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
        }

        (new OrderController)->store(request());

        session()->put('payment_type', 'cart_payment');


        $data['combined_order_id'] = session()->get('combined_order_id');

         $this->orderID =  $data['combined_order_id'] ;
        session()->put('payment_data', $data);
    }

    /*================================================================= */
    public function pay(Request $request)
    {
        $this->handelCheckout();

        $order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));

      
        $amount = $order->grand_total;
       $tapPublicKey = 'pk_live_XDPuMnW5fxL86FwJYBomzIhk'; //env('TAP_PUBLIC_KEY');
        //$tapPublicKey = 'pk_test_AygM9tQ6UBXNkRYu857GfKxq' env('TAP_PUBLIC_KEY');
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
            "orderId" =>$request->session()->get('combined_order_id'),
            "successRedirect" => route("tap.callBack"),
            "webhook" => route("tap.webHook")
        ];

        return response()->json($responseArray, 200);
    }

    /*========================================================================================== */


    public function callBack()
    {


       $paymentStatus = LogPayment::where('order_id',session()->get('combined_order_id'))->first();

       //dd($paymentStatus);
        try {
            if ($paymentStatus->payment_status == "CAPTURED") {

                $payment  = ["status" => "Success"];

                 DB::table('orders')
                ->where('combined_order_id', $paymentStatus->order_id)
                ->update(['payment_type' => $paymentStatus->payment_method]);


                return (new CheckoutController)->checkout_done(session()->get('combined_order_id'), json_encode($payment));
            } else {

                flash(translate('Payment Failed Please Try Again'))->error();

                return redirect()->route('home');
            }
        } catch (\Exception $e) {
            flash(translate('Payment failed contact with  Support'))->error();
            return redirect()->route('home');
        }
    }
    /*========================================================================================== */
    public function webhook(Request $request)
    {

        $webhookDataJson = file_get_contents('php://input');

        $webhookData = json_decode($webhookDataJson, true);

         $orderr=   LogPayment::create([
            'order_id'              => $webhookData["reference"]["order"],
            'payment_status'        => $webhookData["status"],
            'payment_method'        => $webhookData["source"]["payment_method"],
            'payment_details'       => $webhookDataJson,

        ]);



    }
    /*========================================================================================== */
}
