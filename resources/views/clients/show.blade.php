@extends('layouts.master')

@section('heading')
@stop

@push('scripts')
    <script>
        $('#myTabs a').click(function (e) {
            e.preventDefault()
            $(this).tab('show')
        })
    </script>
@endpush

@section('content')
    <div class="row">
        @include('partials.clientheader')
        @include('partials.userheader')
    </div>
    <div class="row">
        <div class="col-md-8">
            <ul class="nav nav-tabs" id="myTabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#contact" role="tab" data-toggle="tab" aria-controls="contact">{{__('Contacts')}}</a>
                </li>
                <li role="presentation">
                    <a href="#task" role="tab" data-toggle="tab" aria-controls="task">{{__('Tasks')}}</a>
                </li>
                <li role="presentation">
                    <a href="#lead" role="tab" data-toggle="tab" aria-controls="lead">{{__('Leads')}}</a>
                </li>
                <li role="presentation">
                    <a href="#document" role="tab" data-toggle="tab" aria-controls="document">{{__('Documents')}}</a>
                </li>
                <li role="presentation">
                    <a href="#invoice" role="tab" data-toggle="tab" aria-controls="invoice">{{__('Invoices')}}</a>
                </li>
            </ul>
            <div class="tab-content">
                @include('clients.tabs.contacttab')
                @include('clients.tabs.tasktab')
                @include('clients.tabs.leadtab')
                @include('clients.tabs.documenttab')
                @include('clients.tabs.invoicetab')
            </div>
        </div>
        <div class="col-md-4">
            {!! Form::model($client, ['method' => 'PATCH', 'url' => ['clients/updateassign', $client->id] ]) !!}
                {!! Form::select('user_assigned_id', $users, $client->user->id, ['class' => 'form-control ui search selection top right pointing search-select', 'id' => 'search-select']) !!}
                {!! Form::submit(__('Assign new user'), ['class' => 'btn btn-primary form-control closebtn']) !!}
            {!! Form::close() !!}
        </div>
    </div>
    </div>
    </div>
@stop
