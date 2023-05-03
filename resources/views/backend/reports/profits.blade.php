@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class=" align-items-center">
       <h1 class="h3">{{translate('Profits')}}</h1>
	</div>
</div>
<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">
            <form action="{{ route('profits.index') }}" method="GET">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">{{ translate('Select Date') }}</h5>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ translate('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-md btn-primary" type="submit">
                            {{ translate('Filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-3">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="px-3 pt-3">
                <div class="opacity-50">
                    <span class="fs-12 d-block">{{ translate('Total Orders') }}</span>

                </div>
                <div class="h3 fw-700 mb-3">
                   {{ $orders->count() }}

                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="px-3 pt-3">
                <div class="opacity-50">
                    <span class="fs-12 d-block">{{ translate('Total Price') }}</span>

                </div>
                <div class="h3 fw-700 mb-3">
                    {{ $totalPrice }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="px-3 pt-3">
                <div class="opacity-50">
                    <span class="fs-12 d-block">{{ translate('Total Cost') }}</span>

                </div>
                <div class="h3 fw-700 mb-3">
                    {{ $totalCost }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="px-3 pt-3">
                <div class="opacity-50">
                    <span class="fs-12 d-block">{{ translate('Net Profit') }}</span>

                </div>
                <div class="h3 fw-700 mb-3">
                    {{ $profits }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card p-3" >
           <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>{{translate('Code')}}</th>
                <th>{{translate('Date')}}</th>
                <th>{{translate('Grand Total')}}</th>
                <th>{{translate('Total Cost')}} </th>
                <th>{{translate('Profit')}} </th>
                <th>shapping</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
            <tr>
                <td>{{ $loop->index + 1 }}</td>
                <td>{{ $order->code }}</td>
                <td >{{ date('d-m-Y h:i A', $order->date) }}</td>
                <td>{{ $order->grand_total }}</td>
                <td>{{ $order->total_cost }}</td>
                <td>{{ $order->profit }}</td>
                <td>  {{ single_price($order->orderDetails->sum('shipping_cost')) }} </td>
            </tr>
            @endforeach
        </tbody>

    </table>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#example').DataTable();
        });
    </script>
@endsection

