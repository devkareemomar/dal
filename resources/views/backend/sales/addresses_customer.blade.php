<div class="row gutters-5">
    @foreach ($addresses as $key => $address)
        <div class="col-md-6 mb-3">
            <label class="aiz-megabox d-block bg-white mb-0">
                <input type="radio" name="address_id" city-cost="{{$address->city->cost ?? 0}}" value="{{ $address->id }}"
                @if ($address->set_default)
                    checked
                @endif
                 required>
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
