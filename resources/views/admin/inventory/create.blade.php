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
                    <form action="{{ route('admin.inventory.store') }}" name="addfrm" id="addfrm" method="POST" enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        @isset($inventory)
                            <input type="hidden" name="inventory_id" id="inventory_id" value="{{ $inventory->id }}">
                        @endisset

                        <div class="row">
                            @if (isSuperAdmin())
                                <div class="col-md-6">
                                    <div class="mb-3 controls">
                                        <label class="form-label @error('user_id') is-invalid @enderror">Select User <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="user_id" id="user_id">
                                            <option value="">Select User</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ (isset($inventory) && $inventory->user_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" id="user_id" value="{{ auth()->id() }}">
                            @endif

                            @php $col = isSuperAdmin() ? 6 : 12; @endphp

                            <div class="col-md-{{ $col }}">
                                <div class="mb-3 controls">
                                    <label class="form-label @error('product_id') is-invalid @enderror">Select Product <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="product_id" id="product_id">
                                        <option value="">Select Product</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('purchase_price') is-invalid @enderror">Purchase Price </label>
                                    <input type="text" class="form-control numbers_only" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', isset($inventory) ? $inventory->purchase_price : '') }}">

                                    @error('purchase_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('selling_price') is-invalid @enderror">Selling Price <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control numbers_only" name="selling_price" id="selling_price" value="{{ old('selling_price', isset($inventory) ? $inventory->selling_price : '') }}">

                                    @error('selling_price')
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
                                    <label class="form-label @error('quantity') is-invalid @enderror">Quantity <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control numbers_only" name="quantity" id="quantity" value="{{ old('quantity', isset($inventory) ? $inventory->quantity : '') }}">

                                    @error('quantity')
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
                                        <input type="checkbox" name="status" class="form-check-input" {{ isset($inventory) && $inventory->status === 1 ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <label class="form-label" for="notes"> Notes </label>
                                <div class="mb-3 controls">
                                    <textarea class="form-control ckeditor" name="notes" id="notes" placeholder="Please enter notes">
                                        {{ old('notes', isset($inventory) ? $inventory->notes : '') }}
                                    </textarea>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary w-md button-responsive">Submit</button>
                            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary w-md button-responsive">Cancel</a>
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
                    user_id: {
                        required: true
                    },
                    product_id: {
                        required: true
                    },
                    // purchase_price: {
                    //     required: true
                    // },
                    selling_price: {
                        required: true
                    },
                    quantity: {
                        required: true
                    },
                },
                messages: {
                    user_id: {
                        required: "The user field is required."
                    },
                    product_id: {
                        required: "The product field is required."
                    },
                    // purchase_price: {
                    //     required: "The purchase price field is required."
                    // },
                    selling_price: {
                        required: "The selling price field is required."
                    },
                    quantity: {
                        required: "The quantity field is required."
                    },
                },
            })
        });

        $('#product_id').select2({
            allowClear: true,
            ajax: {
                url: '{{ route('ajax.get_product') }}',
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
            placeholder: 'Select Product',
        });
    </script>
@endsection
