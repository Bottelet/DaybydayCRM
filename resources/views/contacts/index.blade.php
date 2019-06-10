@extends('layouts.master')
@section('heading')
    <h1>{{__('All Contacts')}}</h1>
@stop

@section('content')
    <table class="table table-striped" id="contacts-table">
        <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Job Title') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Primary Number') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
        </thead>
    </table>
@stop

@push('scripts')
<script>
    $(function () {
        $('#contacts-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('contacts.data') !!}',
            columns: [
                {data: 'namelink', name: 'name'},
                {data: 'job_title', name: 'job_title'},
                {data: 'emaillink', name: 'email'},
                {data: 'primary_number', name: 'primary_number',},
                {data: 'actions', name: 'actions', orderable: false, searchable: false},
            ]
        });
    });
</script>
@endpush
