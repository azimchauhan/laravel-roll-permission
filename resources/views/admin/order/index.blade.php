@extends('admin.layouts.app')

@if (isset($page_title) && $page_title != '')
    @section('title', $page_title . ' | ' . config('app.name'))
@else
    @section('title', config('app.name'))
@endif

@section('styles')
    @parent
    <link href="{{ asset('assets/libs/dataTables/dataTables.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @if (isSuperAdmin())
                            <div class="col-lg-4">
                                <div class="mb-3 controls">
                                    <label for="user_id" class="form-label">User</label>
                                    <select class="form-control select2" name="user_id" id="user_id">
                                        <option value="">Select User</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="text" class="form-control date" name="date" id="date"
                                    autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <button type="submit" class="btn btn-primary w-md button-responsive" onclick="createDataTable()">Filter</button>
                            <button type="submit" class="btn btn-secondary w-md button-responsive" onclick="resetFilter()">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0" data-pattern="priority-columns">
                            <table id="dataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        @if (isSuperAdmin())
                                            <th>USER</th>
                                        @endif
                                        <th>CUSTOMER NAME</th>
                                        <th>CUSTOMER NUMBER</th>
                                        <th>GRAND TOTAL</th>
                                        <th>CREATED AT</th>
                                        @if (auth()->user()->can('order-list') || auth()->user()->can('order-edit') || auth()->user()->can('order-delete'))
                                            <th>ACTION</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    @parent
    <div id="modaOrderProducts" class="modal fade bs-example-modal-lg" data-bs-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modaOrderProductsHeading">Order Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <table id="orderProductsDataTable" class="table table-bordered dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Product Name</th>
                                                <th>Selling Price</th>
                                                <th>Quantity</th>
                                                <th>Total Amount</th>
                                            </tr>
                                        </thead>

                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('assets/libs/dataTables/dataTables.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            let table;
            let url = '{!! route('admin.order.datatable') !!}';

            customDateRangePicker('#date');

            let columns = [
                { data: 'id', name: 'id' },
                @if (isSuperAdmin())
                    { data: 'user', name: 'user' },
                @endif
                { data: 'customer_name', name: 'customer_name' },
                { data: 'customer_number', name: 'customer_number' },
                { data: 'grand_total', name: 'grand_total' },
                { data: 'created_at', name: 'created_at' },
                @if (auth()->user()->can('order-list') || auth()->user()->can('order-edit') || auth()->user()->can('order-delete'))
                    { data: 'action', name: 'action' },
                @endif
            ];

            let sortingFalse = [];

            @if (isSuperAdmin())
                sortingFalse = [1];
            @endif

            @if (auth()->user()->can('order-list') || auth()->user()->can('order-edit') || auth()->user()->can('order-delete'))
                @if (isSuperAdmin())
                    sortingFalse = [1, 6];
                @else
                    sortingFalse = [2, 5];
                @endif
            @endif

            createDataTable(url, columns, ['user_id', 'date'], sortingFalse);
        });

        $(document).on('click', '.orderProducts', function() {
            let orderId = $(this).data('order-id');

            if (($.fn.DataTable.isDataTable('#orderProductsDataTable'))) {
                $('#orderProductsDataTable').DataTable().destroy();
            }

            $('#orderProductsDataTable tbody').empty();

            orderProductsTable = $('#orderProductsDataTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                searching: true,
                pageLength: 10,
                ajax: {
                    url: "{!! route('admin.order.products.datatable') !!}",
                    type: 'POST',
                    dataType: "json",
                    data: {
                        orderId: orderId
                    },
                    beforeSend: function() {
                        if (typeof orderProductsTable != 'undefined' && orderProductsTable.hasOwnProperty('settings')) {
                            orderProductsTable.settings()[0].jqXHR.abort();
                        }
                    }
                },
                columns: [
                    { data: 'product', name: 'product', sortable: false },
                    { data: 'selling_price', name: 'selling_price' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'total_amount', name: 'total_amount' },
                ],
            });

            $('#modaOrderProducts').modal('show');
        });
    </script>
@endsection
