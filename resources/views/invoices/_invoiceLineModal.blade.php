
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                {{ __('Time management') }}
                    ({{$title}})</h4>

            </div>
            @if($type == 'task')
           {!! Form::open([
                'method' => 'post',
                'url' => ['tasks/updatetime', $external_id],
            ]) !!}
            @else
              {!! Form::open([
                'method' => 'post',
                 'route' => ['invoice.new.item', $external_id],
            ]) !!}
            @endif
            <div class="modal-body">
            @if(isset($products))
                <div class="form-group">
                    {!! Form::label('product_id', __('Billy products'), ['class' => 'control-label']) !!}
                    {!! Form::select('product_id', $products, null, ['class' => 'form-control']) !!}
                </div>
            @endif
                <div class="form-group">
                    {!! Form::label('title', __('Title'), ['class' => 'control-label']) !!}
                    {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' =>  __('Insert task title (will be shown on invoice)')]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('comment',  __('Description'), ['class' => 'control-label']) !!}
                    {!! Form::textarea('comment', null, ['class' => 'form-control', 'placeholder' => __('A short description, as to what is being billed')]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('price', __('Price'), ['class' => 'control-label']) !!}
                    {!! Form::number('price', null, ['class' => 'form-control', 'placeholder' => '300']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('quantity', __('Quantity'), ['class' => 'control-label']) !!}
                    {!! Form::number('quantity', null, ['class' => 'form-control', 'placeholder' => '3']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('type', __('Type'), ['class' => 'control-label']) !!}
                    {!! Form::select('type', [
                        'pieces' => __('pieces'),
                        'hours' =>__('hours'),
                        'days' =>__('days'),
                        'session' =>__('session'),
                        'sqm' =>__('sqm'),
                        'meters' =>__('meters'),
                        'kilometer' =>__('kilometer'),
                        'kg' =>__('kg'),
                        'package' =>__('package'),
                        'boxes' =>__('boxes'),
                    ], null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default col-lg-6"
                        data-dismiss="modal">{{ __('Close') }}</button>
                <div class="col-lg-6">
                    {!! Form::submit( __('Register time'), ['class' => 'btn btn-brand form-control closebtn']) !!}
                </div>
              
            </div>
              {!! Form::close() !!}
