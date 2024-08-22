<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Login | {{ config('app.name') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}">
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
    </head>

    <body>
        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="bg-primary bg-soft">
                                <div class="row">
                                    <div class="col-7">
                                        <div class="text-primary p-4">
                                            <h5 class="text-primary">Welcome Back !</h5>
                                            <p>Sign in to continue to {{ config('app.name') }}.</p>
                                        </div>
                                    </div>

                                    <div class="col-5 align-self-end">
                                        <img src="{{ asset('assets/images/profile-img.png') }}" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>

                            <div class="card-body pt-0">
                                <div class="auth-logo">
                                    <a href="{{ route('login') }}" class="auth-logo-light">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                <img src="{{ asset('assets/images/logo-light.svg') }}" alt="" class="rounded-circle" height="34">
                                            </span>
                                        </div>
                                    </a>

                                    <a href="{{ route('login') }}" class="auth-logo-dark">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                <img src="{{ asset('assets/img/favicon.ico') }}" alt="" class="rounded-circle" height="34">
                                            </span>
                                        </div>
                                    </a>
                                </div>

                                <div class="p-2">
                                    @if (session('error'))
                                        <div class="alert alert-danger text-center mb-4" role="alert">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input id="email" type="email" class="form-control numbers_only @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required placeholder="Enter Email Address" autocomplete="off" autofocus>

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <div class="input-group auth-pass-inputgroup">
                                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required placeholder="Enter Password"  autocomplete="off">
                                                <button class="btn btn-light" type="button" id="password-addon">
                                                    <i class="mdi mdi-eye-outline"></i>
                                                </button>

                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="remember-check">
                                                Remember me
                                            </label>
                                        </div>

                                        <div class="mt-3 d-grid">
                                            <button class="btn btn-primary waves-effect waves-light" type="submit">Log In</button>
                                        </div>

                                        <div class="mt-4 text-center">
                                            <a href="{{ route('password.request') }}" class="text-muted">
                                                <i class="mdi mdi-lock me-1"></i>
                                                Forgot your password?
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 text-center">
                            <div>
                                <p>Don't have an account ? <a href="{{ route('register') }}" class="fw-medium text-primary"> Signup now </a> </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="{{ asset('assets/js/app.js') }}"></script>
    </body>
</html>
