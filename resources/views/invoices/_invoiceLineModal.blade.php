<div class="modal fade" id="ModalTimer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
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
                'url' => ['tasks/updatetime', $id],
            ]) !!}
            @else
              {!! Form::open([
                'method' => 'post',
                 'route' => ['invoice.new.item', $id],
            ]) !!}
            @endif
            <div class="modal-body">
                <div class="form-group">
                    {!! Form::label('title', __('Title'), ['class' => 'control-label']) !!}
                    {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' =>  __('Insert task title (will be shown on invoice)')]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('comment',  __('Description'), ['class' => 'control-label']) !!}
                    {!! Form::textarea('comment', null, ['class' => 'form-control', 'placeholder' => __('A short description, as to what is being billed')]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('price', __('Hourly price'), ['class' => 'control-label']) !!}
                    {!! Form::text('price', null, ['class' => 'form-control', 'placeholder' => '300']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('quantity', __('Time spend'), ['class' => 'control-label']) !!}
                    {!! Form::text('quantity', null, ['class' => 'form-control', 'placeholder' => '3']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('type', __('Type'), ['class' => 'control-label']) !!}
                    {!! Form::text('type', null, ['class' => 'form-control', 'placeholder' => '3']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default col-lg-6"
                        data-dismiss="modal">{{ __('Close') }}</button>
                <div class="col-lg-6">
                    {!! Form::submit( __('Register time'), ['class' => 'btn btn-success form-control closebtn']) !!}
                </div>
              
            </div>
              {!! Form::close() !!}
        </div>
    </div>
</div>