<div class="tablet">
    <div class="tablet__head tablet__head__color-brand">
        <div class="tablet__head-label">
            <h3 class="tablet__head-title text-white">{{$subject->title}}</h3>
        </div>
    </div>
    <div class="tablet__body">
        <p class="">{!! $subject->description !!}</p>
    </div>
    <div class="tablet__footer">
        <p class="smalltext">{{ __('Created at') }}:
            {{ date(carbonFullDateWithText(), strtotime($subject->created_at))}}
            @if($subject->updated_at != $subject->created_at)
                <br/>{{ __('Modified') }}: {{date(carbonFullDateWithText(), strtotime($subject->updated_at))}}
            @endif</p>
    </div>
</div>

<?php $count = 0;?>
<?php $i = 1 ?>
@foreach($subject->comments as $comment)
    <div class="tablet tablet__shadow">
        <div class="tablet__body tablet__tigthen">
            <p class="smalltext">#{{$i++}}</p>
            <p>  {!! $comment->description !!}</p>
        </div>
        <div class="tablet__footer tablet__tigthen">
            <p class="smalltext">{{ __('Comment by') }}: <a
                        href="{{route('users.show', $comment->user->external_id)}}"> {{$comment->user->name}} </a>
            </p>
            <p class="smalltext">{{ __('Created at') }}:
                {{ date(carbonFullDateWithText(), strtotime($comment->created_at))}}
                @if($comment->updated_at != $comment->created_at)
                    <br/>{{ __('Modified') }} : {{date(carbonFullDateWithText(), strtotime($comment->updated_at))}}
                @endif</p>
        </div>
    </div>

@endforeach
<br/>
    {!! Form::open(array('url' => $subject->getCreateCommentEndpoint())) !!}
    <div class="form-group">
        {!! Form::textarea('description', null, ['class' => 'form-control', 'id' => 'comment-field']) !!}
        {!! Form::submit( __('Add Comment') , ['class' => 'btn btn-brand btn-md btn-upper movedown']) !!}
    </div>
    {!! Form::close() !!}

@push('scripts')
    <script>

        $(document).ready(function() {
            $('#comment-field').summernote({
                toolbar: [
                    [ 'fontsize', [ 'fontsize' ] ],
                    [ 'font', [ 'bold', 'italic', 'underline','clear'] ],
                    [ 'color', [ 'color' ] ],
                    [ 'para', [ 'ol', 'ul', 'paragraph'] ],
                    [ 'table', [ 'table' ] ],
                    [ 'insert', [ 'link', 'picture'] ],
                    [ 'view', [ 'fullscreen' ] ]
                ],
                height:300,
                disableDragAndDrop: false,
                maximumImageFileSize: 120*1024,

            });

            $('.note-editable').atwho({
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

        });
    </script>
@endpush
