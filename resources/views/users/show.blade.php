@extends('layouts.master')
@section('heading')
<script>$('#pagination a').on('click', function(e){
    e.preventDefault();
    var url = $('#search').attr('action')+'?page='+page;
    $.post(url, $('#search').serialize(), function(data){
        $('#posts').html(data);
    });
});</script>
@stop

@section('content')
@include('partials.userheader')
  <!-- *********************************************************************
     *                 Header end and top task start                   
     *********************************************************************-->
      <div class="row">
          <div class="col-lg-6 currenttask">
          

   <table class="table table-hover" id="opentask-table">
   <h3>@lang('task.status.open')</h3>
        <thead>
            <tr>
                
                <th>@lang('task.headers.title')</th>
                <th>@lang('task.headers.created_at')</th>
                <th>@lang('task.headers.deadline')</th>
                
            </tr>
        </thead>
    </table>

             
          </div>
  <!-- *********************************************************************
     *                     Open task end, Closed task start       
     *********************************************************************-->
          <div class="col-lg-6 currenttask">
          

   <table class="table table-hover" id="closedtask-table">
   <h3>@lang('task.status.closed')</h3>
        <thead>
            <tr>
                
                <th>@lang('task.headers.title')</th>
                <th>@lang('task.headers.created_at')</th>
                <th>@lang('task.headers.deadline')</th>
                
            </tr>
        </thead>
    </table>

          </div>
  <!-- *********************************************************************
     *               Closed task end assigned clients start    
     *********************************************************************-->


          <div class="col-lg-8 currenttask">
          


              
        
<table class="table table-hover" id="clients-table">
   <h3>@lang('client.status.assigned')</h3>
        <thead>
            <tr>
                
                <th>@lang('client.headers.name')</th>
                <th>@lang('client.headers.company')</th>
                <th>@lang('client.headers.primary_number')</th>
              
                
            </tr>
        </thead>
    </table>
   </div>
   <!-- *********************************************************************
 *               assigned clients end, Last 10 created task start    
 *********************************************************************-->

                <div class="col-lg-4 currenttask">
          
                    <table class="table table-hover">
         <h3>@lang('task.status.created')</h3>
            <thead>
    <thead>
      <tr>
        <th>@lang('task.headers.title')</th>
        <th>@lang('task.headers.created_at')</th>
        <th>@lang('task.headers.deadline')</th> 
      </tr>
    </thead>
    <tbody>

@foreach($user->tasksCreated as $task)

       <tr>
<td>
<a href="{{ route('tasks.show', $task->id)}}">
{{ $task->title }}
</a> </td>
<td>{{date('d, M Y', strTotime($task->created_at))}} </td>
<td>{{date('d, M Y', strTotime($task->deadline))}}</td>
</tr>
@endforeach
              </tbody>
              </table>

          </div>
         

@stop
@push('scripts')
<script>
$(function() {
    $('#opentask-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('users.taskdata', ['id' => $user->id]) !!}',
        columns: [
            
            { data: 'titlelink', name: 'title' },
            { data: 'created_at', name: 'created_at' },
            { data: 'deadline', name: 'deadline' },
        ]
    });
});
</script>

<script>
$(function() {
    $('#clients-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('users.clientdata', ['id' => $user->id]) !!}',
        columns: [
            
            { data: 'clientlink', name: 'name' },
            { data: 'company_name', name: 'company_name' },
             { data: 'primary_number', name: 'primary_number' },

        ]
    });
});
</script>
<script>
$(function() {
    $('#closedtask-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('users.closedtaskdata', ['id' => $user->id]) !!}',
        columns: [
            
            { data: 'titlelink', name: 'title' },
            { data: 'created_at', name: 'created_at' },
            { data: 'deadline', name: 'deadline' },
        ]
    });
});
</script>
@endpush
