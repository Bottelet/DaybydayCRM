@extends('layouts.master')

@section('heading')

<h1>@lang('user.titles.edit')</h1>
@stop

@section('content')


{!! Form::model($user, [
        'method' => 'PATCH',
        'route' => ['users.update', $user->id],
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
{!! Form::select('roles', $roles, $user->userRole->role_id, ['class' => 'form-control']) !!}

{!! Form::label('departments', Lang::get('user.headers.assign_department'), ['class' => 'control-label']) !!}
{!! Form::select('department', $department, $user->departmentOne->first()->id, ['class' => 'form-control']) !!}
</div>

{!! Form::submit(Lang::get('user.headers.update_submit'), ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}

@stop