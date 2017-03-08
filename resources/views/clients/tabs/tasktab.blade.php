<div id="task" class="tab-pane fade in active">
    <div class="boxspace">
        <table class="table table-hover">
            <h4>{{ __('All Tasks') }}</h4>
            <thead>
            <thead>
            <tr>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Assigned') }}</th>
                <th>{{ __('Created at') }}</th>
                <th>{{ __('Deadline') }}</th>
                <th><a href="{{ route('tasks.create', ['client' => $client->id])}}">
                        <button class="btn btn-success">{{ __('New task') }}</button>
                    </a></th>

            </tr>
            </thead>
            <tbody>
            <?php  $tr = ""; ?>
            @foreach($client->tasks as $task)
                @if($task->status == 1)
                    <?php  $tr = '#adebad'; ?>
                @elseif($task->status == 2)
                    <?php $tr = '#ff6666'; ?>
                @endif
                <tr style="background-color:<?php echo $tr;?>">

                    <td><a href="{{ route('tasks.show', $task->id) }}">{{$task->title}} </a></td>
                    <td>
                        <div class="popoverOption"
                             rel="popover"
                             data-placement="left"
                             data-html="true"
                             data-original-title="<span class='glyphicon glyphicon-user' aria-hidden='true'> </span> {{$task->user->name}}">
                            <div id="popover_content_wrapper" style="display:none; width:250px;">
                                <img src='http://placehold.it/350x150' height='80px' width='80px'
                                     style="float:left; margin-bottom:5px;"/>
                                <p class="popovertext">
                                    <span class="glyphicon glyphicon-envelope" aria-hidden="true"> </span>
                                    <a href="mailto:{{$task->user->email}}">
                                        {{$task->user->email}}<br/>
                                    </a>
                                    <span class="glyphicon glyphicon-headphones" aria-hidden="true"> </span>
                                    <a href="mailto:{{$task->user->work_number}}">
                                    {{$task->user->work_number}}</p>
                                </a>

                            </div>
                            <a href="{{route('users.show', $task->user->id)}}"> {{$task->user->name}}</a>

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