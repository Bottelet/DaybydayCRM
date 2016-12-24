@extends('layouts.master')
@section('heading')
    <h1>@lang('lead.titles.create')</h1>
@stop

@section('content')

    {!! Form::open([
            'route' => 'leads.store'
            ]) !!}

    <div class="form-group">
        {!! Form::label('title', Lang::get('lead.headers.title'), ['class' => 'control-label']) !!}
        {!! Form::text('title', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('note', lang::get('lead.headers.note'), ['class' => 'control-label']) !!}
        {!! Form::textarea('note', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-inline">
        <div class="form-group col-lg-3 removeleft">
            {!! Form::label('status', lang::get('lead.headers.status'), ['class' => 'control-label']) !!}
            {!! Form::select('status', array(
            '1' => 'Contact Client', '2' => 'Completed'), null, ['class' => 'form-control'] )
         !!}
        </div>
        <div class="form-group col-lg-4 removeleft">
            {!! Form::label('contact_date', lang::get('lead.headers.deadline'), ['class' => 'control-label']) !!}
            {!! Form::date('contact_date', \Carbon\Carbon::now()->addDays(7), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-lg-5 removeleft removeright">
            {!! Form::label('contact_time', lang::get('lead.headers.time'), ['class' => 'control-label']) !!}
            {!! Form::time('contact_time', '11:00', ['class' => 'form-control']) !!}
        </div>

    </div>


    <div class="form-group">
        {!! Form::label('fk_user_id_assign', lang::get('lead.headers.assign_user'), ['class' => 'control-label']) !!}
        {!! Form::select('fk_user_id_assign', $users, null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        @if(Request::get('client') != "")
            {!! Form::hidden('fk_client_id', Request::get('client')) !!}
        @else
            {!! Form::label('fk_client_id', lang::get('lead.headers.assign_client'), ['class' => 'control-label']) !!}
            {!! Form::select('fk_client_id', $clients, null, ['class' => 'form-control']) !!}
        @endif
    </div>

    {!! Form::submit(lang::get('lead.titles.create'), ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}


@stop