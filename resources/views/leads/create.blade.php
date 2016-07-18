@extends('layouts.master')
@section('heading')
<h1>Create Lead</h1>
@stop

@section('content')

{!! Form::open([
        'route' => 'leads.store'
        ]) !!}

<div class="form-group">
    {!! Form::label('title', 'Title:', ['class' => 'control-label']) !!}
    {!! Form::text('title', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('note', 'Note:', ['class' => 'control-label']) !!}
    {!! Form::textarea('note', null, ['class' => 'form-control']) !!}
</div>

<div class="form-inline">
<div class="form-group col-lg-3 removeleft">
    {!! Form::label('status', 'Status:', ['class' => 'control-label']) !!}
    {!! Form::select('status', array(
    '1' => 'Contact Client', '2' => 'Completed'), null, ['class' => 'form-control'] )
 !!}
 </div>
 <div class="form-group col-lg-4 removeleft">
        {!! Form::label('contact_date', 'Next follow up:', ['class' => 'control-label']) !!}
    {!! Form::date('contact_date', \Carbon\Carbon::now()->addDays(7), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group col-lg-5 removeleft removeright">
      {!! Form::label('contact_time', 'Time:', ['class' => 'control-label']) !!}
     {!! Form::time('contact_time', '11:00', ['class' => 'form-control']) !!}
     </div>

</div>

   
    <div class="form-group">
{!! Form::label('fk_user_id_assign', ' Assign User:', ['class' => 'control-label']) !!}
{!! Form::select('fk_user_id_assign', $users, null, ['class' => 'form-control']) !!}
</div>
 <div class="form-group">
 @if(Request::get('client') != "")
 {!! Form::hidden('fk_client_id', Request::get('client')) !!}
 @else
{!! Form::label('fk_client_id', 'Assign Client:', ['class' => 'control-label']) !!}
{!! Form::select('fk_client_id', $clients, null, ['class' => 'form-control']) !!}
@endif
</div>


  
    

</div>

{!! Form::submit('Create New Task', ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}


@stop