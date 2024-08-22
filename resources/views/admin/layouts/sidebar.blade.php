<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu">Menu</li>

                <li>
                    <a href="{{ route('admin.index') }}" class="waves-effect">
                        <i class="bx bx-home-circle"></i>
                        <span key="t-dashboards">Dashboard</span>
                    </a>
                </li>

                @canany(['user-list', 'role-list', 'permission-list'])
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="bx bx-user"></i>
                            <span key="t-layouts"> Users Management </span>
                        </a>

                        <ul class="sub-menu" aria-expanded="true">
                            @can('role-list')
                                <li {{ (\Route::is('admin.role.index') || \Route::is('admin.role.edit') || \Route::is('admin.role.create')) ? 'class=mm-active' : '' }}>
                                    <a href="{{ route('admin.role.index') }}" key="t-light-sidebar"> Roles </a>
                                </li>
                            @endcan

                            @can('permission-list')
                                <li {{ (\Route::is('admin.permission.index') || \Route::is('admin.permission.edit') || \Route::is('admin.permission.create')) ? 'class=mm-active' : '' }}>
                                    <a href="{{ route('admin.permission.index') }}" key="t-light-sidebar"> Permissions </a>
                                </li>
                            @endcan

                            @can('user-list')
                                <li {{ (\Route::is('admin.user.index') || \Route::is('admin.user.edit') || \Route::is('admin.user.create')) ? 'class=mm-active' : '' }}>
                                    <a href="{{ route('admin.user.index') }}" key="t-light-sidebar"> Users </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                @can('category-list')
                    <li {{ (\Route::is('admin.category.index') || \Route::is('admin.category.edit') || \Route::is('admin.category.create')) ? 'class=mm-active' : '' }}>
                        <a href="{{ route('admin.category.index') }}" class="waves-effect">
                            <i class="bx bx-customize"></i>
                            <span key="t-category">Category</span>
                        </a>
                    </li>
                @endcan

                @can('product-list')
                    <li {{ (\Route::is('admin.product.index') || \Route::is('admin.product.edit') || \Route::is('admin.product.create')) ? 'class=mm-active' : '' }}>
                        <a href="{{ route('admin.product.index') }}" class="waves-effect">
                            <i class="bx bx-grid-alt"></i>
                            <span key="t-product">Product</span>
                        </a>
                    </li>
                @endcan

                @can('inventory-list')
                    <li {{ (\Route::is('admin.inventory.index') || \Route::is('admin.inventory.edit') || \Route::is('admin.inventory.create')) ? 'class=mm-active' : '' }}>
                        <a href="{{ route('admin.inventory.index') }}" class="waves-effect">
                            <i class="bx bx-store-alt"></i>
                            <span key="t-inventory">Inventory</span>
                        </a>
                    </li>
                @endcan

                @can('order-list')
                    <li {{ (\Route::is('admin.order.index') || \Route::is('admin.order.edit') || \Route::is('admin.order.create')) ? 'class=mm-active' : '' }}>
                        <a href="{{ route('admin.order.index') }}" class="waves-effect">
                            <i class="bx bx-cart"></i>
                            <span key="t-order">Order</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </div>
</div>
