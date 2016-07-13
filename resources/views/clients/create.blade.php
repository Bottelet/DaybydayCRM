@extends('layouts.master')
@section('heading')
<h1>Create Client</h1>
@stop

@section('content')
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); //Tooltip on icons top

$('.popoverOption').each(function() {
    var $this = $(this);
    $this.popover({
      trigger: 'hover',
      placement: 'left',
      container: $this,
      html: true
    });
});
});
</script>

<?php 
$data = Session::get('data');
 ?>

{!! Form::open([
        'url' => '/clients/create/cvrapi'

        ]) !!}
    <div class="form-group">
        <div class="input-group">
         
    {!! Form::text('vat', null, ['class' => 'form-control', 'placeholder' => 'Insert company VAT']) !!}
       <div class="popoverOption input-group-addon"
             rel="popover"
          data-placement="left"
          data-html="true"
          data-original-title="<span>Only for DK, atm.</span>">?</div>
        
    </div>
    {!! Form::submit('Get client info', ['class' => 'btn btn-primary clientvat']) !!}

</div> 

{!!Form::close()!!}

{!! Form::open([
        'route' => 'clients.store',
        'class' => 'ui-form'
        ]) !!}

<div class="form-group">
    {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
    {!! Form::text('name', $data['owners'][0]['name'], ['class' => 'form-control']) !!}
</div>

<div class="form-inline">
    <div class="form-group col-sm-6 removeleft">
        {!! Form::label('vat', 'Vat:', ['class' => 'control-label']) !!}
        {!! Form::text('vat',  $data['vat'], ['class' => 'form-control']) !!}
    </div>  

    <div class="form-group col-sm-6 removeleft removeright">
        {!! Form::label('company_name', 'Company name:', ['class' => 'control-label']) !!}
        {!! Form::text('company_name',  $data['name'], ['class' => 'form-control']) !!}
    </div>  
</div>

<div class="form-group">
    {!! Form::label('email', 'Email:', ['class' => 'control-label']) !!}
    {!! Form::email('email', $data['email'], ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('address', 'Address:', ['class' => 'control-label']) !!}
    {!! Form::text('address', $data['address'], ['class' => 'form-control']) !!}
</div>

<div class="form-inline">
    <div class="form-group col-sm-4 removeleft">
        {!! Form::label('zipcode', 'Zipcode:', ['class' => 'control-label']) !!}
        {!! Form::text('zipcode', $data['zipcode'], ['class' => 'form-control']) !!}
    </div>

    <div class="form-group col-sm-8 removeleft removeright">
        {!! Form::label('city', 'City:', ['class' => 'control-label']) !!}
        {!! Form::text('city', $data['city'], ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-inline">
    <div class="form-group col-sm-6 removeleft">
        {!! Form::label('primary_number', 'Primary Number:', ['class' => 'control-label']) !!}
        {!! Form::text('primary_number',  $data['phone'], ['class' => 'form-control']) !!}
    </div>

    <div class="form-group col-sm-6 removeleft removeright">
        {!! Form::label('secondary_number', 'Secondary Number:', ['class' => 'control-label']) !!}
        {!! Form::text('secondary_number',  null, ['class' => 'form-control']) !!}
    </div>  
</div>
<div class="form-group">

{!! Form::label('company_type', 'Company type:', ['class' => 'control-label']) !!}
{!! Form::text('company_type',  $data['companydesc'], ['class' => 'form-control']) !!}
</div>  
<div class="form-group">
    {!! Form::label('industry', 'Industry:', ['class' => 'control-label']) !!} 
{!! Form::select('industry_id', $industries, null, ['class' => 'form-control ui search selection top right pointing search-select', 'id' => 'search-select']) !!} 
</div>  


<div class="form-group">
{!! Form::label('fk_user_id', 'Assign user:', ['class' => 'control-label']) !!} 
{!! Form::select('fk_user_id', $users, null, ['class' => 'form-control ui search selection top right pointing search-select', 'id' => 'search-select']) !!}

</div> 


{!! Form::submit('Create New Client', ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>

@endif

@stop