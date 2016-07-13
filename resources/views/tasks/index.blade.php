@extends('layouts.master')
@section('heading')
<h1>All tasks</h1>
@stop

@section('content')
   <table class="table table-hover" id="tasks-table">
        <thead>
            <tr>
                
                <th>Name</th>
                <th>Created at</th>
                <th>Deadline</th>
                <th>Assigned</th>
               
            </tr>
        </thead>
    </table>
@stop

@push('scripts')
<script>
$(function() {
    $('#tasks-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('tasks.data') !!}',
        columns: [
            
            { data: 'titlelink', name: 'title' },
            { data: 'created_at', name: 'created_at'},
            { data: 'deadline', name: 'deadline' },
            {data: 'fk_user_id_assign', name: 'fk_user_id_assign', },
        
        ]
    });
});
</script>
@endpush