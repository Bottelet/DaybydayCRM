@extends('layouts.master')
@section('heading')
<h1>All tasks</h1>
@stop

@section('content')
   <table class="table table-hover" id="leads-table">
        <thead>
            <tr>
                
                <th>Name</th>
                <th>Created by</th>
                <th>Deadline</th>
                <th>Assigned</th>
               
            </tr>
        </thead>
    </table>
@stop

@push('scripts')
<script>
$(function() {
    $('#leads-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('leads.data') !!}',
        columns: [
            
            { data: 'titlelink', name: 'title' },
            { data: 'fk_user_id_created', name: 'fk_user_id_created'},
             {data: 'contact_date', name: 'contact_date', },
            { data: 'fk_user_id_assign', name: 'fk_user_id_assign' },
           
        
        ]
    });
});
</script>
@endpush