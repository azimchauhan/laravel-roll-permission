@extends('admin.layouts.app')
@if (isset($page_title) && $page_title != '')
    @section('title', $page_title . ' | ' . config('app.name'))
@else
    @section('title', config('app.name'))
@endif
@section('styles')
    @parent

@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="row">
                @can('user-list')
                    <div class="col-md-3">
                        <a href="{{ route('admin.user.index') }}">
                            <div class="card mini-stats-wid">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="media-body">
                                            <p class="text-muted fw-medium">Total Users</p>
                                            <h4 class="mb-0">{{ number_format($total_users) }}</h4>
                                        </div>

                                        <div class="mini-stat-icon avatar-sm rounded-circle bg-primary align-self-center">
                                            <span class="avatar-title">
                                                <i class="bx bxs-group font-size-24"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endcan

                @can('category-list')
                    <div class="col-md-3">
                        <a href="{{ route('admin.category.index') }}">
                            <div class="card mini-stats-wid">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="media-body">
                                            <p class="text-muted fw-medium">Total Categories</p>
                                            <h4 class="mb-0">{{ number_format($total_categories) }}</h4>
                                        </div>

                                        <div class="mini-stat-icon avatar-sm rounded-circle bg-primary align-self-center">
                                            <span class="avatar-title">
                                                <i class="bx bx-customize font-size-24"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endcan

                @can('product-list')
                    <div class="col-md-3">
                        <a href="{{ route('admin.product.index') }}">
                            <div class="card mini-stats-wid">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="media-body">
                                            <p class="text-muted fw-medium">Total Products</p>
                                            <h4 class="mb-0">{{ number_format($total_products) }}</h4>
                                        </div>

                                        <div class="mini-stat-icon avatar-sm rounded-circle bg-primary align-self-center">
                                            <span class="avatar-title">
                                                <i class="bx bx-grid-alt font-size-24"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection
@section('modals')

@endsection
@section('scripts')
    @parent

@endsection
