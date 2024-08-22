<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <div class="navbar-brand-box">
                <a href="{{ route('admin.index') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/img/favicon.ico') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/img/favicon.ico') }}" alt="" height="17">
                    </span>
                </a>

                <a href="{{ route('admin.index') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/img/favicon.ico') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/img/favicon.ico') }}" alt="" height="19">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>
        </div>

        <div class="d-flex">

            {{-- <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-magnify"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ..."
                                    aria-label="Recipient's username">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i
                                            class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div> --}}

            @if (isset(Auth::user()->email))
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @if (isset(Auth::user()->image) && \File::exists(public_path('uploads/users/'.Auth::user()->image)))
                            <img src="{{ asset('uploads/users/'.Auth::user()->image) }}" class="rounded-circle header-profile-user login_user_image" alt="Header Avatar">
                        @else
                            <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" class="rounded-circle header-profile-user login_user_image" alt="Header Avatar">
                        @endif
                        <span class="d-none d-xl-inline-block ms-1" key="t-henry" id="header_user_name">{{ Auth::user()->name }}</span>
                        <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                            <i class="bx bx-user font-size-16 align-middle me-1"></i>
                            <span key="t-profile">Profile</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item text-danger" href="javascript:;" onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                            <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i>
                            <span key="t-logout">Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>

                    </div>
                </div>
            @endif
        </div>
    </div>
</header>
