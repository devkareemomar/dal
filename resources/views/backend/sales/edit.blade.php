@extends('backend.layouts.app')



@section('style')
    <link href="{{ static_asset('assets/invoice/css/jquery-ui.min.css') }}" rel="stylesheet">
    <link href="{{ static_asset('assets/invoice/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')

    <div class="card">

        <!-- Begin page content -->
        <div class="card-header">
            <h1 class="h2 fs-16 mb-0">{{ translate('Order Details') }}</h1>
        </div>

        <div class="card-body">
            <div class="row gutters-5">
                <div class="col text-md-left text-center">
                </div>
                @php
                    $delivery_status = $order->delivery_status;
                    $payment_status = $order->payment_status;
                    $admin_user_id = App\Models\User::where('user_type', 'admin')->first()->id;
                @endphp

                <!--Assign Delivery Boy-->
                @if ($order->seller_id == $admin_user_id || get_setting('product_manage_by_admin') == 1)

                    @if (addon_is_activated('delivery_boy'))
                        <div class="col-md-3 ml-auto">
                            <label for="assign_deliver_boy">{{ translate('Assign Deliver Boy') }}</label>
                            @if (
                                ($delivery_status == 'pending' || $delivery_status == 'confirmed' || $delivery_status == 'picked_up') &&
                                    auth()->user()->can('assign_delivery_boy_for_orders'))
                                <select class="form-control aiz-selectpicker" data-live-search="true"
                                    data-minimum-results-for-search="Infinity" id="assign_deliver_boy">
                                    <option value="">{{ translate('Select Delivery Boy') }}</option>
                                    @foreach ($delivery_boys as $delivery_boy)
                                        <option value="{{ $delivery_boy->id }}"
                                            @if ($order->assign_delivery_boy == $delivery_boy->id) selected @endif>
                                            {{ $delivery_boy->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control"
                                    value="{{ optional($order->delivery_boy)->name }}" disabled>
                            @endif
                        </div>
                    @endif

                    {{-- <div class="col-md-3 ml-auto">
                        <label for="update_payment_status">{{ translate('Payment Status') }}</label>
                        @if (auth()->user()->can('update_order_payment_status'))
                            <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                                id="update_payment_status">
                                <option value="unpaid" @if ($payment_status == 'unpaid') selected @endif>
                                    {{ translate('Unpaid') }}
                                </option>
                                <option value="paid" @if ($payment_status == 'paid') selected @endif>
                                    {{ translate('Paid') }}
                                </option>
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ $payment_status }}" disabled>
                        @endif
                    </div>
                    <div class="col-md-3 ml-auto">
                        <label for="update_delivery_status">{{ translate('Delivery Status') }}</label>
                        @if (auth()->user()->can('update_order_delivery_status') &&
    $delivery_status != 'delivered' &&
    $delivery_status != 'cancelled')
                            <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                                id="update_delivery_status">
                                <option value="pending" @if ($delivery_status == 'pending') selected @endif>
                                    {{ translate('Pending') }}
                                </option>
                                <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>
                                    {{ translate('Confirmed') }}
                                </option>
                                <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>
                                    {{ translate('Picked Up') }}
                                </option>
                                <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>
                                    {{ translate('On The Way') }}
                                </option>
                                <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>
                                    {{ translate('Delivered') }}
                                </option>
                                <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>
                                    {{ translate('Cancel') }}
                                </option>
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ $delivery_status }}" disabled>
                        @endif
                    </div>
                    <div class="col-md-3 ml-auto">
                        <label for="update_tracking_code">
                            {{ translate('Tracking Code (optional)') }}
                        </label>
                        <input type="text" class="form-control" id="update_tracking_code"
                            value="{{ $order->tracking_code }}">
                    </div> --}}
                @endif
            </div>
            <div class="mb-3">
                @php
                $removedXML = '<?xml version="1.0" encoding="UTF-8"@endphp';
                ?>
                {!! str_replace($removedXML, '', QrCode::size(100)->generate($order->code)) !!}
            </div>
            <div class="row gutters-5">
                <div class="col text-md-left text-center">
                    @if (json_decode($order->shipping_address))
                        <address>
                            <strong class="text-main">
                                {{ json_decode($order->shipping_address)->name }}
                            </strong><br>
                            {{ json_decode($order->shipping_address)->email }}<br>
                            {{ json_decode($order->shipping_address)->phone }}<br>
                            {{ json_decode($order->shipping_address)->address }},
                            {{ json_decode($order->shipping_address)->city }},
                            {{ json_decode($order->shipping_address)->postal_code }}<br>
                            {{ json_decode($order->shipping_address)->country }}
                        </address>
                    @else
                        <address>
                            <strong class="text-main">
                                {{ $order->user->name }}
                            </strong><br>
                            {{ $order->user->email }}<br>
                            {{ $order->user->phone }}<br>
                        </address>
                    @endif
                    @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                        <br>
                        <strong class="text-main">{{ translate('Payment Information') }}</strong><br>
                        {{ translate('Name') }}: {{ json_decode($order->manual_payment_data)->name }},
                        {{ translate('Amount') }}:
                        {{ single_price(json_decode($order->manual_payment_data)->amount) }},
                        {{ translate('TRX ID') }}: {{ json_decode($order->manual_payment_data)->trx_id }}
                        <br>
                        <a href="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" target="_blank">
                            <img src="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" alt=""
                                height="100">
                        </a>
                    @endif
                </div>
                <div class="col-md-4 ml-auto">
                    <table>
                        <tbody>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Order #') }}</td>
                                <td class="text-info text-bold text-right"> {{ $order->code }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Order Status') }}</td>
                                <td class="text-right">
                                    @if ($delivery_status == 'delivered')
                                        <span class="badge badge-inline badge-success">
                                            {{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}
                                        </span>
                                    @else
                                        <span class="badge badge-inline badge-info">
                                            {{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Order Date') }} </td>
                                <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">
                                    {{ translate('Total amount') }}
                                </td>
                                <td class="text-right">
                                    {{ single_price($order->grand_total) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Payment method') }}</td>
                                <td class="text-right">
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Additional Info') }}</td>
                                <td class="text-right">{{ $order->additional_info }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="new-section-sm bord-no">

            <form action="{{ url('admin/orders/update', $order->id) }}" method="POST">
                @csrf
                <div class='row'>
                    <div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>

                        <table class="table item-table table-bordered table-hover">
                            <div class="m-2 col-4 row d-flex">
                                <h5 class="col-5 " for="">{{ translate('Add Product:') }}</h5>
                                <input type="text" id="1" placeholder="{{ translate('search here....') }}"
                                    class="form-control col-6 form-control-sm search title1" autocomplete="off">
                                <div class="content-search"> </div>
                            </div>
                            <thead>
                                <tr>
                                    <th width="2%"><input id="check_all" class="formcontrol" type="checkbox" /></th>
                                    {{-- <th width="5%">{{ translate('Item No') }}</th> --}}
                                    <th width="15%">{{ translate('Item Name') }}</th>
                                    <th width="15%">{{ translate('Color') }}</th>
                                    <th width="15%">{{ translate('Choice Options') }}</th>
                                    <th width="15%">{{ translate('Price') }}</th>
                                    <th width="15%">{{ translate('Quantity') }}</th>
                                    <th width="15%">{{ translate('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderDetails as $key => $orderDetail)
                                    <tr id="{{ $key + 1 }}">
                                        <td><input class="case" type="checkbox" /></td>


                                        <td>


                                            <input type="hidden" id="item_1"
                                                name="items[{{ $orderDetail->product_id }}][detail_id]"
                                                value="{{ $orderDetail->id }}"
                                                class=" form-control form-control-sm search itemid item_id1"
                                                placeholder="@lang('site.item')" autocomplete="off">
                                            <input type="hidden" id="item_1"
                                                name="items[{{ $orderDetail->product_id }}][id]"
                                                value="{{ $orderDetail->product_id }}"
                                                class=" form-control form-control-sm search itemid item_id1"
                                                placeholder="@lang('site.item')" autocomplete="off">
                                            <span
                                                id="itemid{{ $orderDetail->product_id }}">{{ $orderDetail->product->getTranslation('name') }}</span>
                                            {{-- <div class="content-search1"> </div> --}}
                                            {{-- <input type="text" value="{{ $orderDetail->product->getTranslation('name') }}" data-type="productName" name="itemName[]" id="itemName_1" class="form-control autocomplete_txt" autocomplete="off" readonly> --}}

                                        </td>
                                        <td>
                                            @if (count(json_decode($orderDetail->product->colors)) > 0)
                                                <div class="row no-gutters">

                                                    <div class="col-sm-10">
                                                        <div class="aiz-radio-inline">
                                                            @foreach (json_decode($orderDetail->product->colors) as $key => $color)
                                                                @php
                                                                    $color_name = \App\Models\Color::where('code', $color)->first()->name;
                                                                @endphp
                                                                <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip"
                                                                    data-title="{{ $color_name }}">
                                                                    <input type="radio"
                                                                        name="items[{{ $orderDetail->product_id }}][color]"
                                                                        value="{{ $color_name }}"
                                                                        @if (in_array($color_name, explode('-', $orderDetail->variation))) checked @endif>
                                                                    <span
                                                                        class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                                                        <span class="size-15px d-inline-block rounded"
                                                                            style="background: {{ $color }};"></span>
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($orderDetail->product->choice_options != null)
                                                @foreach (json_decode($orderDetail->product->choice_options) as $key => $choice)
                                                    <div class="row no-gutters">
                                                        <div class="col-sm-2">
                                                            <div class="opacity-50 my-2">
                                                                {{ \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name') }}:
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-10">
                                                            <div class="aiz-radio-inline">
                                                                @foreach ($choice->values as $key => $value)
                                                                    <label class="aiz-megabox pl-0 mr-2">
                                                                        <input type="radio"
                                                                            name="items[{{ $orderDetail->product_id }}][attribute]"
                                                                            value="{{ $value }}"
                                                                            @if (in_array($value, explode('-', $orderDetail->variation))) checked @endif>
                                                                        <span
                                                                            class=" aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-2 px-3 mb-2">
                                                                            {{ $value }}
                                                                        </span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td><input type="number" name="items[{{ $orderDetail->product_id }}][price]"
                                                readonly value="{{ $orderDetail->product->unit_price }}"
                                                id="price_{{ $orderDetail->product_id }}" class="form-control changesNo"
                                                autocomplete="off" onkeypress="return IsNumeric(event);"
                                                ondrop="return false;" onpaste="return false;"></td>
                                        <td><input type="number" name="items[{{ $orderDetail->product_id }}][quantity]"
                                                value="{{ $orderDetail->quantity }}"
                                                id="quantity_{{ $orderDetail->product_id }}"
                                                class="form-control changesNo" autocomplete="off"
                                                onkeypress="return IsNumeric(event);" ondrop="return false;"
                                                onpaste="return false;"></td>
                                        <td><input type="number" name="items[{{ $orderDetail->product_id }}][total]"
                                                readonly id="total_{{ $orderDetail->product_id }}"
                                                value="{{ $orderDetail->price }}" class="form-control totalLinePrice"
                                                autocomplete="off" onkeypress="return IsNumeric(event);"
                                                ondrop="return false;" onpaste="return false;"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>



                    </div>
                </div>

                <div class='row'>
                    <div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>
                        <button class="btn btn-danger delete" type="button">- </button>
                        {{-- <button class="btn btn-success addmore" type="button">+ </button> --}}
                    </div>
                </div>


                <div class="clearfix float-right">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Sub Total') }} :</strong>
                                </td>
                                <td>
                                    {{-- <input type="number" class="form-control" id="subTotal" placeholder="Subtotal" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"> --}}

                                    <span id="subTotal"> {{ single_price($order->orderDetails->sum('price')) }} </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Tax') }} :</strong>
                                </td>
                                <td>
                                    <input type="hidden" class="form-control" id="tax" name="tax"
                                        value="{{ $order->orderDetails->first()->tax }}">

                                    {{ single_price($order->orderDetails->first()->tax) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Shipping') }} :</strong>
                                </td>
                                <td>
                                    <input type="hidden" class="form-control" id="shipping_cost" name="shipping_cost"
                                        value="{{ $order->shipping_cost }}">

                                    {{ single_price($order->shipping_cost) }}

                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Coupon') }} :</strong>
                                </td>
                                <td>
                                    {{ single_price($order->coupon_discount) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('TOTAL') }} :</strong>
                                </td>
                                <td class="text-muted h5">
                                    <input class="grand_total" type="hidden" name="grand_total"
                                        value="{{ $order->grand_total }}">
                                    <span class="grand_total"> {{ single_price($order->grand_total) }} </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="no-print text-right">
                        <button type="submit" class="btn btn-success"> {{ translate('save') }} </button>
                        <a href="{{ route('invoice.download', $order->id) }}" type="button"
                            class="btn btn-icon btn-light"><i class="las la-print"></i></a>

                    </div>
                </div>


            </form>
        </div>

    </div>

@endsection

@section('script')
    <script src="{{ static_asset('assets/invoice/js/jquery-ui.min.js') }}"></script>
    <script>
        // search for item  by ajax request
        $(".search").keyup(function() {
            var value = $(this).val();

            $.ajax({
                type: 'get',
                url: "{{ url('admin/orders/search/') }}" + '/' + value,
                success: function(data) {
                    $('.content-search').html(data);

                },
                error: function(data_error, exception) {
                    if (exception == 'error') {
                        var error_list = '';
                        $.each(data_error.responseJSON.errors, function(index, v) {
                            error_list += '<li>' + v + '</li>';
                        });
                        $('.alert-errors ul').html(error_list)
                    }
                }
            });
        });
    </script>
    <script src="{{ static_asset('assets/invoice/js/auto.js') }}"></script>
@endsection
