<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Address;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\OrderDetail;
use App\Models\CouponUsage;
use App\Models\Coupon;
use App\Models\User;
use App\Models\CombinedOrder;
use App\Models\SmsTemplate;
use Auth;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\Models\City;
use App\Utility\NotificationUtility;
use App\Utility\SmsUtility;
use DB;
use Illuminate\Support\Facades\Route;

class OrderController extends Controller
{

    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_orders'])->only('all_orders');
        $this->middleware(['permission:view_inhouse_orders'])->only('all_orders');
        $this->middleware(['permission:view_seller_orders'])->only('all_orders');
        $this->middleware(['permission:view_pickup_point_orders'])->only('all_orders');
        $this->middleware(['permission:view_order_details'])->only('show');
        $this->middleware(['permission:delete_order'])->only('destroy');
    }

    // All Orders
    public function all_orders(Request $request)
    {

        $date = $request->date;
        $sort_search = null;
        $phone_search = null;
        $delivery_status = null;
        $payment_status = '';

        $orders = Order::orderBy('id', 'desc');
        $admin_user_id = User::where('user_type', 'admin')->first()->id;
        if (Route::currentRouteName() == 'inhouse_orders.index') {
            $orders = $orders->where('orders.seller_id', '=', $admin_user_id);
        }
        if (Route::currentRouteName() == 'seller_orders.index') {
            $orders = $orders->where('orders.seller_id', '!=', $admin_user_id);
        }
        if (Route::currentRouteName() == 'pick_up_point.index') {
            $orders->where('shipping_type', 'pickup_point')->orderBy('code', 'desc');
            if (Auth::user()->user_type == 'staff' && Auth::user()->staff->pick_up_point != null) {
                $orders->where('shipping_type', 'pickup_point')
                    ->where('pickup_point_id', Auth::user()->staff->pick_up_point->id);
            }
        }
        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }

        if ($request->search_by_phone) {
            $phone_search = $request->search_by_phone;
            $orders =  $orders->join('users', 'users.id', '=', 'orders.user_id')->select('orders.*', 'users.phone')
                ->where('phone', 'like', '%' . $phone_search . '%');
        }

        if ($request->payment_status != null) {
            $orders = $orders->where('payment_status', $request->payment_status);
            $payment_status = $request->payment_status;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($date != null) {
            $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])) . '  00:00:00')
                ->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])) . '  23:59:59');
        }
        $orders = $orders->paginate(15);
        return view('backend.sales.index', compact('orders', 'sort_search', 'phone_search', 'payment_status', 'delivery_status', 'date'));
    }

    public function show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = User::where('city', $order_shipping_address->city)
            ->where('user_type', 'delivery_boy')
            ->get();

        $order->viewed = 1;
        $order->save();
        return view('backend.sales.show', compact('order', 'delivery_boys'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = User::where('user_type', 'customer')->orderBy('created_at', 'desc')->get();
        // dd(95261);

        return view('backend.sales.create', compact('customers'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $carts = Cart::where('user_id', Auth::user()->id)->get();

        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $address = Address::where('id', $carts[0]['address_id'])->first();

        $shippingAddress = [];
        if ($address != null) {
            $shippingAddress['name']        = Auth::user()->name;
            $shippingAddress['email']       = Auth::user()->email;
            $shippingAddress['address']     = $address->street . ' , ' . $address->block . ' , ' . $address->house . ' , ' . $address->avenue;
            $shippingAddress['country']     = $address->country->name;
            $shippingAddress['state']       = $address->state->name;
            $shippingAddress['city']        = $address->city->name;
            $shippingAddress['postal_code'] = $address->postal_code;
            $shippingAddress['house']       = $address->house;
            $shippingAddress['block']       = $address->block;
            $shippingAddress['street']       = $address->street;
            $shippingAddress['avenue']       = $address->avenue;
            $shippingAddress['phone']       = $address->phone;
            $shippingAddress['floor']       = $address->floor;
            $shippingAddress['flat']       = $address->flat;

            if ($address->latitude || $address->longitude) {
                $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
            }
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = Auth::user()->id;
        $combined_order->shipping_address = json_encode($shippingAddress);
        $combined_order->save();

        $seller_products = array();
        foreach ($carts as $cartItem) {
            $product_ids = array();
            $product = Product::find($cartItem['product_id']);
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem);
            $seller_products[$product->user_id] = $product_ids;
        }

        foreach ($seller_products as $seller_product) {
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id = Auth::user()->id;
            $order->shipping_address = $combined_order->shipping_address;

            $order->additional_info = $request->additional_info;

            //======== Closed By Kiron ==========
            // $order->shipping_type = $carts[0]['shipping_type'];
            // if ($carts[0]['shipping_type'] == 'pickup_point') {
            //     $order->pickup_point_id = $cartItem['pickup_point'];
            // }
            // if ($carts[0]['shipping_type'] == 'carrier') {
            //     $order->carrier_id = $cartItem['carrier_id'];
            // }

            $order->payment_type = $request->payment_option;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            //$order->code = date('Ymd-His') . rand(10, 99);
            $order->code = orderMaxCode();
            $order->date = strtotime('now');
            $order->save();

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;

            //Order Details Storing
            foreach ($seller_product as $cartItem) {
                $product = Product::find($cartItem['product_id']);

                $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                $tax +=  cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                $coupon_discount += $cartItem['discount'];

                $product_variation = $cartItem['variation'];

                $product_stock = $product->stocks->where('variant', $product_variation)->first();
                if ($product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                    flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                    $order->delete();
                    return redirect()->route('cart')->send();
                } elseif ($product->digital != 1) {
                    $product_stock->qty -= $cartItem['quantity'];
                    $product_stock->save();
                }

                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = $product_variation;
                $order_detail->price = cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                $order_detail->tax = cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];
                $order_detail->shipping_cost = $cartItem['shipping_cost'];

                $shipping += $order_detail->shipping_cost;
                //End of storing shipping cost

                $order_detail->quantity = $cartItem['quantity'];
                $order_detail->save();

                $product->num_of_sale += $cartItem['quantity'];
                $product->save();

                $order->seller_id = $product->user_id;
                //======== Added By Kiron ==========
                $order->shipping_type = $cartItem['shipping_type'];
                if ($cartItem['shipping_type'] == 'pickup_point') {
                    $order->pickup_point_id = $cartItem['pickup_point'];
                }
                if ($cartItem['shipping_type'] == 'carrier') {
                    $order->carrier_id = $cartItem['carrier_id'];
                }

                if ($product->added_by == 'seller' && $product->user->seller != null) {
                    $seller = $product->user->seller;
                    $seller->num_of_sale += $cartItem['quantity'];
                    $seller->save();
                }

                if (addon_is_activated('affiliate_system')) {
                    if ($order_detail->product_referral_code) {
                        $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                    }
                }
            }

            $order->grand_total = $subtotal + $tax + $shipping;
            $order->shipping_cost =  $shipping;

            if ($seller_product[0]->coupon_code != null) {
                $order->coupon_discount = $coupon_discount;
                $order->grand_total -= $coupon_discount;

                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = Auth::user()->id;
                $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                $coupon_usage->save();
            }

            $combined_order->grand_total += $order->grand_total;

            $order->save();
        }

        $combined_order->save();

        $request->session()->put('order_id', $order->id);
        $request->session()->put('combined_order_id', $combined_order->id);
    }


    public function storeOrderFromAdmin(OrderRequest $request){


        $address = Address::where('id', $request->address_id)->first();
        $customer = User::findOrFail($request->customer_id);

        $shippingAddress = [];
        if ($address != null) {
            $shippingAddress['name']        = $customer->name;
            $shippingAddress['email']       = $customer->email;
            $shippingAddress['address']     = $address->street . ' , ' . $address->block . ' , ' . $address->house . ' , ' . $address->avenue;
            $shippingAddress['country']     = $address->country->name;
            $shippingAddress['state']       = $address->state->name;
            $shippingAddress['city']        = $address->city->name;
            $shippingAddress['postal_code'] = $address->postal_code;
            $shippingAddress['house']       = $address->house;
            $shippingAddress['block']       = $address->block;
            $shippingAddress['street']       = $address->street;
            $shippingAddress['avenue']       = $address->avenue;
            $shippingAddress['phone']       = $address->phone;
            $shippingAddress['floor']       = $address->floor;
            $shippingAddress['flat']       = $address->flat;

            if ($address->latitude || $address->longitude) {
                $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
            }
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = $request->customer_id;
        $combined_order->shipping_address = json_encode($shippingAddress);
        $combined_order->save();

        $seller_products = array();
        foreach ($request->items as $item) {
            $product_ids = array();
            $product = Product::find($item['id']);
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $item);
            $seller_products[$product->user_id] = $product_ids;
        }

        foreach ($seller_products as $seller_product) {
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id = $request->customer_id;
            $order->shipping_address = $combined_order->shipping_address;

            $order->additional_info = $request->additional_info;
            $order->added_by = $request->added_by;


            $order->payment_type = $request->payment_option;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            //$order->code = date('Ymd-His') . rand(10, 99);
            $order->code = orderMaxCode();
            $order->date = strtotime('now');
            $order->save();

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;


            // dd( $seller_product);

            //Order Details Storing
            foreach ($seller_product as $key => $item) {
                $product = Product::find($item['id']);

                $product_variation = null;
                if (isset($item['color'])) {
                    $product_variation = isset($item['attribute']) ? $item['color'] . '-' . $item['attribute'] : $item['color'];
                } elseif (isset($item['attribute'])) {
                    $product_variation = isset($item['color']) ? $item['attribute'] . '-' . $item['color'] : $item['attribute'];
                }
                $item['variation'] =  $product_variation;

                $subtotal += $item['total'];

                $tax +=  cart_product_tax($item, $product, false) * $item['quantity'];



                // $product_variation = $item['variation'];

                $product_stock = $product->stocks->where('variant', $product_variation)->first();
                if ($product->digital != 1 && $item['quantity'] > $product_stock->qty) {
                    flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                    $order->delete();
                    return redirect()->back()->send();
                } elseif ($product->digital != 1) {
                    $product_stock->qty -= $item['quantity'];
                    $product_stock->save();
                }
                $item['shipping_type'] = 'home_delivery';
                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = $product_variation;
                $order_detail->price = $item['total'];
                $order_detail->tax = cart_product_tax($item, $product, false) * $item['quantity'];
                $order_detail->shipping_type = $item['shipping_type'];
                $order_detail->product_referral_code =0;
                $order_detail->shipping_cost = $request->shipping_cost / count($request->items);

                $shipping = $request->shipping_cost;
                //End of storing shipping cost

                $order_detail->quantity = $item['quantity'];
                $order_detail->save();

                $product->num_of_sale += $item['quantity'];
                $product->save();

                $order->seller_id = $product->user_id;
                //======== Added By Kiron ==========
                $order->shipping_type = $item['shipping_type'];
                if ($item['shipping_type'] == 'pickup_point') {
                    $order->pickup_point_id = $item['pickup_point'];
                }
                if ($item['shipping_type'] == 'carrier') {
                    $order->carrier_id = $item['carrier_id'];
                }

                if ($product->added_by == 'seller' && $product->user->seller != null) {
                    $seller = $product->user->seller;
                    $seller->num_of_sale += $item['quantity'];
                    $seller->save();
                }


            }

            $order->grand_total = $subtotal + $tax + $shipping;
            $order->payment_type = 'cash_on_delivery';
            $order->payment_status = $request->payment_status;
            $order->shipping_cost =  $shipping;

            // dd( 55 );

            $combined_order->grand_total += $order->grand_total;

            $order->save();
        }

        $combined_order->save();
        return redirect('admin/all_orders');
    }

    public function addCustomer(Request $request)
    {
        $data =[
            'name' =>$request->name,
            'email' => $request->email,
            'password' => '123456',
        ];
        $request->request->add(['phone' => '+965' . $request->phone]);

        if(User::where('email', $request->email)->first() != null){
            $user = User::where('email', $request->email)->first();
            $request->request->add(['customer_id' => $user->id]);

            $address =  new AddressController();
            $address->guestStore($request);

        }else{
            $register =  new RegisterController();
            $user     = $register->create($data);
            $request->request->add(['customer_id' => $user->id]);

            $address =  new AddressController();
            $address->guestStore($request);
        }

        return redirect('admin/orders/create?customer_id='.$user->id);
        // return $address->id;

    }
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = User::where('city', $order_shipping_address->city)
            ->where('user_type', 'delivery_boy')
            ->get();

        $order->viewed = 1;
        $order->save();
        // dd($order->orderDetails);
        return view('backend.sales.edit', compact('order', 'delivery_boys'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $order_id)
    {


        $order = Order::findOrFail($order_id);
        $shipping_cost = $request->shipping_cost / count($request->items);

        $tax = $request->tax;
        // dd($request->items);
        if (isset($request->items)) {
            // $orderDetails = collect($request->items)->mapWithKeys(function ($item) use ($order_id, $shipping_cost, $tax) {
            //     $product = Product::find($item['id']);
            //     $product->num_of_sale += $item['quantity'];
            //     $product->save();

            //     $product_variation = null;
            //     if (isset($item['color'])) {
            //         $product_variation = isset($item['attribute']) ? $item['color'] . '-' . $item['attribute'] : $item['color'];
            //     } elseif (isset($item['attribute'])) {
            //         $product_variation = isset($item['color']) ? $item['attribute'] . '-' . $item['color'] : $item['attribute'];
            //     }
            //     return [$item['id'] => [
            //         'order_id' => $order_id,
            //         'product_id' => $item['id'],
            //         'seller_id' => $product->user_id,
            //         'variation' => $product_variation,
            //         'price' => $item['total'],
            //         'quantity' => $item['quantity'],
            //         'tax' => $tax,
            //         'shipping_cost' => $shipping_cost,
            //         'shipping_type' => 'home_delivery',
            //     ]];
            // })->toArray();




            OrderDetail::where('order_id', $order->id)->delete();
            $subtotal =0;
            foreach ($request->items as $item) {

                $product = Product::find($item['id']);
                $product->num_of_sale += $item['quantity'];
                $product->save();

                $product_variation = null;
                if (isset($item['color'])) {
                    $product_variation = isset($item['attribute']) ? $item['color'] . '-' . $item['attribute'] : $item['color'];
                } elseif (isset($item['attribute'])) {
                    $product_variation = isset($item['color']) ? $item['attribute'] . '-' . $item['color'] : $item['attribute'];
                }

                $orderDetails =  [
                    'order_id' => $order_id,
                    'product_id' => $item['id'],
                    'seller_id' => $product->user_id,
                    'variation' => $product_variation,
                    'price' => $item['total'],
                    'quantity' => $item['quantity'],
                    'tax' => $tax,
                    'shipping_cost' => $shipping_cost,
                    'shipping_type' => 'home_delivery',
                ];
                $subtotal += $item['total'];
                OrderDetail::create($orderDetails);
            }
            // $order->orderProductDetails()->sync($orderDetails);
        }
        // dd($subtotal);

        $order->grand_total = $subtotal+$request->shipping_cost;
        $order->shipping_cost =  $request->shipping_cost;

        $order->save();

        flash(translate('order updated '));

        return redirect()->back();
    }

    public function updateOrderShippingAddress()
    {
        $orders = Order::all();

        foreach($orders as $order){
           $cost =  City::where('name',json_decode($order->shipping_address,true)['city'])->first()->cost;
        //    dd($cost);
           Order::findOrFail($order->id)->update(['shipping_cost' => $cost]);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if ($order != null) {
            foreach ($order->orderDetails as $key => $orderDetail) {
                try {

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $orderDetail->variation)->first();
                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                } catch (\Exception $e) {
                }

                $orderDetail->delete();
            }
            $order->delete();
            flash(translate('Order has been deleted successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }
        return back();
    }

    public function bulk_order_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $this->destroy($order_id);
            }
        }

        return 1;
    }

    public function order_details(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->save();
        return view('seller.order_details_seller', compact('order'));
    }

    public function update_delivery_status(Request $request)
    {
        $this->update_delivery($request, $request->status, $request->order_id);
        return 1;
    }


    public function bulk_order_status(Request $request, $status)
    {
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $this->update_delivery($request, $status, $order_id);
            }
            flash(translate('Delivery status has been updated'))->success();
        }

        return 1;
    }


    public function update_tracking_code(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->tracking_code = $request->tracking_code;
        $order->save();

        return 1;
    }

    public function update_payment_status(Request $request)
    {
        $this->update_payment($request, $request->status, $request->order_id);

        return 1;
    }

    public function bulk_order_payment(Request $request, $status)
    {
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $this->update_payment($request, $status, $order_id);
            }
            flash(translate('Payment status has been updated'))->success();
        }

        return 1;
    }

    public function assign_delivery_boy(Request $request)
    {
        if (addon_is_activated('delivery_boy')) {

            $order = Order::findOrFail($request->order_id);
            $order->assign_delivery_boy = $request->delivery_boy;
            $order->delivery_history_date = date("Y-m-d H:i:s");
            $order->save();

            $delivery_history = \App\Models\DeliveryHistory::where('order_id', $order->id)
                ->where('delivery_status', $order->delivery_status)
                ->first();

            if (empty($delivery_history)) {
                $delivery_history = new \App\Models\DeliveryHistory;

                $delivery_history->order_id = $order->id;
                $delivery_history->delivery_status = $order->delivery_status;
                $delivery_history->payment_type = $order->payment_type;
            }
            $delivery_history->delivery_boy_id = $request->delivery_boy;

            $delivery_history->save();

            if (env('MAIL_USERNAME') != null && get_setting('delivery_boy_mail_notification') == '1') {
                $array['view'] = 'emails.invoice';
                $array['subject'] = translate('You are assigned to delivery an order. Order code') . ' - ' . $order->code;
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;

                try {
                    Mail::to($order->delivery_boy->email)->queue(new InvoiceEmailManager($array));
                } catch (\Exception $e) {
                }
            }

            if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'assign_delivery_boy')->first()->status == 1) {
                try {
                    SmsUtility::assign_delivery_boy($order->delivery_boy->phone, $order->code);
                } catch (\Exception $e) {
                }
            }
        }

        return 1;
    } //end of method


    public function search($value)
    {
        $products =  Product::where('name', 'LIKE', '%' . $value . '%')->get();
        return view('backend.sales.search', compact('products'));
    } // end of search


    public function additem($id,$length)
    {
        $product =  Product::findOrFail($id);
        // dd($value);
        return view('backend.sales.product_details', compact('product','length'));
    } // end of search

    private function update_delivery($request, $status, $order_id)
    {
        $order = Order::findOrFail($order_id);
        $order->delivery_viewed = '0';
        $order->delivery_status = $status;
        $order->save();

        if ($status == 'cancelled' && $order->payment_type == 'wallet') {
            $user = User::where('id', $order->user_id)->first();
            $user->balance += $order->grand_total;
            $user->save();
        }

        if ($order->delivery_status == 'cancelled' && $order->payment_type == 'wallet') {
            $user = User::where('id', $order->user_id)->first();
            $user->balance -= $order->grand_total;
            $user->save();
        }

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->delivery_status = $status;
                $orderDetail->save();

                if ($status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }

                if ($order->delivery_status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {

                $orderDetail->delivery_status = $status;
                $orderDetail->save();

                if ($status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }
                if ($order->delivery_status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty -= $orderDetail->quantity;
                        $product_stock->save();
                    }
                }

                if (addon_is_activated('affiliate_system')) {
                    if (($status == 'delivered' || $status == 'cancelled') &&
                        $orderDetail->product_referral_code
                    ) {

                        $no_of_delivered = 0;
                        $no_of_canceled = 0;

                        if ($status == 'delivered') {
                            $no_of_delivered = $orderDetail->quantity;
                        }
                        if ($status == 'cancelled') {
                            $no_of_canceled = $orderDetail->quantity;
                        }

                        $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, 0, $no_of_delivered, $no_of_canceled);
                    }
                }
            }
        }
        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'delivery_status_change')->first()->status == 1) {
            try {
                SmsUtility::delivery_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {
            }
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->delivery_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('delivery_boy')) {
            if (Auth::user()->user_type == 'delivery_boy') {
                $deliveryBoyController = new DeliveryBoyController;
                $deliveryBoyController->store_delivery_history($order);
            }
        }
    }


    private function update_payment($request, $status, $order_id)
    {

        $order = Order::findOrFail($order_id);
        $order->payment_status_viewed = '0';
        $order->save();

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->payment_status = $status;
                $orderDetail->save();
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->payment_status = $status;
                $orderDetail->save();
            }
        }

        $status = 'paid';
        foreach ($order->orderDetails as $key => $orderDetail) {
            if ($orderDetail->payment_status != 'paid') {
                $status = 'unpaid';
            }
        }
        $order->payment_status = $status;
        $order->save();


        if ($order->payment_status == 'paid' && $order->commission_calculated == 0) {
            calculateCommissionAffilationClubPoint($order);
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->payment_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }



        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'payment_status_change')->first()->status == 1) {
            try {
                SmsUtility::payment_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {
            }
        }
    }
}
