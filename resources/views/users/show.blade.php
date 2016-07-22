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
   <h3>Open Tasks</h3>
        <thead>
            <tr>
                
                <th>Name</th>
                <th>Created at</th>
                <th>Deadline</th>
                
            </tr>
        </thead>
    </table>

             
          </div>
  <!-- *********************************************************************
     *                     Open task end, Closed task start       
     *********************************************************************-->
          <div class="col-lg-6 currenttask">
          

   <table class="table table-hover" id="closedtask-table">
   <h3>Closed Tasks</h3>
        <thead>
            <tr>
                
                <th>Name</th>
                <th>Created at</th>
                <th>Deadline</th>
                
            </tr>
        </thead>
    </table>

          </div>
  <!-- *********************************************************************
     *               Closed task end assigned clients start    
     *********************************************************************-->


          <div class="col-lg-8 currenttask">
          


              
        
<table class="table table-hover" id="clients-table">
   <h3>Assigned Clients</h3>
        <thead>
            <tr>
                
                <th>Name</th>
                <th>Company</th>
                <th>Number</th>
              
                
            </tr>
        </thead>
    </table>
   </div>
   <!-- *********************************************************************
 *               assigned clients end, Last 10 created task start    
 *********************************************************************-->

                <div class="col-lg-4 currenttask">
          
                    <table class="table table-hover">
         <h3>Last 10 created tasks</h3>
            <thead>
    <thead>
      <tr>
        <th>Title</th>
        <th>Created at</th>
        <th>Deadline</th> 
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
