@extends('layouts.master')
@section('heading')

@stop

@section('content')

    <table class="table table-hover " id="clients-table">
        <thead>
        <tr>
            <th>{{ __('Company') }}</th>
            <th>{{ __('Primary Contact') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Number') }}</th>
            <th></th>
            <th></th>
        </tr>
        </thead>
    </table>

@stop

@push('scripts')
<script>
    $(function () {
        $('#clients-table').DataTable({
            processing: true,
            serverSide: true,

            ajax: '{!! route('clients.data') !!}',
            columns: [

                {data: 'namelink', name: 'company_name'},
                {data: 'primary_contact_name', name: 'primary_contact_name'},
                {data: 'emaillink', name: 'email'},
                {data: 'primary_number', name: 'primary_number'},
                @if(Entrust::can('client-update'))   
                { data: 'edit', name: 'edit', orderable: false, searchable: false},
                @endif
                @if(Entrust::can('client-delete'))   
                { data: 'delete', name: 'delete', orderable: false, searchable: false},
                @endif

            ]
        });
    });
</script>
@endpush
