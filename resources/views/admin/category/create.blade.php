@extends('admin.layouts.app')

@if (isset($page_title) && $page_title != '')
    @section('title', $page_title . ' | ' . config('app.name'))
@else
    @section('title', config('app.name'))
@endif

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('assets/libs/dropify/dist/css/dropify.min.css') }}">

    <style>
        #btn_add_more, #btn_edit_more, .btn_remove {
            margin-top: 26px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.category.store') }}" name="addfrm" id="addfrm" method="POST" enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        <input type="hidden" name="category_id" id="category_id" value="{{ isset($category) ? $category->id : '' }}">

                        @if (isSuperAdmin())
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3 controls">
                                        <label class="form-label @error('user_id') is-invalid @enderror">Select User <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="user_id" id="user_id">
                                            <option value="">Select User</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ (isset($category) && $category->user_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 controls">
                                    <label class="form-label">Select Parent Category</label>
                                    <select class="form-control select2" name="parent_id" id="parent_id">
                                        <option value="">Select Parent Category</option>
                                        @foreach ($parent_categories as $parent_category)
                                            <option value="{{ $parent_category->id }}" {{ (isset($category) && ($category->parent_id == $parent_category->id)) ? 'selected' : '' }}>{{ $parent_category->name }}</option>
                                        @endforeach
                                    </select>

                                    @error('parent_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('name') is-invalid @enderror">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name', isset($category) ? $category->name : '') }}">

                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <label class="form-label" for="details">Details </label>
                                <div class="mb-3 controls">
                                    <textarea class="form-control ckeditor" name="details" id="details" placeholder="Please enter details">
                                        {{ old('details', isset($category) ? $category->details : '') }}
                                    </textarea>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="notes">Notes </label>
                                <div class="mb-3 controls">
                                    <textarea class="form-control ckeditor" name="notes" id="notes" placeholder="Please enter notes">
                                        {{ old('notes', isset($category) ? $category->notes : '') }}
                                    </textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @php
                                $image  = (isset($category->image) && $category->image != '' && \File::exists(public_path('uploads/category/'.$category->image))) ? asset('uploads/category/'.$category->image) : '';

                                $imageExits = '';

                                if ($image) {
                                    $imageExits = 'image-exist';
                                }
                            @endphp

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

                            <div class="col-md-6">
                                <div class="mb-3 controls">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch form-switch-md mb-3" dir="ltr">
                                        <input type="checkbox" name="status" class="form-check-input" {{ isset($category) && $category->status === 1 ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="div_add_more">
                            @if (isset($category) && isset($category->varients) && $category->varients->count() > 0)
                                @foreach ($category->varients as $key => $categoryVarients)
                                    <div class="row raw_category_varients">
                                        <div class="col-lg-4">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Varient Name</label>
                                                <input type="text" name="categoryVarients[{{ $key }}][name]" class="form-control" id="name_0" data-key="{{ $key }}" value="{{ $categoryVarients->name }}">
                                            </div>
                                        </div>

                                        <div class="col-lg-1">
                                            @if ($key == 0)
                                                <button type="button" id="btn_add_more" class="btn btn-primary"><i class="bx bx-plus"></i></button>
                                            @else
                                                <button type="button" class="btn btn-danger btn_remove"><i class="bx bx-x"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row raw_category_varients">
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Varient Name</label>
                                            <input type="text" name="categoryVarients[0][name]" class="form-control" id="name_0" data-key="0" value="">
                                        </div>
                                    </div>

                                    <div class="col-lg-1">
                                        <button type="button" id="btn_add_more" class="btn btn-primary"><i class="bx bx-plus"></i></button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary w-md button-responsive">Submit</button>
                            <a href="{{ route('admin.category.index') }}" class="btn btn-secondary w-md button-responsive">Cancel</a>
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

                    if (element.attr("type") == "radio" || element.hasClass('select2') || element.hasClass("ckeditor") || element.attr("name") == "password") {
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
                    },
                    image: {
                        required: ($('#category_id').val() == '') ? true : false,
                    }
                },
                messages: {
                    name: {
                        required: "The name field is required."
                    },
                    image: {
                        required: "The image field is required."
                    }
                },
            })
        });

        var i = ($('#category_id').val() != '') ? $('.raw_category_varients').length : 0;

        $(document).on('click', '#btn_add_more', function() {
            ++i;

            $('#div_add_more').append('<div class="row raw_category_varients"> <div class="col-lg-4"> <div class="mb-3"> <label for="name" class="form-label">Varient Name</label> <input type="text" name="categoryVarients['+i+'][name]" class="form-control" id="name_'+i+'" data-key="'+i+'" value="" /> </div> </div> <div class="col-lg-1"> <button type="button" class="btn btn-danger btn_remove"> <i class="bx bx-x"></i> </button> </div> </div>');
        });

        $(document).on('click', '.btn_remove', function() {
            $(this).closest('.row').remove();
        });
    </script>
@endsection
