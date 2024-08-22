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
                    <form action="{{ route('admin.product.store') }}" name="addfrm" id="addfrm" method="POST" enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        <input type="hidden" name="product_id" id="product_id" value="{{ isset($product) ? $product->id : '' }}">

                        @if (isSuperAdmin())
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3 controls">
                                        <label class="form-label @error('user_id') is-invalid @enderror">Select User <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="user_id" id="user_id">
                                            <option value="">Select User</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ ((isset($product) && $product->user_id == $user->id) || old('user_id') == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @else
                            <input type="hidden" id="user_id" value="{{ auth()->id() }}">
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 controls">
                                    <label class="form-label">Select Parent Category <span class="text-danger">*</span></label>
                                    <select class="form-control select2 category" name="parent_category_id" id="parent_category_id">
                                        @if (isset($product) && isset($product->parentCategory) && isset($product->parentCategory))
                                            <option value="{{ $product->parentCategory->id }}" selected>{{ $product->parentCategory->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3 controls">
                                    <label class="form-label @error('category_id') is-invalid @enderror">Select Category </label>
                                    <select class="form-control select2 category" name="category_id" id="category_id">
                                        @if (isset($product) && isset($product->category))
                                            <option value="{{ $product->category->id }}" selected>{{ $product->category->name }}</option>
                                        @endif
                                    </select>

                                    @error('category_id')
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
                                    <label class="form-label @error('name') is-invalid @enderror">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name', isset($product) ? $product->name : '') }}">

                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('purchase_price') is-invalid @enderror">Purchase Price </label>
                                    <input type="text" class="form-control numbers_only" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', isset($product) ? $product->purchase_price : '') }}">

                                    @error('purchase_price')
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
                                    <label class="form-label @error('selling_price') is-invalid @enderror">Selling Price <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control numbers_only" name="selling_price" id="selling_price" value="{{ old('selling_price', isset($product) ? $product->selling_price : '') }}">

                                    @error('selling_price')
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
                                        <input type="checkbox" name="status" class="form-check-input" {{ ((isset($product) && $product->status === 1) || old('status') == 'on') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <label class="form-label" for="details">Details </label>
                                <div class="mb-3 controls">
                                    <textarea class="form-control ckeditor" name="details" id="details" placeholder="Please enter details">
                                        {{ old('details', isset($product) ? $product->details : '') }}
                                    </textarea>

                                    @error('details')
                                        <span class="invalid-feedback" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            @php
                                $image  = (isset($product->image) && $product->image != '' && \File::exists(public_path('uploads/product/'.$product->image))) ? asset('uploads/product/'.$product->image) : '';

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
                        </div>

                        <div id="div_add_more">
                            @if (isset($product) && isset($product->varients) && $product->varients->count() > 0)
                                @foreach ($product->varients as $key => $productVarients)
                                    <div class="row raw_product_varients">
                                        <div class="row">
                                            <input type="hidden" name="productVarientVals[{{ $key }}][varientId]" value="{{ $productVarients->category_varient_id }}">
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Varient Name</label>
                                                    <input type="text" name="productVarientVals[{{ $key }}][name]" class="form-control" id="name_{{ $key }}" data-key="{{ $key }}" value="{{ ($productVarients->categoryVarient) ? $productVarients->categoryVarient->name : '' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label for="price" class="form-label">Varient Price <span class="text-danger">*</span></label>
                                                    <input type="text" name="productVarientVals[{{ $key }}][price]" class="form-control numbers_only" id="price_{{ $key }}" data-key="{{ $key }}" value="{{ ($productVarients->price == 0) ? '-' : $productVarients->price }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row raw_product_varients">

                                </div>
                            @endif
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary w-md button-responsive">Submit</button>
                            <a href="{{ route('admin.product.index') }}" class="btn btn-secondary w-md button-responsive">Cancel</a>
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
                    parent_category_id: {
                        required: true
                    },
                    name: {
                        required: true
                    },
                    // purchase_price: {
                    //     required: true
                    // },
                    selling_price: {
                        required: true
                    },
                    image: {
                        required: ($('#product_id').val() == '') ? true : false,
                    }
                },
                messages: {
                    parent_category_id: {
                        required: "The parent category field is required."
                    },
                    name: {
                        required: "The name field is required."
                    },
                    // purchase_price: {
                    //     required: "The purchase price field is required."
                    // },
                    selling_price: {
                        required: "The selling price field is required."
                    },
                    image: {
                        required: "The image field is required."
                    }
                },
            });
        });

        $('#parent_category_id').select2({
            allowClear: true,
            ajax: {
                url: '{{ route('ajax.get_parent_category') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1,
                        userId: $('#user_id').val()
                    };
                },
            },
            placeholder: 'Select Parent Category',
        });

        $('#parent_category_id').on('change', function(e) {
            let optionSelected = $("option:selected", this);
            $('#category_id').attr('disabled', true);

            if (this.value) {
                $('#category_id').attr('disabled', false);
            }
        });

        $('#category_id').select2({
            allowClear: true,
            ajax: {
                url: '{{ route('ajax.get_sub_category') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1,
                        categoryId: $('#parent_category_id').find(":selected").val()
                    };
                },
            },
            placeholder: 'Select Sub Category',
        });

        $(document).on('change', '.category', function() {
            let categoryId = $(this).val();

            $.ajax({
                type: "POST",
                url: "{{ route('ajax.get_category_varients') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    categoryId: categoryId
                },
                dataType: "json",
                success: function (res) {
                    $('.raw_product_varients').html('');

                    if (res.success) {
                        if (res.categoryVarients.length > 0) {
                            let html = '';

                            $.each(res.categoryVarients, function (i, val) {
                                html += "<div class='row'> <input type='hidden' name='productVarientVals["+i+"][varientId]' value='"+val.id+"'> <div class='col-lg-3'> <div class='mb-3'> <label for='name' class='form-label'>Varient Name</label> <input type='text' name='productVarientVals["+i+"][name]' class='form-control' id='name_"+i+"' data-key='"+i+"' value='"+val.name+"' readonly> </div> </div> <div class='col-lg-3'> <div class='mb-3'> <label for='price' class='form-label'>Varient Price <span class='text-danger'>*</span></label> <input type='text' name='productVarientVals["+i+"][price]' class='form-control numbers_only' id='price_"+i+"' data-key='"+i+"' value=''> </div> </div> </div>";
                            });

                            $('.raw_product_varients').html(html);

                            $('.numbers_only').keyup(function () {
                                this.value = this.value.replace(/[^0-9\.]/g, '');
                            });
                        }
                    } else {
                        Toast.fire("Failed!", res.message, "error");
                    }
                }
            });
        })
    </script>
@endsection
