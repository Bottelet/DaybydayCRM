@extends('layouts.master')

@section('heading')

@stop

@section('content')
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); 
});
</script>

<div class="row">
@include('partials.clientheader')
@include('partials.userheader')
</div>

     <div class="row">
        <div class="col-md-9">
          <div class="taskcase">

          <h3>{{$leads->title}}</h3>
          <hr class="grey">
          <p>{{$leads->note}}</p><br />
          <p class="smalltext">Created at: 
          {{ date('d F, Y, H:i:s', strtotime($leads->created_at))}} 
          @if($leads->updated_at != $leads->created_at) 
          <br/>Modified at: {{date('d F, Y, H:i:s', strtotime($leads->updated_at))}}
          @endif</p>
          

              
      </div>
              <?php $i=1 ?>
       
          @foreach($leads->notes as $note)
          <div class="taskcase" style="margin-top:15px; padding-top:10px;">
                  <p  class="smalltext">#{{$i++}}</p>
                  <p>  {{$note->note}}</p>
                  <p class="smalltext">note by: <a href="{{route('users.show', $note->user->id)}}"> {{$note->user->name}} </a></p>
                            <p class="smalltext">Created at: 
          {{ date('d F, Y, H:i:s', strtotime($note->created_at))}} 
          @if($note->updated_at != $note->created_at) 
          <br/>Modified at: {{date('d F, Y, H:i:s', strtotime($note->updated_at))}}
          @endif</p>
                  </div>
          @endforeach
  <br />
         {!! Form::open(array('url' => array('/leads/notes',$leads->id, ))) !!}
          <div class="form-group">
    {!! Form::textarea('note', null, ['class' => 'form-control']) !!}
    
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>
        {!! Form::close() !!}

          </div>
          <div class="col-md-3">
          <div class="sidebarheader">
            <p>Task information</p>
             </div>
             <div class="sidebarbox">
            <p>Assigned to:
            <a href="{{route('leads.show', $leads->assignee->id)}}">
             {{$leads->assignee->name}}</a></p>
            <p>Created at: {{ date('d F, Y, H:i', strtotime($leads->created_at))}} </p>
            @if($leads->days_until_contact < 2)
             <p >Follow up: <span  style="color:red;">{{date('d, F Y, H:i', strTotime($leads->contact_date))}} 

             @if($leads->status == 1) ({!! $leads->days_until_contact !!}) @endif</span> <i class="glyphicon glyphicon-calendar" data-toggle="modal" data-target="#ModalFollowUp"></i></p> <!--Remove days left if lead is completed-->

             @else
             <p >Follow up: <span style="color:green;">{{date('d, F Y, H:i', strTotime($leads->contact_date))}}

             @if($leads->status == 1) ({!! $leads->days_until_contact !!})<i class="glyphicon glyphicon-calendar" data-toggle="modal" data-target="#ModalFollowUp"></i>@endif</span></p> <!--Remove days left if lead is completed-->
             @endif
             @if($leads->status == 1)
                Status: Contact client
            @elseif($leads->status == 2)
                Status: Completed 
          @elseif($leads->status == 3)
              Satus: Client not Interested
             @endif
              
          </div>
@if($leads->status == 1)
          {!! Form::model($leads, [
         'method' => 'PATCH',
          'url' => ['leads/updateassign', $leads->id],
          ]) !!}
           {!! Form::select('fk_user_id_assign', $users, null, ['class' => 'form-control ui search selection top right pointing search-select', 'id' => 'search-select']) !!}
          {!! Form::submit('Assign new user', ['class' => 'btn btn-primary form-control closebtn']) !!}
       {!! Form::close() !!}
           {!! Form::model($leads, [
          'method' => 'PATCH',
          'url' => ['leads/updatestatus', $leads->id],
          ]) !!}

          {!! Form::submit('Complete Lead', ['class' => 'btn btn-success form-control closebtn movedown']) !!}
    {!! Form::close() !!}
       @endif

               <div class="activity-feed movedown">
          @foreach($leads->activity as $activity)
          <div class="feed-item">
          <div class="activity-date">{{date('d, F Y H:i', strTotime($activity->created_at))}}</div>
             <div class="activity-text">{{$activity->text}}</div>
                
                </div>
        @endforeach
</div>
          </div>

        </div>


<div class="modal fade" id="ModalFollowUp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Change Deadline</h4>
      </div>

      <div class="modal-body">
      
      {!! Form::model($leads, [
        'method' => 'PATCH',
        'route' => ['leads.followup', $leads->id],
        ]) !!}
          {!! Form::label('contact_date', 'Next follow up:', ['class' => 'control-label']) !!}
    {!! Form::date('contact_date', \Carbon\Carbon::now()->addDays(7), ['class' => 'form-control']) !!}
     {!! Form::time('contact_time', '11:00', ['class' => 'form-control']) !!}


     <div class="modal-footer">
        <button type="button" class="btn btn-default col-lg-6" data-dismiss="modal">Close</button>
    <div class="col-lg-6">
        {!! Form::submit('Update follow up', ['class' => 'btn btn-success form-control closebtn']) !!}
        </div>
       {!! Form::close() !!}
        </div>
      </div>
    </div>
  </div>
</div>
@stop
       

   