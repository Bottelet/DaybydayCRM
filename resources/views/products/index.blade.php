@extends('layouts.master')


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="tablet tablet--tabs tablet--height-fluid">
                <div class="tablet__head ">
                    <div class="tablet__head-toolbar">
                        @lang('Products')
                    </div>
                    <div class="tablet__head"  style="padding: 6px 2px;">
                        <button class="btn btn-brand" id="create-product-btn">@lang('New product')</button>
                    </div>
                </div>
                <div class="tablet__body">
                    <table class="table table-hover" id="leads-table">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                        <tbody>
                        @foreach($products as $index => $product)
                            <tr>
                                <td>{{$product->name}}</td>
                                <td>{{formatMoney($product->moneyPrice)}}</td>
                                <td>
                                    <button class="btn edit-product-btn" id="edit-product-btn" data-product_offer_external_id="{{$product->external_id}}"><span class="fa fa-pencil"></span></button>
                                    <button class="btn btn-warning"  data-toggle="modal" data-title="{{$product->name}}" data-target="#deletion" data-id="{{route('products.destroy',$product->external_id)}}"><span class="fa fa-trash"></span></button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="product-creator-modal" tabindex="-1" role="dialog" aria-hidden="true"
    style="display:none;">
    <div class="modal-dialog">
        <div class="modal-content"></div>
    </div>
</div>


<!-- DELETE MODAL SECTION -->
<div id="deletion" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <form method="POST" id="deletion-form">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">@lang('Deletion of ') <span id="deletion-title" style="font-size: 1rem;"></span></h4>
            
            </div>
            <div class="modal-body">
            @lang('Are you sure?')
            @method('delete')
            @csrf
            
            </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Cancel')</button>
                <input type="submit" class="btn btn-brand" value="{{__('Delete')}}">
                </div>
            </form>
        </div>
    </div>
</div>
<!-- END OF THE DELETE MODAL SECTION -->
@endsection

@push('scripts')
<script>
    $('.edit-product-btn').on('click', function () {
        var productExternalId = $(this).data('product_offer_external_id');
        $('#product-creator-modal .modal-content').load('/products/creator/' + productExternalId);
        $('#product-creator-modal').modal('show');
    });
    $('#create-product-btn').on('click', function () {
        var productExternalId = $(this).data('product_offer_external_id');
        $('#product-creator-modal .modal-content').load('/products/creator');
        $('#product-creator-modal').modal('show');
    });
    $( '#deletion' ).on( 'show.bs.modal', function (e) {
            var target = e.relatedTarget;
            var id = $(target).data('id');
            var title = $(target).data('title');
            
            $("#deletion-title").text(title);
            $('#deletion-form').attr('action', id)

        });
</script>
 
@endpush