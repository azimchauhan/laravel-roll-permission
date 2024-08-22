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
                    <form action="{{ route('admin.order.store') }}" name="addfrm" id="addfrm" method="POST" enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        <input type="hidden" name="order_id" id="order_id" value="{{ isset($order) ? $order->id : '' }}">

                        @if (isSuperAdmin())
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3 controls">
                                        <label class="form-label @error('user_id') is-invalid @enderror">Select User <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="user_id" id="user_id">
                                            <option value="">Select User</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ (isset($order) && $order->user_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
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
                                <div class="mb-3">
                                    <label class="form-label @error('customer_name') is-invalid @enderror">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="customer_name" id="customer_name" value="{{ old('customer_name', isset($order) ? $order->customer_name : '') }}">

                                    @error('customer_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label @error('customer_number') is-invalid @enderror">Customer Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control numbers_only" name="customer_number" id="customer_number" value="{{ old('customer_number', isset($order) ? $order->customer_number : '') }}">

                                    @error('customer_number')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div id="div_add_more">
                            @if (isset($order) && isset($order->products) && $order->products->count() > 0)
                                @foreach ($order->products as $key => $orderProduct)
                                    <div class="row raw_bill_product">
                                        <input type="hidden" name="orderProducts[{{ $key }}][inventory_id]" id="inventory_id_{{ $key }}" value="{{ $orderProduct->inventory_id }}">

                                        <div class="col-lg-2">
                                            <div class="mb-3 controls">
                                                <label class="form-label">Select Product <span class="text-danger">*</span></label>
                                                <select class="form-control select2 productIds" name="orderProducts[{{ $key }}][product_id]" id="product_id_{{ $key }}" data-key="{{ $key }}">
                                                    <option value="">Select Product</option>
                                                    @foreach ($inventories as $inventory)
                                                        @if ($inventory->product)
                                                            <option value="{{ $inventory->product->id }}" data-inventoryid="{{ $inventory->id }}" data-sellingprice="{{ $inventory->selling_price }}" data-maxquantity="{{ $inventory->quantity }}" {{ $orderProduct->product_id == $inventory->product->id ? 'selected' : '' }}>{{ $inventory->product->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="mb-3">
                                                <label for="selling_price" class="form-label">Selling Price</label>
                                                <input type="text" name="orderProducts[{{ $key }}][selling_price]" class="form-control numbers_only selling_price" id="selling_price_{{ $key }}" data-key="{{ $key }}" value="{{ $orderProduct->selling_price }}" readonly>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="mb-3">
                                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                                <input type="text" name="orderProducts[{{ $key }}][quantity]" class="form-control numbers_only calculation quantity" id="quantity_{{ $key }}" data-key="{{ $key }}" value="{{ $orderProduct->quantity }}">
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="mb-3">
                                                <label for="total_amount" class="form-label">Total Amount</label>
                                                <input type="text" name="orderProducts[{{ $key }}][total_amount]" class="form-control total_amount" id="total_amount_{{ $key }}" data-key="{{ $key }}" value="{{ $orderProduct->total_amount }}" readonly>
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
                                <div class="row raw_bill_product">
                                    <input type="hidden" name="orderProducts[0][inventory_id]" id="inventory_id_0">

                                    <div class="col-lg-2">
                                        <div class="mb-3 controls">
                                            <label class="form-label">Select Product <span class="text-danger">*</span></label>
                                            <select class="form-control select2 productIds" name="orderProducts[0][product_id]" id="product_id_0" data-key="0">

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="mb-3">
                                            <label for="selling_price" class="form-label">Selling Price</label>
                                            <input type="text" name="orderProducts[0][selling_price]" class="form-control numbers_only selling_price" id="selling_price_0" data-key="0" value="0" readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <input type="text" name="orderProducts[0][quantity]" class="form-control numbers_only calculation quantity" id="quantity_0" data-key="0" data-maxquantity="1" value="1">
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="mb-3">
                                            <label for="total_amount" class="form-label">Total Amount</label>
                                            <input type="text" name="orderProducts[0][total_amount]" class="form-control total_amount" id="total_amount_0" data-key="0" readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-1">
                                        <button type="button" id="btn_add_more" class="btn btn-primary"><i class="bx bx-plus"></i></button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Amount </label>
                                    <input type="text" class="form-control" name="grand_total" id="grand_total" value="{{ old('grand_total', isset($order) ? $order->grand_total : '') }}" readonly>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary w-md button-responsive">Submit</button>
                            <a href="{{ route('admin.order.index') }}" class="btn btn-secondary w-md button-responsive">Cancel</a>
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
                        required: function() {
                            return {{ isSuperAdmin() ? true : false }}
                        }
                    },
                    customer_name: {
                        required: true
                    },
                    customer_number: {
                        required: true
                    },
                },
                messages: {
                    user_id: {
                        required: "The user field is required."
                    },
                    customer_name: {
                        required: "The customer name field is required."
                    },
                    customer_number: {
                        required: "The customer number field is required."
                    },
                },
            });

            $('.productIds').each(function() {
                $(this).rules('add', {
                    required: true,
                    messages: {
                        required: "The product field is required.",
                    }
                });
            });
        });

        function productSelect2() {
            $('.productIds').select2({
                allowClear: true,
                ajax: {
                    url: '{{ route('ajax.get_inventory_products') }}',
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
                templateSelection: function(data) {
                    $(data.element).attr("data-inventoryid", data.inventoryId);
                    $(data.element).attr("data-sellingprice", data.sellingPrice);
                    $(data.element).attr("data-maxquantity", data.maxQuantity);

                    return data.text;
                },
                placeholder: 'Select Product',
            });
        }

        productSelect2();

        $(document).on('change', '#user_id', function() {
            $('#div_add_more').html('');

            $('#div_add_more').append('<div class="row raw_bill_product"> <input type="hidden" name="orderProducts[0][inventory_id]" id="inventory_id_0"> <div class="col-lg-2"> <div class="mb-3 controls"> <label class="form-label">Select Product <span class="text-danger">*</span></label> <select class="form-control select2 productIds" name="orderProducts[0][product_id]" id="product_id_0" data-key="0"> </select> </div> </div> <div class="col-lg-2"> <div class="mb-3"> <label for="selling_price" class="form-label">Selling Price</label> <input type="text" name="orderProducts[0][selling_price]" class="form-control numbers_only selling_price" id="selling_price_0" data-key="0" value="0" readonly> </div> </div> <div class="col-lg-2"> <div class="mb-3"> <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label> <input type="text" name="orderProducts[0][quantity]" class="form-control numbers_only calculation quantity" id="quantity_0" data-key="0" data-maxquantity="1" value="1"> </div> </div> <div class="col-lg-2"> <div class="mb-3"> <label for="total_amount" class="form-label">Total Amount</label> <input type="text" name="orderProducts[0][total_amount]" class="form-control total_amount" id="total_amount_0" data-key="0" readonly> </div> </div> <div class="col-lg-1"> <button type="button" id="btn_add_more" class="btn btn-primary"><i class="bx bx-plus"></i></button> </div> </div>');

            calculation(0);
            productSelect2();
        });

        var i = ($('#order_id').val() != '') ? $('.raw_bill_product').length : 0;

        $(document).on('click', '#btn_add_more', function() {
            ++i;
            $('#div_add_more').append('<div class="row raw_bill_product"> <input type="hidden" name="orderProducts['+i+'][inventory_id]" id="inventory_id_'+i+'"> <div class="col-lg-2"> <div class="mb-3 controls"> <label class="form-label">Select Product <span class="text-danger">*</span></label> <select class="form-control select2 productIds" name="orderProducts['+i+'][product_id]" id="product_id_'+i+'" data-key="'+i+'"> </select> </div> </div> <div class="col-lg-2"> <div class="mb-3"> <label for="selling_price" class="form-label">Selling Price</label> <input type="text" name="orderProducts['+i+'][selling_price]" class="form-control numbers_only selling_price" id="selling_price_'+i+'" data-key="'+i+'" value="0" readonly> </div> </div> <div class="col-lg-2"> <div class="mb-3"> <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label> <input type="text" name="orderProducts['+i+'][quantity]" class="form-control numbers_only calculation quantity" id="quantity_'+i+'" data-key="'+i+'" data-maxquantity="1" value="1"> </div> </div> <div class="col-lg-2"> <div class="mb-3"> <label for="total_amount" class="form-label">Total Amount</label> <input type="text" name="orderProducts['+i+'][total_amount]" class="form-control total_amount" id="total_amount_'+i+'" data-key="'+i+'" readonly> </div> </div> <div class="col-lg-1"> <button type="button" class="btn btn-danger btn_remove"><i class="bx bx-x"></i></button> </div> </div>');

            productSelect2();

            $('.numbers_only').keyup(function () {
                this.value = this.value.replace(/[^0-9\.]/g, '');
            });

            $('.productIds').each(function() {
                $(this).rules('add', {
                    required: true,
                    messages: {
                        required: "The product field is required.",
                    }
                });
            });
        });

        $(document).on('click', '.btn_remove', function() {
            $(this).closest('.row').remove();
            calculation(0);
        });

        $(document).on('change', '.productIds', function() {
            let key = $(this).data('key');
            let selectedProduct = $(this).select2('data')[0];

            if (selectedProduct) {
                let maxQty = Number(selectedProduct.element.dataset.maxquantity);

                $('#inventory_id_'+key).val(selectedProduct.element.dataset.inventoryid);
                $('#selling_price_'+key).val(selectedProduct.element.dataset.sellingprice);
                $('#quantity_'+key).attr('data-maxquantity', maxQty);

                $('#quantity_'+key).rules('add', {
                    required: true,
                    max: maxQty,
                    messages: {
                        required: "The quantity field is required.",
                        max: "The quantity should not greater than "+maxQty+".",
                    }
                });
            }

            calculation(key);
        });

        $(document).on('keyup', '.calculation', function() {
            var key = $(this).data('key');
            calculation(key);
        });

        function calculation(key) {
            let productId = $('#product_id_'+key).val();

            if (!productId) {
                $('#quantity_'+key).val(1);
                $('#selling_price_'+key).val(0);
            }

            let quantity = $('#quantity_'+key).val();
            let selling_price = $('#selling_price_'+key).val();

            total_amount = parseFloat(quantity) * parseFloat(selling_price);
            $('#total_amount_'+key).val(total_amount);

            var grand_total = 0;
            $('.total_amount').each(function() {
                grand_total += Number($(this).val());
            });

            $('#grand_total').val(grand_total);
        }
    </script>
@endsection
