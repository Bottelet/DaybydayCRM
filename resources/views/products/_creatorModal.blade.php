<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">
        {{ __('Product') }} <span style="font-size: 1rem;">{{ $product ? $product->name : ''}}</span></h4>

</div>
<form action="{{route('products.update', [optional($product)->external_id])}}" method="POST">
<div class="modal-body">
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group col-lg-12 removeleft">
                <label for="name" class="thin-weight">@lang('Name')</label>
                <input type="text" name="name" class="form-control input-sm" value="{{ optional($product)->name }}">
            </div>
            <div class="form-group col-lg-12 removeleft" >
                <label for="source" class="thin-weight">@lang('Description')</label>
                <textarea name="description" id="description" class="form-control">{{optional($product)->description}}</textarea>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group col-lg-12 removeleft">
                <label for="type" class="control-label thin-weight">{{trans('Type')}}</label>
                <select name="type"  class="type form-control">
                    <option value="pieces" {{optional($product)->default_type == 'pieces' ? 'selected' : ''}}>{{trans('pieces')}}</option>
                    <option value="hours" {{optional($product)->default_type == 'hours' ? 'selected' : ''}}>{{trans('hours')}}</option>
                    <option value="days" {{optional($product)->default_type == 'days' ? 'selected' : ''}}>{{trans('days')}}</option>
                    <option value="session" {{optional($product)->default_type == 'session' ? 'selected' : ''}}>{{trans('session')}}</option>
                    <option value="sqm" {{optional($product)->default_type == 'sqm' ? 'selected' : ''}}>{{trans('sqm')}}</option>
                    <option value="meters" {{optional($product)->default_type == 'meters' ? 'selected' : ''}}>{{trans('meters')}}</option>
                    <option value="kilometer" {{optional($product)->default_type == 'kilometer' ? 'selected' : ''}}>{{trans('kilometer')}}</option>
                    <option value="kg" {{optional($product)->default_type == 'kg' ? 'selected' : ''}}>{{trans('kg')}}</option>
                    <option value="package" {{optional($product)->default_type == 'package' ? 'selected' : ''}}>{{trans('package')}}</option>
                    <option value="boxes" {{optional($product)->default_type == 'boxes' ? 'selected' : ''}}>{{trans('boxes')}}</option>
                </select>
            </div>
            <div class="form-group col-lg-12 removeleft">
                <label for="price" class="thin-weight">@lang('Price')</label>
                <input type="number" name="price" step=".01" class="form-control" placeholder="300" value="{{ optional($product)->price / 100}}">
            </div>
            <div class="form-group col-lg-12 removeleft">
                <label for="price" class="thin-weight">@lang('Product number')</label>
                <input type="text" name="product_number" class="form-control" value="{{ optional($product)->number}}">
            </div>
        </div>
        {{csrf_field()}}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default col-lg-6"
            data-dismiss="modal">{{ __('Close') }}</button>
    <div class="col-lg-6">
        <input type="submit" value="{{__('Submit')}}" class="btn btn-brand form-control closebtn">
    </div>
</div>
</form>

@push('scripts')
    <script>

        $('#payment_date').pickadate({
            hiddenName:true,
            format: "{{frontendDate()}}",
            formatSubmit: 'yyyy/mm/dd',
            closeOnClear: false,
        });
    </script>
@endpush
