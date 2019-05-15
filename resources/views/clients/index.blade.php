@extends('layouts.master')
@section('heading')

@stop

@section('content')

    <table class="table table-striped" id="clients-table">
        <thead>
        <tr>
            <th>{{ __('Company') }}</th>
            <th>{{ __('Primary Contact') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Number') }}</th>
            <th>{{ __('Actions') }}</th>
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
                {data: 'actions', name: 'actions', orderable: false, searchable: false},

            ]
        });
    });
</script>
@endpush
