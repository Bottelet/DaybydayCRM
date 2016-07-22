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

          <h3>{{$tasks->title}}</h3>
          <hr class="grey">
          <p>{{$tasks->description}}</p>
          <p class="smalltext">Created at: 
          {{ date('d F, Y, H:i:s', strtotime($tasks->created_at))}} 
          @if($tasks->updated_at != $tasks->created_at) 
          <br/>Modified at: {{date('d F, Y, H:i:s', strtotime($tasks->updated_at))}}
          @endif</p>
          

              
      </div>
    
          <?php $count = 0;?>

           <?php $i=1 ?>
          @foreach($tasks->comments as $comment)
          <div class="taskcase" style="margin-top:15px; padding-top:10px;">
                  <p  class="smalltext">#{{$i++}}</p>
                  <p>  {{$comment->description}}</p>
                  <p class="smalltext">Comment by: <a href="{{route('users.show', $comment->user->id)}}"> {{$comment->user->name}} </a></p>
                            <p class="smalltext">Created at: 
          {{ date('d F, Y, H:i:s', strtotime($comment->created_at))}} 
          @if($comment->updated_at != $comment->created_at) 
          <br/>Modified at: {{date('d F, Y, H:i:s', strtotime($comment->updated_at))}}
          @endif</p>
                  </div>
          @endforeach
  <br />
         {!! Form::open(array('url' => array('/tasks/comments',$tasks->id, ))) !!}
          <div class="form-group">
    {!! Form::textarea('description', null, ['class' => 'form-control']) !!}
    
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
            <a href="{{route('users.show', $tasks->assignee->id)}}">
             {{$tasks->assignee->name}}</a></p>
            <p>Created at: {{ date('d F, Y, H:i', strtotime($tasks->created_at))}} </p>
           
            @if($tasks->days_until_deadline)
             <p >Deadline: <span style="color:red;">{{date('d, F Y', strTotime($tasks->deadline))}}

              @if($tasks->status == 1)({!! $tasks->days_until_deadline !!})@endif</span></p><!--Remove days left if tasks is completed-->

             @else
             <p >Deadline: <span style="color:green;">{{date('d, F Y', strTotime($tasks->deadline))}}

             @if($tasks->status == 1)({!! $tasks->days_until_deadline !!})@endif</span></p> <!--Remove days left if tasks is completed-->
             @endif
       
            @if($tasks->status == 1)
            Task status: Open
            @else
            Task status: Closed
            @endif
            </div>   
            @if($tasks->status == 1)

          {!! Form::model($tasks, [
         'method' => 'PATCH',
          'url' => ['tasks/updateassign', $tasks->id],
          ]) !!}
           {!! Form::select('fk_user_id_assign', $users, null, ['class' => 'form-control ui search selection top right pointing search-select', 'id' => 'search-select']) !!}
          {!! Form::submit('Assign new user', ['class' => 'btn btn-primary form-control closebtn']) !!}
       {!! Form::close() !!}

                {!! Form::model($tasks, [
          'method' => 'PATCH',
          'url' => ['tasks/updatestatus', $tasks->id],
          ]) !!}
            
          {!! Form::submit('Close task', ['class' => 'btn btn-success form-control closebtn']) !!}
       {!! Form::close() !!}

            @endif
             <div class="sidebarheader">
            <p>Time Managment</p>
            </div>
         <table class="table table_wrapper ">
           <tr>
             <th>Title</th>
             <th>Time</th>
           </tr>
           <tbody>
            @foreach($tasktimes as $tasktime)
            <tr>
             <td style="padding: 5px">{{$tasktime->title}}</td> 
             <td style="padding: 5px">{{$tasktime->time}} </td>
             </tr>
            @endforeach
           </tbody>
         </table>
  <br/>
       <button type="button" class="btn btn-primary form-control" data-toggle="modal" data-target="#ModalTimer">
  Add Time
</button>
@if($apiconnected)
       <button type="button" class="btn btn-primary form-control movedown" data-toggle="modal" data-target="#myModal">
  Add to Invoice
</button>
@endif
<div class="activity-feed movedown">
          @foreach($tasks->activity as $activity)
          <div class="feed-item">
          <div class="activity-date">{{date('d, F Y H:i', strTotime($activity->created_at))}}</div>
             <div class="activity-text">{{$activity->text}}</div>
                
                </div>
        @endforeach
</div>
<div class="modal fade" id="ModalTimer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Time Managment For This Task  ({{$tasks->title}})</h4>
      </div>

      <div class="modal-body">
      
          {!! Form::open([
          'method' => 'post',
          'url' => ['tasks/updatetime', $tasks->id],
          ]) !!}

<div class="form-group">
    {!! Form::label('title', 'Title:', ['class' => 'control-label']) !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'Fx Consultation Meeting']) !!}
</div>
<div class="form-group">
    {!! Form::label('comment', 'Description:', ['class' => 'control-label']) !!}
    {!! Form::textarea('comment', null, ['class' => 'form-control', 'placeholder' => 'Short Comment about whats done(Will show on Invoice)']) !!}
</div>
<div class="form-group">
    {!! Form::label('value', 'Hourly price(DKK):', ['class' => 'control-label']) !!}
    {!! Form::text('value', null, ['class' => 'form-control', 'placeholder' => '300']) !!}
</div>
<div class="form-group">
    {!! Form::label('time', 'Time spend (Hours):', ['class' => 'control-label']) !!}
    {!! Form::text('time', null, ['class' => 'form-control', 'placeholder' => '3']) !!}
</div>
    


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default col-lg-6" data-dismiss="modal">Close</button>
    <div class="col-lg-6">
        {!! Form::submit('Register time', ['class' => 'btn btn-success form-control closebtn']) !!}
        </div>
       {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>


@if($apiconnected)

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Choose Contact</h4>
      </div>

      <div class="modal-body">

               {!! Form::model($tasks, [
          'method' => 'POST',
          'url' => ['tasks/invoice', $tasks->id],
          ]) !!}
          
               @foreach ($contacts as $key => $contact)
        {!! Form::radio('invoiceContact', $contact['guid']) !!}
        {{$contact['name']}}
    <br />
@endforeach
            {!! Form::label('mail', 'Send mail with invoice to Customer?(Cheked = Yes):', ['class' => 'control-label']) !!}
        {!! Form::checkbox('sendMail', true) !!}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default col-lg-6" data-dismiss="modal">Close</button>
    <div class="col-lg-6">
        {!! Form::submit('Create Invoice', ['class' => 'btn btn-success form-control closebtn']) !!}
        </div>
       {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>
@endif

             </div>
         
            </div> 
          </div>

        </div>


     
@stop