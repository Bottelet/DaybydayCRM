@extends('layouts.master')
@section('heading')
<h1>@lang('user.titles.create')</h1>
@stop

@section('content')
{!! Form::open([
        'route' => 'users.store',
        'files'=>true,
        'enctype' => 'multipart/form-data'

        ]) !!}

<div class="form-group">
    {{ Form::label('image_path', Lang::get('user.headers.image'), ['class' => 'control-label']) }}
    {!! Form::file('image_path',  null, ['class' => 'form-control']) !!}
</div>  

<div class="form-group">
    {!! Form::label('name', Lang::get('user.headers.name'), ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('email', Lang::get('user.headers.mail'), ['class' => 'control-label']) !!}
    {!! Form::email('email', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('address', Lang::get('user.headers.address'), ['class' => 'control-label']) !!}
    {!! Form::text('address', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('work_number', Lang::get('user.headers.work_number'), ['class' => 'control-label']) !!}
    {!! Form::text('work_number',  null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('personal_number', Lang::get('user.headers.personal_number'), ['class' => 'control-label']) !!}
    {!! Form::text('personal_number',  null, ['class' => 'form-control']) !!}
</div>  

<div class="form-group">
    {!! Form::label('password', Lang::get('user.headers.password'), ['class' => 'control-label']) !!}
    {!! Form::password('password', ['class' => 'form-control']) !!}    
</div>

<div class="form-group">
    {!! Form::label('password_confirmation', Lang::get('user.headers.password_confirm'), ['class' => 'control-label']) !!}
    {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}    
</div>

   <div class="form-group form-inline">
{!! Form::label('roles', Lang::get('user.headers.assign_role'), ['class' => 'control-label']) !!}
{!! Form::select('roles', $roles, null, ['class' => 'form-control']) !!}

{!! Form::label('departments', Lang::get('user.headers.assign_department'), ['class' => 'control-label']) !!}
{!! Form::select('departments', $departments, null, ['class' => 'form-control']) !!}
</div>

   <div class="form-group form-inline">

</div>

{!! Form::submit(Lang::get('user.headers.create_submit'), ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}


@stop