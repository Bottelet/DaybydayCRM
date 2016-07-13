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
 <div class="col-md-6">
    <h1 class="moveup">{{$client->name}} ({{$client->company_name}})</h1>         
     {!! Form::open([
          'method' => 'DELETE',
          'route' => ['clients.destroy', $client->id],
          ]) !!}
        {!! Form::submit('Delete Client', ['class' => 'btn btn-danger  btn-xs', 'onclick' => 'return confirm("Are you sure?")']) !!}
              {!! Form::close() !!}



    <!--Client info leftside-->
    <div class="contactleft">
      @if($client->email != "")
      <!--MAIL-->
      <p> <span class="glyphicon glyphicon-envelope" aria-hidden="true" data-toggle="tooltip" title="Email" data-placement="left" > </span>
      <a href="mailto:{{$client->email}}" data-toggle="tooltip" data-placement="left">{{$client->email}}</a></p>
      @endif
      @if($client->primary_number != "")
      <!--Work Phone-->
      <p> <span class="glyphicon glyphicon-headphones" aria-hidden="true" data-toggle="tooltip" title="Primary number" data-placement="left"> </span>
      <a href="tel:{{$client->work_number}}">{{$client->primary_number}}</a></p>
      @endif
      @if($client->secondary_number != "")
      <!--Secondary Phone-->
      <p> <span class="glyphicon glyphicon-phone" aria-hidden="true" data-toggle="tooltip" title="Secondary number" data-placement="left"> </span>
      <a href="tel:{{$client->secondary_number}}">{{$client->secondary_number}}</a></p>
      @endif
      @if($client->address || $client->zipcode || $client->city != "")
      <!--Address-->
      <p> <span class="glyphicon glyphicon-home" aria-hidden="true" data-toggle="tooltip" title="Address/Zip code/city" data-placement="left"> </span>  {{$client->address}} <br />{{$client->zipcode}} {{$client->city}}
    </p>
    @endif
  </div>

  <!--Client info leftside END-->
  <!--Client info rightside-->
  <div class="contactright">
    @if($client->company_name != "")
    <!--Company-->
    <p> <span class="glyphicon glyphicon-star" aria-hidden="true" data-toggle="tooltip" title="Company name" data-placement="left"> </span> {{$client->company_name}}</p>
    @endif
     @if($client->vat != "")
     <!--Company-->            
    <p> <span class="glyphicon glyphicon-cloud" aria-hidden="true" data-toggle="tooltip" title="VAT number" data-placement="left"> </span> {{$client->vat}}</p>
@endif
    @if($client->industry != "")
    <!--Industry-->
    <p> <span class="glyphicon glyphicon-briefcase" aria-hidden="true" data-toggle="tooltip" title="Industry" data-placement="left"> </span> {{$client->industry}}</p>
    @endif
    @if($client->company_type!= "")
    <!--Company Type-->
    <p> <span class="glyphicon glyphicon-globe" aria-hidden="true" data-toggle="tooltip" title="Company type" data-placement="left"> </span>
  {{$client->company_type}}</p>
  @endif
</div>  
</div>

<!--Client info rightside END-->

<!--User info-->
<div  class="col-md-6">
<div class="profilepic"><img class="profilepicsize" 
  @if($client->userAssignee->image_path != "")
      src="../images/{{$companyname}}/{{$client->userAssignee->image_path}}"
  @else
      src="../images/default_avatar.jpg"
  @endif /></div>
<h1 class="moveup">{{$client->userAssignee->name}}</h1>

<!--MAIL-->
<p> <span class="glyphicon glyphicon-envelope" aria-hidden="true"> </span>
<a href="mailto:{{$client->userAssignee->email}}">{{$client->userAssignee->email}}</a></p>
<!--Work Phone-->
<p> <span class="glyphicon glyphicon-headphones" aria-hidden="true"> </span>
<a href="tel:{{$client->userAssignee->work_number}}">{{$client->userAssignee->work_number}}</a></p>

<!--Personal Phone-->
<p> <span class="glyphicon glyphicon-phone" aria-hidden="true"> </span>
<a href="tel:{{$client->userAssignee->personal_number}}">{{$client->userAssignee->personal_number}}</a></p>

</div>
</div>
<!--User info END-->
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