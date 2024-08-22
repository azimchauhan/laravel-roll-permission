<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>Verify Email | {{ config('app.name') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Themesbrand" name="author" />
        <link rel="shortcut icon" href="{{ asset('aassets/img/favicon.ico') }}">
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
    </head>

    <body>
        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mb-5 text-muted">
                            <a href="{{ route('login') }}" class="d-block auth-logo">
                                <img src="{{ asset('assets/images/logo.svg') }}" alt="" height="20" class="auth-logo-dark mx-auto">
                                <img src="{{ asset('assets/images/logo.svg') }}" alt="" height="20" class="auth-logo-light mx-auto">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card">
                            <div class="card-body">
                                <div class="p-2">
                                    <div class="text-center">
                                        <div class="avatar-md mx-auto">
                                            <div class="avatar-title rounded-circle bg-light">
                                                <i class="bx bxs-envelope h1 mb-0 text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="p-2 mt-4">
                                            <h4>Before proceeding, please check your email for a verification link.</h4>
                                            <p>If you did not receive the email</p>
                                            <div class="mt-4">
                                                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-md">Verify email</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
