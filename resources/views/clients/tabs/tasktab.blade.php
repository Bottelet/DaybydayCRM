<div id="task" class="tab-pane fade in active">
  <div class="boxspace">
    <table class="table table-hover">
      <h4>@lang('client.tabs.all_tasks')</h4>
      <thead>
        <thead>
          <tr>
            <th>@lang('client.tabs.headers.title')</th>
            <th>@lang('client.tabs.headers.assigned')</th>
            <th>@lang('client.tabs.headers.created_at')</th>
            <th>@lang('client.tabs.headers.deadline')</th>
            <th><a href="{{ route('tasks.create', ['client' => $client->id])}}"><button class="btn btn-success">@lang('client.tabs.headers.new_task')</button> </a></th>
            
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