@extends('layouts.master')

@section('content')

    <div class="row">
        <h3>{{ __('Integrations') }}</h3>
        <div class="col-sm-4">
            <img src="imagesIntegration/dinero-logo.png" width="50%" align="center" alt="">

            {!! Form::open([
               'route' => 'integrations.store'
           ]) !!}
            <div class="form-group">
                {!! Form::label('api_key', __('Api key'), ['class' => 'control-label']) !!}
                {!! Form::text('api_key', null, ['class' => 'form-control']) !!}
            </div>


            <div class="form-group">
                {!! Form::label('org_id',  __('Organization id'), ['class' => 'control-label']) !!}
                {!! Form::text('org_id', null, ['class' => 'form-control']) !!}
            </div>


            {!! Form::hidden('name', 'Dinero') !!}
            {!! Form::hidden('api_type', 'billing') !!}

            {!! Form::submit(__('Update'), ['class' => 'btn btn-primary']) !!}

            {!! Form::close() !!}
        </div>

        <div class="col-sm-4">

            <img src="imagesIntegration/billy-logo-final_blue.png" width="50%" align="center" alt="">
            {!! Form::open([

           ]) !!}
            <div class="form-group">
                {!! Form::label('api_key', __('Api key'), ['class' => 'control-label']) !!}
                {!! Form::text('api_key', null, ['class' => 'form-control']) !!}
            </div>


            {!! Form::hidden('name', 'Billy') !!}
            {!! Form::hidden('api_type', 'billing') !!}
            {!! Form::submit(__('Update'), ['class' => 'btn btn-primary']) !!}

            {!! Form::close() !!}
        </div>
    </div>


@stop