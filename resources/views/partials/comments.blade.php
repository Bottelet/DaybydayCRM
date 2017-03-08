<?php $subject instanceof \App\Models\Task ? $instance = 'task' : $instance = 'lead' ?>

<div class="panel panel-primary shadow">
    <div class="panel-heading"><p>{{$subject->title}}</p></div>
    <div class="panel-body">
        <p>{{$instance == 'task' ? $subject->description : $subject->note}}</p>
        <p class="smalltext">{{ __('Created at') }}:
            {{ date('d F, Y, H:i:s', strtotime($subject->created_at))}}
            @if($subject->updated_at != $subject->created_at)
                <br/>{{ __('Modified') }}: {{date('d F, Y, H:i:s', strtotime($subject->updated_at))}}
            @endif</p>
    </div>
</div>

<?php $count = 0;?>
<?php $i = 1 ?>
@foreach(($instance == 'task' ? $subject->comments : $subject->notes) as $comment)
    <div class="panel panel-primary shadow" style="margin-top:15px; padding-top:10px;">
        <div class="panel-body">
            <p class="smalltext">#{{$i++}}</p>
            <p>  {{$instance == 'task' ? $comment->description : $comment->note}}</p>
            <p class="smalltext">{{ __('Comment by') }}: <a
                        href="{{route('users.show', $comment->user->id)}}"> {{$comment->user->name}} </a>
            </p>
            <p class="smalltext">{{ __('Created at') }}:
                {{ date('d F, Y, H:i:s', strtotime($comment->created_at))}}
                @if($comment->updated_at != $comment->created_at)
                    @if($instance == 'task')
                        <br/>{{ __('Modified') }} : {{date('d F, Y, H:i:s', strtotime($comment->updated_at))}}
                    @else
                        <br/> {{ __('Modified') }}: {{date('d F, Y, H:i:s', strtotime($lead->updated_at))}}
                    @endif
                @endif</p>
        </div>
    </div>
@endforeach
<br/>
@if($instance == 'task')
    {!! Form::open(array('url' => array('/tasks/comments',$subject->id, ))) !!}
    <div class="form-group">
        {!! Form::textarea('description', null, ['class' => 'form-control']) !!}

        {!! Form::submit( __('Add Comment') , ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
@else
    {!! Form::open(array('url' => array('/leads/notes',$lead->id, ))) !!}
    <div class="form-group">
        {!! Form::textarea('note', null, ['class' => 'form-control']) !!}

        {!! Form::submit( __('Add Note') , ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
@endif
