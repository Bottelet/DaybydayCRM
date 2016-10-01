@extends('layouts.master')
@section('heading')
<h1>@lang('lead.titles.all_leads')</h1>
@stop

@section('content')
  <table class="table table-hover" id="leads-table">
        <thead>
            <tr>
                
                <th>@lang('lead.headers.title')</th>
                <th>@lang('lead.headers.created_by')</th>
                <th>@lang('lead.headers.deadline')</th>
                <th>@lang('lead.headers.assigned')</th>
               
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