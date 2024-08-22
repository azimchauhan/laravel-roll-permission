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
                            <div class="mb-3 controls">
                                <label for="parent_category_id" class="form-label">Parent Category</label>
                                <select class="form-control select2" name="parent_category_id" id="parent_category_id">
                                    <option value="">Select Parent Category</option>
                                    @foreach ($parent_categories as $parent_category)
                                        <option value="{{ $parent_category->id }}">{{ $parent_category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="mb-3 controls">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-control select2" name="category_id" id="category_id">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                                        <th>PARENT CATEGORY</th>
                                        <th>CATEGORY</th>
                                        <th>NAME</th>
                                        <th>PURCHASE PRICE</th>
                                        <th>SELLING PRICE</th>
                                        <th>IMAGE</th>
                                        <th>STATUS</th>
                                        <th>CREATED AT</th>
                                        @if (auth()->user()->can('product-edit') || auth()->user()->can('product-delete'))
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
            let url = '{!! route('admin.product.datatable') !!}';

            customDateRangePicker('#date');

            let columns = [
                { data: 'id', name: 'id' },
                @if (isSuperAdmin())
                    { data: 'user', name: 'user' },
                @endif
                { data: 'parent_category', name: 'parent_category' },
                { data: 'category', name: 'category' },
                { data: 'name', name: 'name' },
                { data: 'purchase_price', name: 'purchase_price' },
                { data: 'selling_price', name: 'selling_price' },
                { data: 'image', name: 'image' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                @if (auth()->user()->can('product-edit') || auth()->user()->can('product-delete'))
                    { data: 'action', name: 'action' },
                @endif
            ];

            // let sortingFalse = [1, 2, 6];
            let sortingFalse = [1, 2];

            @if (isSuperAdmin())
                sortingFalse = [1, 2, 3];
            @endif

            @if (auth()->user()->can('product-edit') || auth()->user()->can('product-delete'))
                @if (isSuperAdmin())
                    sortingFalse = [1, 2, 3, 7, 10];
                @else
                    sortingFalse = [1, 2, 6, 9];
                @endif
            @endif

            createDataTable(url, columns, ['user_id', 'parent_category_id', 'category_id', 'fltStatus', 'date'], sortingFalse);
        });
    </script>
@endsection
