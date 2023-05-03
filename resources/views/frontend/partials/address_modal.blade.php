<div class="modal fade" id="new-address-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('New Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-default" role="form" action="{{ route('addresses.store') }}" method="POST">
                @csrf
                        <input type="hidden" name="customer_id" id="set_customer_id" value="">
                        {{-- <input type="hidden" name="admin_order" id="admin_order" value=""> --}}
                <div class="modal-body">
                    <div class="p-3">
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Phone')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('+965')}}" name="phone" value="" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Country')}}</label>
                            </div>
                            <div class="col-md-10">
                                <div class="mb-3">
                                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ translate('Select your country') }}" name="country_id" required>
                                        <option value="117" selected> الكويت</option>

                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('State')}}</label>
                            </div>
                            <div class="col-md-10">
                                <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="state_id" required>
                            <option value="">{{ translate('Select your State') }}</option>
                            @foreach (\App\Models\State::where([['status', 1], ['country_id', 117]])->get() as $key => $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('City')}}</label>
                            </div>
                            <div class="col-md-10">
                                <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="city_id" required>

                                </select>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('block')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('block')}}" name="block" value="" required>
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('avenue')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('avenue')}}" name="avenue" value="" >
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('street')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('street')}}" name="street" value="" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('House')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('house')}}" name="house" value="" required>
                            </div>
                        </div>

                         <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Floor')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('Floor')}}" name="floor" value="" >
                            </div>
                        </div>

                         <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Flat')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('Flat')}}" name="flat" value="" >
                            </div>
                        </div>







                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-address-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('New Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="edit_modal_body">

            </div>
        </div>
    </div>
</div>

{{-- @section('script') --}}
    <script type="text/javascript">
        function add_new_address(){
            var customer_id = $('#customer_id').val();
            $('#set_customer_id').val(customer_id);
            $('#new-address-modal').modal('show');
            $("#close-button").click();
        }

        function edit_address(address) {
            var url = '{{ route("addresses.edit", ":id") }}';
            url = url.replace(':id', address);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function (response) {
                    $('#edit_modal_body').html(response.html);
                    $('#edit-address-modal').modal('show');
                    AIZ.plugins.bootstrapSelect('refresh');

                    @if (get_setting('google_map') == 1)
                        var lat     = -33.8688;
                        var long    = 151.2195;

                        if(response.data.address_data.latitude && response.data.address_data.longitude) {
                            lat     = parseFloat(response.data.address_data.latitude);
                            long    = parseFloat(response.data.address_data.longitude);
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


    @if (get_setting('google_map') == 1)
        @include('frontend.partials.google_map')
    @endif
{{-- @endsection --}}
