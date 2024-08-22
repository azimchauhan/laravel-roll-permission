@extends('admin.layouts.app')

@if (isset($page_title) && $page_title != '')
    @section('title', $page_title . ' | ' . config('app.name'))
@else
    @section('title', config('app.name'))
@endif

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('assets/libs/dropify/dist/css/dropify.min.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.user.store') }}" name="addfrm" id="addfrm" method="POST" enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        <input type="hidden" name="user_id" id="user_id" value="{{ isset($user) ? $user->id : '' }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 controls">
                                    <label class="form-label @error('role_id') is-invalid @enderror">Select Role <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="role_id" id="role_id">
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                            @if (isset($user))
                                                <option value="{{ $role->id }}" {{ (in_array($role->id, $role_ids)) ? 'selected' : '' }}>{{ $role->display_name }}</option>
                                            @else
                                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                            @endif
                                        @endforeach
                                    </select>

                                    @error('role_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('name') is-invalid @enderror">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name', isset($user) ? $user->name : '') }}">

                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('email') is-invalid @enderror">Email Address</label>
                                    <input type="text" class="form-control" name="email" id="email" value="{{ old('email', isset($user) ? $user->email : '') }}">

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3 controls">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch form-switch-md mb-3" dir="ltr">
                                        <input type="checkbox" name="status" class="form-check-input" {{ isset($user) && $user->status === 1 ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 controls">
                                    <label class="form-label @error('password') is-invalid @enderror">
                                        Password
                                        @if (!isset($user))
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    <div class="input-group auth-pass-inputgroup">
                                        <input type="password" class="form-control" name="password" id="password" value="{{ old('password') }}">

                                        <button class="btn btn-light " type="button" id="password-addon">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('password_confirmation') is-invalid @enderror">
                                        Confirm Password
                                        @if (!isset($user))
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" value="{{ old('password_confirmation') }}">

                                    @error('password_confirmation')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @php
                            $image  = (isset($user->image) && $user->image != '' && \File::exists(public_path('uploads/users/'.$user->image))) ? asset('uploads/users/'.$user->image) : '';

                            $imageExits = '';

                            if ($image) {
                                $imageExits = 'image-exist';
                            }
                        @endphp

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('image') is-invalid @enderror">Image <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control dropify {{ $imageExits }}" name="image" id="image" data-default-file="{{ $image }}" data-allowed-file-extensions="gif png jpg jpeg" data-max-file-size="5M" data-show-errors="true" data-errors-position="outside" data-show-remove="false">
                                    @error('image')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary w-md button-responsive">Submit</button>
                            <a href="{{ route('admin.user.index') }}" class="btn btn-secondary w-md button-responsive">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('assets/libs/dropify/dist/js/dropify.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.dropify').dropify();

            $.validator.addMethod("enhancedEmail", function(value, element) {
                return this.optional(element) || /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z]{2,})(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(value);
            }, "Please enter a valid email address.");

            $("#addfrm").validate({
                errorElement: "span",
                errorPlacement: function(label, element) {
                    label.addClass('errorMessage');

                    if (element.attr("type") == "radio" || element.hasClass('select2') || element.attr("name") == "description" || element.attr("name") == "password") {
                        $(element).parents('.controls').append(label)
                    } else if (element.hasClass('dropify')) {
                        label.insertAfter(element.closest('div'));
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
                    role_id: {
                        required: true
                    },
                    name: {
                        required: true
                    },
                    email: {
                        required: true,
                        enhancedEmail: true,
                        remote: {
                            url: '{!! route("admin.user.exists") !!}',
                            type: "POST",
                            data:{
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                id: $("#user_id").val()
                            },
                        }
                    },
                    password: {
                        required: ($('#user_id').val() == '') ? true : false,
                    },
                    password_confirmation: {
                        required: ($('#user_id').val() == '') ? true : false,
                        equalTo: "#password"
                    },
                    image: {
                        required: ($('#user_id').val() == '') ? true : false,
                    }
                },
                messages: {
                    role_id: {
                        required: "The role field is required."
                    },
                    name: {
                        required: "The name field is required."
                    },
                    email: {
                        required: "The email field is required.",
                        remote: "Email already taken."
                    },
                    password: {
                        required: "The password field is required."
                    },
                    password_confirmation: {
                        required: "The password confirmation field is required."
                    },
                    image: {
                        required: "The image field is required."
                    }
                },
            })
        });
    </script>
@endsection
