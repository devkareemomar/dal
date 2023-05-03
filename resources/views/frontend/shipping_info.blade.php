@extends('frontend.layouts.app')

@section('content')

<section class="pt-5 mb-4">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="row aiz-steps arrow-divider">
                    <div class="col done">
                        <div class="text-center text-success">
                            <i class="la-3x mb-2 las la-shopping-cart"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block ">{{ translate('1. My Cart')}}</h3>
                        </div>
                    </div>
                    <div class="col active">
                        <div class="text-center text-primary">
                            <i class="la-3x mb-2 las la-map"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block ">{{ translate('2. Shipping info')}}</h3>
                        </div>
                    </div>
                    {{-- <div class="col">
                        <div class="text-center">
                            <i class="la-3x mb-2 opacity-50 las la-truck"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">{{ translate('3. Delivery info')}}</h3>
                        </div>
                    </div> --}}
                    <div class="col">
                        <div class="text-center">
                            <i class="la-3x mb-2 opacity-50 las la-credit-card"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">{{ translate('4. Payment')}}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-center">
                            <i class="la-3x mb-2 opacity-50 las la-check-circle"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">{{ translate('5. Confirmation')}}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-4 gry-bg">
    <div class="container">
        <div class="row cols-xs-space cols-sm-space cols-md-space">
            <div class="col-xxl-8 col-xl-10 mx-auto">
                <form class="form-default" data-toggle="validator" action="{{ route('checkout.store_shipping_infostore') }}" role="form" method="POST">
                    @csrf
                    @if(Auth::check())
                    <div class="shadow-sm bg-white p-4 rounded mb-4">
                        <div class="row gutters-5">
                            @foreach (Auth::user()->addresses as $key => $address)
                                <div class="col-md-6 mb-3">
                                    <label class="aiz-megabox d-block bg-white mb-0">
                                        <input type="radio" name="address_id" value="{{ $address->id }}" @if ($address->set_default)
                                            checked
                                        @endif required>
                                        <span class="d-flex p-3 aiz-megabox-elem">
                                            <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                            <span class="flex-grow-1 pl-3 text-left">
                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('City') }}:</span>
                                                    <span class="ml-2">{{ optional($address->city)->name }}</span>
                                                </div>
                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('State') }}:</span>
                                                    <span class="ml-2">{{ optional($address->state)->name }}</span>
                                                </div>
                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('Country') }}:</span>
                                                    <span class="ml-2">{{ optional($address->country)->name }}</span>
                                                </div>
                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('Phone') }}:</span>
                                                    <span class="ml-2">{{ $address->phone }}</span>
                                                </div>
                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('block') }}:</span>
                                                    <span class="ml-2">{{ $address->block }}</span>
                                                </div>
                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('avenue') }}:</span>
                                                    <span class="ml-2">{{ $address->avenue }}</span>
                                                </div>
                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('street') }}:</span>
                                                    <span class="ml-2">{{ $address->street }}</span>
                                                </div>
                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('House') }}:</span>
                                                    <span class="ml-2">{{ $address->house }}</span>
                                                </div>
                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('Floor') }}:</span>
                                                    <span class="ml-2">{{ $address->floor }}</span>
                                                </div>

                                                <div>
                                                    <span class="w-50 fw-600">{{ translate('Flat') }}:</span>
                                                    <span class="ml-2">{{ $address->flat }}</span>
                                                </div>

                                            </span>
                                        </span>
                                    </label>
                                    <div class="dropdown position-absolute right-0 top-0">
                                        <button class="btn bg-gray px-2" type="button" data-toggle="dropdown">
                                            <i class="la la-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item" onclick="edit_address('{{$address->id}}')">
                                                {{ translate('Edit') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <input type="hidden" name="checkout_type" value="logged">
                            <div class="col-md-6 mx-auto mb-3" >
                                <div class="border p-3 rounded mb-3 c-pointer text-center bg-white h-100 d-flex flex-column justify-content-center" onclick="add_new_address()">
                                    <i class="las la-plus la-2x mb-3"></i>
                                    <div class="alpha-7">{{ translate('Add New Address') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                        <div class="shadow-sm bg-white p-4 rounded mb-4">
                            <input type="hidden" class="form-control" name="is_guest"  value="1">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="control-label">{{ translate('Name')}} <span class="text-danger">*</span> </label>
                                    <input type="text" class="form-control" name="name" placeholder="{{ translate('Name')}}" required>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group phone-form-group has-feedback">
                                        <label class="control-label">{{ translate('Phone')}} <span class="text-danger">*</span> </label>
                                        <input id="phone-code" type="tel" lang="en" min="0" maxlength="8" class="form-control {{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone') }}" placeholder="{{ translate('Phone')}}" name="phone" required
                                            oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" >
                                    </div>
                                </div>
                            </div>




                            <div class="row">


                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label>{{ translate('Country')}} <span class="text-danger">*</span></label>

                                        <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ translate('Select your country') }}" name="country_id" aria-readonly="" required>
                                            <option value="117" selected> الكويت</option>
                                            {{-- @foreach (\App\Models\Country::where('status', 1)->get() as $key => $country)
                                                <option value="{{ $country->id }}" selected>{{ $country->name }}</option>

                                            @endforeach --}}
                                        </select>
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <label>{{ translate('State')}} <span class="text-danger">*</span></label>

                                    <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" data-placeholder="{{ translate('Select your State') }}" name="state_id" required>
                                            <option value="">{{ translate('Select your State') }}</option>
                                            @foreach (\App\Models\State::where([['status',1],['country_id', 117]])->get() as $key => $state)
                                                <option value="{{ $state->id }}">{{ $state->name }}</option>

                                            @endforeach
                                    </select>

                                    {{-- <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="state_id" required> --}}

                                    </select>
                                </div>


                                <div class="col-md-6">
                                    <label>{{ translate('City')}} <span class="text-danger">*</span></label>

                                    <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="city_id" required>

                                    </select>
                                </div>



                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        <label class="control-label">{{ translate('Block')}} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" placeholder="{{ translate('Block')}}" name="block" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        <label class="control-label">{{ translate('Avenue')}}</label>
                                        <input type="text" class="form-control" placeholder="{{ translate('Avenue')}}" name="avenue" >
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        <label class="control-label">{{ translate('Street')}} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" placeholder="{{ translate('Street')}}" name="street" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        <label class="control-label">{{ translate('House')}} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" placeholder="{{ translate('House')}}" name="house" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        <label class="control-label">{{ translate('Floor')}}</label>
                                        <input type="text" class="form-control" placeholder="{{ translate('Floor')}}" name="floor">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        <label class="control-label">{{ translate('Flat Number')}}</label>
                                        <input type="text" class="form-control" placeholder="{{ translate('Flat No')}}" name="flat">
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="control-label">{{ translate('Email')}}</label>
                                    <input type="text" class="form-control" name="email" placeholder="{{ translate('Email')}}" >
                                </div>

                            </div>
                            <input type="hidden" name="checkout_type" value="guest">
                        </div>
                    @endif
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center text-md-left order-1 order-md-0">
                            <a href="{{ route('home') }}" class="btn btn-link">
                                <i class="las la-arrow-left"></i>
                                {{ translate('Return to shop')}}
                            </a>
                        </div>
                        <div class="col-md-6 text-center text-md-right">
                            <button type="submit" class="btn btn-primary fw-600">{{ translate('Continue to Delivery Info')}}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('modal')
    @include('frontend.partials.address_modal')
@endsection


@section('script')
    <script type="text/javascript">

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
                url: "{{route('get-state')}}",
                type: 'POST',
                data: {
                    country_id  : country_id
                },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if(obj != '') {
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
                url: "{{route('get-city')}}",
                type: 'POST',
                data: {
                    state_id: state_id
                },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if(obj != '') {
                        $('[name="city_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }
    </script>


@endsection
