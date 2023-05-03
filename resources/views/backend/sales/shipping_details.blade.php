<section class="mb-4 gry-bg">
    <div class="row col-md-8 col-xl-10 m-2">
        <div class="col-md-4">
            <button type="button" class="btn btn-primary mt-4" onclick="add_new_customer()">{{ translate('Add New Customer') }}</button>
        </div>
        <div class="col-md-4">
            <label>{{ translate('Choose Customer') }} <span class="text-danger">*</span></label>

            <select class="form-control mb-3 aiz-selectpicker" id="customer" data-live-search="true" name="customer_id" required>
                <option value="" > اختر العميل</option>

                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{(request()->get('customer_id') == $customer->id)?'selected' : ''}}> {{ $customer->name }}</option>
                @endforeach

            </select>
        </div>

        <div class="col-md-4">
            <label>{{ translate('Payment Status') }} <span class="text-danger">*</span></label>

            <select class="form-control mb-3 aiz-selectpicker"  required data-live-search="true" name="payment_status">
                <option value="unpaid" > {{ translate('Un-Paid') }}</option>
                    <option value="paid" > {{ translate('Paid') }}</option>

            </select>
        </div>
    </div>

    <div class="col-md-8 col-xl-10">
        <div class="shadow-sm bg-white p-4 rounded mb-4 " id="addresses">
            @if (request()->get('customer_id'))
                 @include('backend.sales.addresses_customer',['addresses'=>\App\Models\Address::where('user_id',request()->get('customer_id'))->get() ])

            @endif

        </div>
    </div>
</section>
<!-- new address modal -->

