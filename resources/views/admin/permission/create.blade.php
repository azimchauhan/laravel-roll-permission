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
                    <form action="{{ route('admin.permission.store') }}" name="addfrm" id="addfrm" method="POST" enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        @isset($permission)
                            <input type="hidden" name="permission_id" id="permission_id" value="{{ $permission->id }}">
                        @endisset

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('name') is-invalid @enderror">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name', isset($permission) ? $permission->display_name : '') }}">
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
                                        <input type="checkbox" name="status" class="form-check-input" {{ (isset($permission) && $permission->status === 1) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary w-md button-responsive">Submit</button>
                            <a href="{{ route('admin.permission.index') }}" class="btn btn-secondary w-md button-responsive">Cancel</a>
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

                    if (element.attr("type") == "radio" || element.hasClass('select2') || element.attr("name") == "description" || element.attr("type") == "checkbox") {
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
                    name: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: 'The name field is required.'
                    }
                }
            })
        });
    </script>
@endsection
