<?php $subject instanceof \App\Models\Task ? $instance = 'task' : $instance = 'lead' ?>

<div class="panel panel-primary shadow">
    <div class="panel-heading"><p>{{$subject->title}}</p></div>
    <div class="panel-body">
        <p>{{$subject->description }}</p>
        <p class="smalltext">{{ __('Created at') }}:
            {{ date('d F, Y, H:i:s', strtotime($subject->created_at))}}
            @if($subject->updated_at != $subject->created_at)
                <br/>{{ __('Modified') }}: {{date('d F, Y, H:i:s', strtotime($subject->updated_at))}}
            @endif</p>
    </div>
</div>

<?php $count = 0;?>
<?php $i = 1 ?>
@foreach($subject->comments as $comment)
    <div class="panel panel-primary shadow" style="margin-top:15px; padding-top:10px;">
        <div class="panel-body">
            <p class="smalltext">#{{$i++}}</p>
            <p>  {{ $comment->description }}</p>
            <p class="smalltext">{{ __('Comment by') }}: <a
                        href="{{route('users.show', $comment->user->id)}}"> {{$comment->user->name}} </a>
            </p>
            <p class="smalltext">{{ __('Created at') }}:
                {{ date('d F, Y, H:i:s', strtotime($comment->created_at))}}
                @if($comment->updated_at != $comment->created_at)
                        <br/>{{ __('Modified') }} : {{date('d F, Y, H:i:s', strtotime($comment->updated_at))}}
                @endif</p>
        </div>
    </div>
@endforeach
<br/>

@if($instance == 'task')
    {!! Form::open(array('url' => array('/comments/task',$subject->id, ))) !!}
    <div class="form-group">
        {!! Form::textarea('description', null, ['class' => 'form-control', 'id' => 'comment-field']) !!}

        {!! Form::submit( __('Add Comment') , ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
@else
    {!! Form::open(array('url' => array('/comments/lead',$lead->id, ))) !!}
    <div class="form-group">
        {!! Form::textarea('description', null, ['class' => 'form-control', 'id' => 'comment-field']) !!}

        {!! Form::submit( __('Add Comment') , ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
@endif

@push('scripts')
    <script>
        $('#comment-field').atwho({
            at: "@",
            limit: 5, 
            delay: 400,
            callbacks: {
                remoteFilter: function (t, e) {
                    t.length <= 2 || $.getJSON("/users/users", {q: t}, function (t) {
                        e(t)
                    })
                }
            }
        })
    </script>
@endpush