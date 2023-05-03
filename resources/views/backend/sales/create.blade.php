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
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    {!! implode('', $errors->all('<span >:message</span> <br>')) !!}
                </div>
            @endif

            <form action="{{ url('admin/orders/store-order-from-admin') }}" method="POST">
                @csrf
                @include('backend.sales.shipping_details')
                <input type="hidden" name="added_by" value="admin">




                <div class='row'>
                    <div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>

                        <table class="table item-table table-bordered table-hover">
                            <div class="m-2 col-6 row d-flex">
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

                                    <span id="subTotal"> 00</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Tax') }} :</strong>
                                </td>
                                <td>
                                    <input type="hidden" class="form-control" id="tax" name="tax" value="0">

                                    0
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Shipping') }} :</strong>
                                </td>
                                <td>
                                    <input type="hidden" class="form-control shipping_cost" id="shipping_cost"
                                        name="shipping_cost" value="0">


                                    <span class="shipping_cost">0</span>
                                    {{-- {{ single_price($order->orderDetails->first()->shipping_cost) }} --}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Coupon') }} :</strong>
                                </td>
                                <td>
                                    0
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('TOTAL') }} :</strong>
                                </td>
                                <td class="text-muted h5">
                                    <input class="grand_total" type="hidden" name="grand_total" value="0">
                                    <span class="grand_total">0 </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="no-print text-right">
                        <button type="submit" class="btn btn-success"> {{ translate('save') }} </button>
                    </div>
                </div>


                <div class="row mt-5">

                    <div class="form-group col-md-6">
                        <hr class="new-section-sm bord-no">
                        <label>{{ translate('Additional Info') }} </label>

                        <textarea name="additional_info" rows="5" class="form-control" placeholder="Type your text"></textarea>
                    </div>

                </div>

            </form>
        </div>

    </div>
@endsection


@include('backend.partials.address_modal')
@include('backend.sales.add_customer')

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
    <script type="text/javascript">
        function add_new_address() {
            var customer_id = $('#customer').val();
            $('#set_customer_id').val(customer_id);
            $('#admin_order').val(1);
            $('#new-address-modal').modal('show');
            $("#close-button").click();
        }

        function edit_address(address) {
            var url = '{{ route('addresses.edit', ':id') }}';
            url = url.replace(':id', address);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#edit_modal_body').html(response.html);
                    $('#edit-address-modal').modal('show');
                    AIZ.plugins.bootstrapSelect('refresh');

                    @if (get_setting('google_map') == 1)
                        var lat = -33.8688;
                        var long = 151.2195;

                        if (response.data.address_data.latitude && response.data.address_data.longitude) {
                            lat = parseFloat(response.data.address_data.latitude);
                            long = parseFloat(response.data.address_data.longitude);
                        }

                        initialize(lat, long, 'edit_');
                    @endif
                }
            });
        }

        $(document).on('change', '[name=country_id]', function() {
            var country_id = $(this).val();
            get_states(country_id);
        });

        $(document).on('change', '[name=state_id]', function() {
            var state_id = $(this).val();
            get_city(state_id);
        });


        function get_states(country_id) {
            $('[name="state"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('get-state') }}",
                type: 'POST',
                data: {
                    country_id: country_id
                },
                success: function(response) {
                    var obj = JSON.parse(response);
                    if (obj != '') {
                        $('[name="state_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        function get_city(state_id) {
            $('[name="city"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('get-city') }}",
                type: 'POST',
                data: {
                    state_id: state_id
                },
                success: function(response) {
                    var obj = JSON.parse(response);
                    if (obj != '') {
                        $('[name="city_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        function add_new_customer() {
            $('#new-customer-modal').modal('show');
        }
        $(document).on('click', '[name="address_id"]', function() {
            var cost = parseFloat($(this).attr('city-cost'));
            $('.shipping_cost').val(cost)
            $('.shipping_cost').text(cost);
            calculateTotal();
        });
        $(document).ready(function() {
            var cost = parseFloat($('[name="address_id"]').attr('city-cost'));
            $('.shipping_cost').val(cost)
            $('.shipping_cost').text(cost);
            calculateTotal();
        });
    </script>
    <script>
        $("#customer").change(function() {
            var customer_id = $(this).val();

            $.ajax({
                type: 'get',
                url: "{{ url('/addresses/customer-addresses/') }}" + '/' + customer_id,
                success: function(data) {
                    $('#addresses').html(data);
                    var cost = parseFloat($('[name="address_id"]').attr('city-cost'));
                    $('.shipping_cost').val(cost)
                    $('.shipping_cost').text(cost);
                    calculateTotal();
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

        $("#addCustomer").click(function() {
            $('#customer').val('');
            AIZ.plugins.bootstrapSelect('refresh');

            $('#addresses').html(`@include('backend.sales.add_customer')`);

        });
    </script>
@endsection
