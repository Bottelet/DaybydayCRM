@extends('layouts.master')

@section('content')
    {!! Form::open([
            'route' => 'departments.store',
            ]) !!}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group">
        {!! Form::label( 'name', __('Department name'), ['class' => 'control-label']) !!}
        {!! Form::text('name', null,['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label( 'description', __('Department description'), ['class' => 'control-label']) !!}
        {!! Form::textarea('description', null, ['class' => 'form-control']) !!}
    </div>
    {!! Form::submit(__("Create Department"), ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}

@endsection
