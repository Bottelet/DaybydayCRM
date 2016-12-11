@extends('layouts.master')

@section('content')
    {!! Form::open([
            'route' => 'roles.store',
            ]) !!}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group">
        {!! Form::label('name', Lang::get('role.headers.name'), ['class' => 'control-label']) !!}
        {!! Form::text('name', null,['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('description', Lang::get('role.headers.description'), ['class' => 'control-label']) !!}
        {!! Form::textarea('description', null, ['class' => 'form-control']) !!}
    </div>
    {!! Form::submit(Lang::get('role.headers.add_new'), ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}

@endsection