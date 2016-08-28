@extends('layouts.master')
@section('heading')

@stop

@section('content')

   <table class="table table-hover " id="clients-table">
        <thead>
            <tr>
                
                <th>Name</th>
                <th>Company</th>
                <th>Email</th>
                <th>Number</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
    </table>

@stop

@push('scripts')
<script>
$(function() {
    $('#clients-table').DataTable({
        processing: true,
        serverSide: true,
       
        ajax: '{!! route('clients.data') !!}',
        columns: [
            
            { data: 'namelink', name: 'name' },
            { data: 'company_name', name: 'company_name' },
            { data: 'email', name: 'email' },
            { data: 'primary_number', name: 'primary_number' },
          
             { data: 'edit', name: 'edit', orderable: false, searchable: false},
        
            
             { data: 'delete', name: 'delete', orderable: false, searchable: false},
         
           
        ]
    });
});
</script>
@endpush
