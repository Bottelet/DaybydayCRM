@extends('layouts.master')
@section('heading')
Edit Client ({{$client->name}})
@stop

@section('content')
{!! Form::model($client, [
        'method' => 'PATCH',
        'route' => ['clients.update', $client->id],
        ]) !!}

<div class="form-group">
    {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('vat', 'Vat:', ['class' => 'control-label']) !!}
    {!! Form::text('vat',  null, ['class' => 'form-control']) !!}
</div>  

<div class="form-group">
    {!! Form::label('company_name', 'Company name:', ['class' => 'control-label']) !!}
    {!! Form::text('company_name',  null, ['class' => 'form-control']) !!}
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
    {!! Form::label('zipcode', 'Zipcode:', ['class' => 'control-label']) !!}
    {!! Form::text('zipcode', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('city', 'City:', ['class' => 'control-label']) !!}
    {!! Form::text('city', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('primary_number', 'Primary Number:', ['class' => 'control-label']) !!}
    {!! Form::text('primary_number',  null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('secondary_number', 'Secondary Number:', ['class' => 'control-label']) !!}
    {!! Form::text('secondary_number',  null, ['class' => 'form-control']) !!}
</div>  

<div class="form-group">
    {!! Form::label('industry', 'Industry:', ['class' => 'control-label']) !!} 
{!! Form::select('industry_id', $industries, null, ['class' => 'form-control ui search selection top right pointing search-select', 'id' => 'search-select']) !!} 
</div>  


<div class="form-group">
    {!! Form::label('company_type', 'Company type:', ['class' => 'control-label']) !!}
    {!! Form::text('company_type',  null, ['class' => 'form-control']) !!}
</div>  
{!! Form::label('fk_user_id', 'Assign user:', ['class' => 'control-label']) !!} 
{!! Form::select('fk_user_id', $users, null, ['class' => 'form-control']) !!}<br />

{!! Form::submit('Update Client', ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}

@stop