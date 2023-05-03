<div class="modal larag fade" id="new-customer-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Add Customer') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="shadow-sm bg-white p-4 rounded mb-4">
                    <form class="form-default" role="form" action="{{ route('orders.add-customer') }}"
                        method="POST">
                        @csrf
                        <input type="hidden" class="form-control" name="is_guest" value="1">

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">{{ translate('Name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="name"
                                    placeholder="{{ translate('Name') }}" required>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group phone-form-group has-feedback">
                                    <label class="control-label">{{ translate('Phone') }} <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input id="phone-code" type="tel" lang="en" min="0" maxlength="8"
                                        class="form-control {{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                        value="{{ old('phone') }}" placeholder="{{ translate('Phone') }}"
                                        name="phone" required
                                        oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">
                                </div>
                            </div>
                        </div>




                        <div class="row">


                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>{{ translate('Country') }} <span class="text-danger">*</span></label>

                                    <select class="form-control aiz-selectpicker" data-live-search="true"
                                        data-placeholder="{{ translate('Select your country') }}" name="country_id"
                                        aria-readonly="" required>
                                        <option value="117" selected> الكويت</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <label>{{ translate('State') }} <span class="text-danger">*</span></label>

                                <select class="form-control mb-3 aiz-selectpicker" data-live-search="true"
                                    data-placeholder="{{ translate('Select your State') }}" name="state_id" required>
                                    <option value="">{{ translate('Select your State') }}</option>
                                    @foreach (\App\Models\State::where([['status', 1], ['country_id', 117]])->get() as $key => $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>

                                {{-- <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="state_id" required> --}}

                                </select>
                            </div>


                            <div class="col-md-6">
                                <label>{{ translate('City') }} <span class="text-danger">*</span></label>

                                <select class="form-control mb-3 aiz-selectpicker" data-live-search="true"
                                    name="city_id" required>

                                </select>
                            </div>



                            <div class="col-md-6">
                                <div class="form-group has-feedback">
                                    <label class="control-label">{{ translate('Block') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="{{ translate('Block') }}"
                                        name="block" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group has-feedback">
                                    <label class="control-label">{{ translate('Avenue') }}</label>
                                    <input type="text" class="form-control" placeholder="{{ translate('Avenue') }}"
                                        name="avenue">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group has-feedback">
                                    <label class="control-label">{{ translate('Street') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="{{ translate('Street') }}"
                                        name="street" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group has-feedback">
                                    <label class="control-label">{{ translate('House') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="{{ translate('House') }}"
                                        name="house" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group has-feedback">
                                    <label class="control-label">{{ translate('Floor') }}</label>
                                    <input type="text" class="form-control"
                                        placeholder="{{ translate('Floor') }}" name="floor">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group has-feedback">
                                    <label class="control-label">{{ translate('Flat Number') }}</label>
                                    <input type="text" class="form-control"
                                        placeholder="{{ translate('Flat No') }}" name="flat">
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">{{ translate('Email') }}</label>
                                <input type="text" class="form-control" name="email"
                                    placeholder="{{ translate('Email') }}">
                            </div>

                        </div>
                        <input type="hidden" name="checkout_type" value="guest">
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
