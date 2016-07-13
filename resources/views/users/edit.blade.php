@extends('layouts.master')

@section('heading')

<h1>Edting</h1>
@stop

@section('content')


{!! Form::model($user, [
        'method' => 'PATCH',
        'route' => ['users.update', $user->id],
        'files'=>true,
        'enctype' => 'multipart/form-data'
        ]) !!}
        
<div class="form-group">
    {!! Form::label('image_path', 'Choose an image:', ['class' => 'control-label']) !!}
    {!! Form::file('image_path',  null, ['class' => 'form-control']) !!}
</div>  


<div class="form-group">
    {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('email', 'Email:', ['class' => 'control-label']) !!}
    {!! Form::email('email', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('address', 'Address:', ['class' => 'control-label']) !!}
    {!! Form::text('address', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('work_number', 'Work Number:', ['class' => 'control-label']) !!}
    {!! Form::text('work_number',  null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('personal_number', 'Personal Number:', ['class' => 'control-label']) !!}
    {!! Form::text('personal_number',  null, ['class' => 'form-control']) !!}
</div>  

<div class="form-group">
    {!! Form::label('password', 'Password:', ['class' => 'control-label']) !!}
    {!! Form::password('password', ['class' => 'form-control']) !!}    
</div>
<div class="form-group">
    {!! Form::label('password_confirmation', 'Password Confirmation:', ['class' => 'control-label']) !!}
    {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}    
</div>
   <div class="form-group form-inline">
{!! Form::label('roles', ' Assign Role:', ['class' => 'control-label']) !!}
{!! Form::select('roles', $roles, $user->userRole->role_id, ['class' => 'form-control']) !!}

{!! Form::label('department', ' Assign Department:', ['class' => 'control-label']) !!}
{!! Form::select('department', $department, $user->departmentOne->first()->id, ['class' => 'form-control']) !!}
</div>

{!! Form::submit('Update User', ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}

@stop