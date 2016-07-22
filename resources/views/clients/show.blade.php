@extends('layouts.master')

@section('heading')

@stop

@section('content')
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); //Tooltip on icons top

$('.popoverOption').each(function() {
    var $this = $(this);
    $this.popover({
      trigger: 'hover',
      placement: 'left',
      container: $this,
      html: true,
      content: $this.find('#popover_content_wrapper').html()  
    });
});
});
</script>

<div class="row">
@include('partials.clientheader')
@include('partials.userheader')


</div>
<div class="row">

<div class="col-md-8 currenttask">

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#home">Tasks</a></li>
    <li><a data-toggle="tab" href="#menu1">Leads</a></li>
    <li><a data-toggle="tab" href="#menu2">Documents</a></li>
  </ul>

  <div class="tab-content">
    <div id="home" class="tab-pane fade in active">

<div class="boxspace">
<table class="table table-hover">
  <h4>All Tasks</h4>
  <thead>
    <thead>
      <tr>
        <th>Title</th>
        <th>Assigned user</th>
        <th>Created date</th>
        <th>Deadline</th>
        <th><a href="{{ route('tasks.create', ['client' => $client->id])}}"><button class="btn btn-success">Add new task</button> </a></th>

        
      </tr>
    </thead>
    <tbody>
      <?php  $tr =""; ?>
      @foreach($client->alltasks as $task)
      @if($task->status == 1)
        <?php  $tr = '#adebad'; ?>
      @elseif($task->status == 2)
        <?php $tr = '#ff6666'; ?>
      @endif
      <tr style="background-color:<?php echo $tr ;?>">
        
        <td > <a href="{{ route('tasks.show', $task->id) }}">{{$task->title}} </a></td>
        <td > <div class="popoverOption"
          rel="popover"
          data-placement="left"
          data-html="true"
          data-original-title="<span class='glyphicon glyphicon-user' aria-hidden='true'> </span> {{$task->assignee->name}}">
          <div id="popover_content_wrapper" style="display:none; width:250px;">
            <img src='http://placehold.it/350x150' height='80px' width='80px' style="float:left; margin-bottom:5px;"/>
            <p class="popovertext">
              <span class="glyphicon glyphicon-envelope" aria-hidden="true"> </span>
              <a href="mailto:{{$task->assignee->email}}">
              {{$task->assignee->email}}<br />
              </a>
              <span class="glyphicon glyphicon-headphones" aria-hidden="true"> </span>
              <a href="mailto:{{$task->assignee->work_number}}">
            {{$task->assignee->work_number}}</p>
            </a>
            
          </div>
          <a href="{{route('users.show', $task->assignee->id)}}"> {{$task->assignee->name}}</a>
          
          </div> <!--Shows users assigned to task -->

        </td>
        <td>{{date('d, M Y, H:i', strTotime($task->created_at))}}  </td>
        <td>{{date('d, M Y', strTotime($task->deadline))}}
        @if($task->status == 1)({{ $task->days_until_deadline }}) @endif</td>

        <td></td>
      </tr>
      
      @endforeach
      
    </tbody>
  </table>

</div>
    </div>
    <div id="menu1" class="tab-pane fade">

<div class="boxspace">
<table class="table table-hover">
  <h4>All Leads</h4>
  <thead>
    <thead>
      <tr>
        <th>Title</th>
        <th>Assigned user</th>
        <th>Created date</th>
        <th>Contact at</th>
       
        <th><a href="{{ route('leads.create', ['client' => $client->id])}}"><button class="btn btn-success">Add new lead</button> </a></th>
        
      </tr>
    </thead>
    <tbody>
        <?php  $tr =""; ?>
      @foreach($client->allleads as $lead)
      @if($lead->status == 1)
        <?php  $tr = '#adebad'; ?>
      @elseif($lead->status == 2)
        <?php $tr = '#ff6666'; ?>
      @endif
      <tr style="background-color:<?php echo $tr ;?>">
        
        <td > <a href="{{ route('leads.show', $lead->id) }}">{{$lead->title}} </a></td>
        <td > <div class="popoverOption"
          rel="popover"
          data-placement="left"
          data-html="true"
          data-original-title="<span class='glyphicon glyphicon-user' aria-hidden='true'> </span> {{$lead->assignee->name}}">
          <div id="popover_content_wrapper" style="display:none; width:250px;">
            <img src='http://placehold.it/350x150' height='80px' width='80px' style="float:left; margin-bottom:5px;"/>
            <p class="popovertext">
              <span class="glyphicon glyphicon-envelope" aria-hidden="true"> </span>
              <a href="mailto:{{$lead->assignee->email}}">
              {{$lead->assignee->email}}<br />
              </a>
              <span class="glyphicon glyphicon-headphones" aria-hidden="true"> </span>
              <a href="mailto:{{$lead->assignee->work_number}}">
            {{$lead->assignee->work_number}}</p>
            </a>
            
          </div>
          <a href="{{route('users.show', $lead->assignee->id)}}"> {{$lead->assignee->name}}</a>
          
          </div> <!--Shows users assigned to lead -->

        </td>
        <td>{{date('d, M Y, H:i', strTotime($lead->cotact_date))}}  </td>
        <td>{{date('d, M Y', strTotime($lead->contact_date))}}
        @if($lead->status == 1)({{ $lead->days_until_contact }})@endif </td>

        <td></td>
      </tr>
      
      @endforeach
      
    </tbody>
  </table>

</div>
    </div>
    <div id="menu2" class="tab-pane fade">
    <table class="table">
      <h4>All Documents</h4>

        <div class="col-xs-10">
              <div class="form-group">


<form method="POST" action="{{ url('/clients/upload', $client->id)}}" class="dropzone" id="dropzone" files="true" data-dz-remove
        enctype="multipart/form-data"
>
 <meta name="csrf-token" content="{{ csrf_token() }}">
</form>
<p><b>max 5MB pr. file</b></p>
</div>  
</div>
  <thead>
    <thead>
      <tr>
        <th>File</th>
        <th>Size</th>
        <th>Created at</th>
        
      </tr>
    </thead>
    <tbody>
    @foreach($client->documents as $document)
      <tr>

     <td><a href="../files/{{$companyname}}/{{$document->path}}"  target="_blank">{{$document->file_display}}</a></td>
      <td>{{$document->size}} <span class="moveright"> MB</span></td>
      <td>{{$document->created_at}}</td>
      </tr>
      @endforeach
    </tbody>
    </table>




  

    </div>
  </div>
</div>
<div class="col-md-4 currenttask">
<div class="boxspace">
<!--Tasks stats at some point-->
</div>
</div>
</div>


@stop
