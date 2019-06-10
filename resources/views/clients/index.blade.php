@extends('layouts.master')
@section('heading')

@stop

@section('content')

    <table class="table table-striped" id="clients-table">
        <thead>
        <tr>
            <th>{{ __('Company') }}</th>
            <th>{{ __('Primary Number') }}</th>
            <th>{{ __('Primary Email') }}</th>
            <th>{{ __('Salesperson') }}</th>
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

                {data: 'namelink', name: 'name'},
                {data: 'primary_number', name: 'primary_number'},
                {data: 'emaillink', name: 'primary_email'},
                {data: 'salesperson', name: 'salesperson'},
                {data: 'actions', name: 'actions', orderable: false, searchable: false},

            ]
        });
    });
</script>
@endpush
