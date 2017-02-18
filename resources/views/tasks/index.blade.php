@extends('layouts.master')
@section('heading')
    <h1>All tasks</h1>
@stop

@section('content')
    <table class="table table-hover" id="tasks-table">
        <thead>
        <tr>

            <th>{{ __('Title') }}</th>
            <th>{{ __('Created at') }}</th>
            <th>{{ __('Deadline') }}</th>
            <th>{{ __('Assigned') }}</th>

        </tr>
        </thead>
    </table>
@stop

@push('scripts')
<script>
    $(function () {
        $('#tasks-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('tasks.data') !!}',
            columns: [

                {data: 'titlelink', name: 'title'},
                {data: 'created_at', name: 'created_at'},
                {data: 'deadline', name: 'deadline'},
                {data: 'user_assigned_id', name: 'user_assigned_id',},

            ]
        });
    });
</script>
@endpush