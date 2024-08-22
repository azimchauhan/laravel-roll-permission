@extends('admin.layouts.app')
@if (isset($page_title) && $page_title != '')
    @section('title', $page_title . ' | ' . config('app.name'))
@else
    @section('title', config('app.name'))
@endif
@section('content')
    <div class="row">
        <div class="col-xl-4">
            <div class="card overflow-hidden">
                <div class="card-body" style="min-height: 385px;">
                    @if (isset(Auth::user()->image) && \File::exists(public_path('uploads/users/'.Auth::user()->image)))
                        <img src="{{ asset('uploads/users/'.Auth::user()->image) }}" alt="" class="img-thumbnail rounded-circle login_user_image">
                    @else
                        <img src="{{ asset('assets-skote/images/users/avatar-1.jpg') }}" alt="" class="img-thumbnail rounded-circle login_user_image">
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#profiletab" role="tab">
                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                <span class="d-none d-sm-block">Profile</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#settings" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                <span class="d-none d-sm-block">Settings</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <div class="tab-pane" id="settings" role="tabpanel">
                            <form method="POST" name="passwordform" id="passwordform" enctype="multipart/form-data" autocomplete="off">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label @error('current_password') is-invalid @enderror">Current Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="current_password" id="current_password" placeholder="Current Password">
                                            @error('current_password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label @error('password') is-invalid @enderror">New Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="password" id="password" placeholder="New Password">
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label @error('password_confirmation') is-invalid @enderror">Confirm Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Confirm Password">
                                            @error('password_confirmation')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary w-md" id="btnPasswordSubmit">Submit</button>
                                    <a href="{{ route('admin.index') }}" class="btn btn-secondary w-md">Cancel</a>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane active" id="profiletab" role="tabpanel">
                            <form method="POST" name="profileform" id="profileform" enctype="multipart/form-data" autocomplete="off">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label @error('name') is-invalid @enderror">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" id="name" value="{{ old('name', Auth::user()->name) }}">
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label @error('email') is-invalid @enderror">Email <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="email" id="email" value="{{ old('email', Auth::user()->email) }}">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label @error('image') is-invalid @enderror">Image <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" name="image" id="image">
                                            @error('image')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary w-md" id="btnProfileSubmit">Submit</button>
                                    <a href="{{ route('admin.index') }}" class="btn btn-secondary w-md">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    <script src="{{ asset('assets/libs/validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/libs/validate/additional-methods.js') }}"></script>
    <script>
        $(document).ready(function() {
            $.validator.addMethod('filesize', function (value, element, param) {
                return this.optional(element) || (element.files[0].size <= param * 1000000)
            }, 'File size must be less than {0} MB');

            $("#profileform").validate({
                errorElement: "span",
                errorPlacement: function(label, element) {
                    label.addClass('errorMessage');
                    if(element.attr("type") == "radio") {
                        $(element).parents('.controls').append(label)
                    } else {
                        label.insertAfter(element);
                    }
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).closest('.form-group').addClass(errorClass).removeClass(validClass);
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).closest('.form-group').removeClass(errorClass).addClass(validClass)
                },
                ignore: [],
                rules: {
                    name: {
                        required: true,
                    },
                    email: {
                        required: true,
                        maxlength: 50,
                        email: true,
                    },
                    image: {
                        required: true,
                        extension: "jpg,jpeg,png,gif",
                        filesize: 2,
                    },
                },
                messages: {
                    name: {
                        required: "Please enter name",
                    },
                    email: {
                        required: "Please enter email",
                        email: "Please enter valid email",
                        maxlength: "The email name should less than or equal to 50 characters",
                    },
                    image: {
                        required: "Please select an image"
                    }
                },
                submitHandler: function (form) {
                    var formData = new FormData(form);
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $("#btnProfileSubmit").attr("disabled", true);
                    $.ajax({
                        url: '{{ route('admin.profile.update') }}',
                        type: "POST",
                        data: formData,
                        cache: false,
                        processData: false,
                        dataType: "json",
                        contentType: false,
                        success: function (data) {
                            $("#btnProfileSubmit").attr("disabled", false);
                            if (data.success == true) {
                                form.reset();
                                $('#name').val(data.name);
                                $('#email').val(data.email);
                                $('#header_user_name').html(data.name);
                                $('.login_user_image').attr('src', data.image);
                                setTimeout(function() {
                                    Toast.fire("Success!", data.message, "success");
                                }, 2000);
                            } else {
                                if (data.error) {
                                    $.each(data.error, function(index, value) {
                                        $.each(value, function(ind, val) {
                                            $("#"+index).after("<span id='"+index+"-error' class='error errorMessage'>"+val+'</span>');
                                        });
                                    });
                                } else {
                                    setTimeout(function() {
                                        Toast.fire("Failed!", data.message, "error");
                                    }, 2000);
                                }
                            }
                        }
                    });
                }
            });

            $("#passwordform").validate({
                errorElement: "span",
                errorPlacement: function(label, element) {
                    label.addClass('errorMessage');
                    if(element.attr("type") == "radio") {
                        $(element).parents('.controls').append(label)
                    } else {
                        label.insertAfter(element);
                    }
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).closest('.form-group').addClass(errorClass).removeClass(validClass);
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).closest('.form-group').removeClass(errorClass).addClass(validClass)
                },
                ignore: [],
                rules: {
                    current_password: {
                        required: true,
                        remote: {
                            type: 'POST',
                            url: '{{ route('admin.profile.check.password') }}',
                        }
                    },
                    password: {
                        required: true,
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#password"
                    },
                },
                messages: {
                    current_password: {
                        required: "Please enter current password",
                        remote: "Please enter valid current password",
                    },
                    password: {
                        required: "Please enter new password",
                    },
                    password_confirmation: {
                        required: "Please enter confirm password",
                        equalTo: "confirm password must be same as new password",
                    },
                },
                submitHandler: function (form) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $("#btnPasswordSubmit").attr("disabled", true);
                    $.ajax({
                        url: '{{ route('admin.profile.password.update') }}',
                        type: "POST",
                        data: $('#passwordform').serialize(),
                        dataType: 'json',
                        success: function (data) {
                            $("#btnPasswordSubmit").attr("disabled", false);
                            if (data.success == true) {
                                $('#current_password').val('');
                                $('#password').val('');
                                $('#password_confirmation').val('');

                                setTimeout(function() {
                                    Toast.fire("Success!", data.message, "success");
                                }, 2000);
                            } else {
                                if (data.error) {
                                    $.each(data.error, function(index, value) {
                                        $.each(value, function(ind, val) {
                                            $("#"+index).after("<span id='"+index+"-error' class='error errorMessage'>"+val+'</span>');
                                        });
                                    });
                                } else {
                                    setTimeout(function() {
                                        Toast.fire("Failed!", data.message, "error");
                                    }, 2000);
                                }
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection



