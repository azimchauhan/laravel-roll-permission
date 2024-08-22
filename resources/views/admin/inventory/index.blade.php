@extends('admin.layouts.app')

@if (isset($page_title) && $page_title != '')
    @section('title', $page_title . ' | ' . config('app.name'))
@else
    @section('title', config('app.name'))
@endif

@section('styles')
    @parent
    <link href="{{ asset('assets/libs/dataTables/dataTables.min.css') }}" rel="stylesheet">

    <style>
        table#dataTable tbody tr:nth-child(even) td.dangerRow {
            background-color: #f8d7da !important;
        }

        table#dataTable tbody tr:nth-child(odd) td.dangerRow {
            --bs-table-accent-bg: #f8d7da !important;
        }
    </style>
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
                            <div class="mb-3 controls">
                                <label for="product_id" class="form-label">Product</label>
                                <select class="form-control select2" name="product_id" id="product_id">
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="mb-3 controls">
                                <label for="fltStatus" class="form-label">Status</label>
                                <select class="form-control select2" name="fltStatus" id="fltStatus">
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">InActive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="text" class="form-control date" name="date" id="date" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label>Quantity Range</label>
                                <div class="input-group">
                                    <input type="text" class="form-control numbers_only" name="quantity_from" id="quantity_from" placeholder="Start Quantity Size" />
                                    <input type="text" class="form-control numbers_only" name="quantity_to" id="quantity_to" placeholder="End Quantity Size" />
                                </div>
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
                                        <th>PRODUCT</th>
                                        <th>QUANTITY</th>
                                        <th>PURCHASE PRICE</th>
                                        <th>SELLING PRICE</th>
                                        <th>STATUS</th>
                                        <th>CREATED AT</th>
                                        @if (auth()->user()->can('inventory-edit') || auth()->user()->can('inventory-delete'))
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

@section('scripts')
    @parent
    <script src="{{ asset('assets/libs/dataTables/dataTables.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            let table;
            let url = '{!! route('admin.inventory.datatable') !!}';

            customDateRangePicker('#date');

            let columns = [
                { data: 'id', name: 'id' },
                @if (isSuperAdmin())
                    { data: 'user', name: 'user' },
                @endif
                { data: 'product', name: 'product' },
                { data: 'quantity', name: 'quantity' },
                { data: 'purchase_price', name: 'purchase_price' },
                { data: 'selling_price', name: 'selling_price' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                @if (auth()->user()->can('inventory-edit') || auth()->user()->can('inventory-delete'))
                    { data: 'action', name: 'action' },
                @endif
            ];

            let sortingFalse = [1];

            @if (isSuperAdmin())
                sortingFalse = [1, 2];
            @endif

            @if (auth()->user()->can('inventory-edit') || auth()->user()->can('inventory-delete'))
                @if (isSuperAdmin())
                    sortingFalse = [1, 2, 8];
                @else
                    sortingFalse = [1, 7];
                @endif
            @endif

            inventoryDataTable(url, columns, ['user_id', 'product_id', 'fltStatus', 'date', 'quantity_from', 'quantity_to',], sortingFalse);
        });

        function inventoryDataTable(url, columns, filter = [], sortingFalse = []) {
            if (!($.fn.DataTable.isDataTable('#dataTable'))) {
                table = $('#dataTable').DataTable({
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    searching: true,
                    ajax: {
                        data: function (d) {
                            d._token = $('meta[name="csrf-token"]').attr('content');
                            d.filter = getFilterData(filter);
                        },
                        url: url,
                        type: 'POST',
                    },
                    columns: columns,
                    order: [0, 'desc'],
                    aoColumnDefs: [
                        { "bSortable": false, "aTargets": sortingFalse },
                    ],
                    fnRowCallback: function(row, data, index, indexFull) {
                        let envMinInvQty = {{ env('MIN_INVENTORY_QTY') }};

                        if (data.quantity <= envMinInvQty) {
                            $('td', row).addClass('dangerRow');
                        }
                    }
                });
                return table;
            } else {
                table.ajax.reload();
            }
        }
    </script>
@endsection
