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
                                        <th>PERMISSION</th>
                                        <th>STATUS</th>
                                        <th>CREATED AT</th>
                                        @if (auth()->user()->can('permission-edit') || auth()->user()->can('permission-delete'))
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
            let url = '{!! route('admin.permission.datatable') !!}';

            customDateRangePicker('#date');

            let columns = [
                { data: 'id', name: 'id' },
                { data: 'display_name', name: 'display_name' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                @if (auth()->user()->can('permission-edit') || auth()->user()->can('permission-delete'))
                    { data: 'action', name: 'action' },
                @endif
            ];

            let sortingFalse = [];
            @if (auth()->user()->can('permission-edit') || auth()->user()->can('permission-delete'))
                sortingFalse = [4];
            @endif

            createDataTable(url, columns, ['fltStatus', 'date'], sortingFalse);
        })
    </script>
@endsection
