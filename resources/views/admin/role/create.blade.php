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
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.role.store') }}" name="addfrm" id="addfrm" method="POST"
                        enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        @isset($role)
                            <input type="hidden" name="role_id" id="role_id" value="{{ $role->id }}">
                        @endisset

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('name') is-invalid @enderror">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name', isset($role) ? $role->display_name : '') }}">
                                    @error('name')
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
                                        <input type="checkbox" name="status" class="form-check-input"
                                            {{ isset($role) && $role->status === 1 ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (isset($permissions) && count($permissions) > 0)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group @error('permissions') is-invalid @enderror">
                                        <label class="control-label">Permissions <span style="color: red">*</span></label>
                                        <div class="controls">
                                            <div class="row">
                                                @foreach ($permissions as $permission)
                                                    <div class="col-md-3">
                                                        <div class="checkbox checkbox-info">
                                                            <input type="checkbox" name="permissions[]" id="{{ $permission->name }}" value="{{ $permission->name }}" {{ (isset($role) && $role->hasPermissionTo($permission->name)) ? 'checked' : '' }}>
                                                            <label for="{{ $permission->name }}">{{ $permission->display_name }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('permissions')
                                                <span class="invalid-feedback" role="alert">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <button type="submit" class="btn btn-primary w-md button-responsive">Submit</button>
                            <a href="{{ route('admin.role.index') }}" class="btn btn-secondary w-md button-responsive">Cancel</a>
                        </div>
                    </form>
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
            $("#addfrm").validate({
                errorElement: "span",
                errorPlacement: function(label, element) {
                    label.addClass('errorMessage');

                    if (element.attr("type") == "radio" || element.hasClass('select2') || element.attr(
                            "name") == "description" || element.attr("type") == "checkbox") {
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
                    'permissions[]':{
                        required: true,
                        minlength: 1
                    },
                    name: {
                        required: true
                    }
                },
                messages: {
                    'permissions[]':{
                        required:"The permissions field is required.",
                        minlength:"Please select at least 1 permission"
                    },
                    name: {
                        required: 'The name field is required.'
                    }
                }
            })
        });
    </script>
@endsection
