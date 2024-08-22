<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>500 Error Page | {{ env('APP_NAME') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}">
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

        <style>
            .theme-bg {
                background-color: #17b67c !important;
                color: white;
            }

            .btn:hover {
                color: white;
            }

            .theme-clr {
                color: #17b67c;
            }
        </style>
    </head>

    <body>
        <div class="account-pages my-5 pt-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mb-5">
                            <h1 class="display-2 fw-medium">5<i class="bx bx-buoy bx-spin theme-clr display-3"></i>0</h1>
                            <h4 class="text-uppercase">Internal Server Error</h4>
                            <div class="mt-5 text-center">
                                <a class="btn theme-bg waves-effect waves-light" href="{{ url()->previous() }}">Back</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-xl-6">
                        <div>
                            <img src="{{ asset('assets/img/error-img.png') }}" alt="" class="img-fluid">
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
