<?php $notifications = auth()->user()->unreadNotifications; ?>
<div class="notification-wrapper">
    @if($notifications->isEmpty())
        <ul>
            <div class="action-content">
                <h3 style="position:  absolute; margin-top:  180px; margin-left: 10px;" class="title">@lang('No new notifications')</h3>
                <img src="{{url('/images/undraw_empty.svg')}}" class="not_found_image_wrapper">
            </div>
        </ul>
    @endif
    <ul>
        @foreach($notifications as $notification)
            <li>
                <div class="action-header">
                    <img src="{{ auth()->user()->avatar }}" class="action-image"/>
                    <span class="action-date">{{$notification->created_at->diffForHumans()}}</span>
                </div>
                <div class="action-content">
                    <a href="{{ route('notification.read', ['id' => $notification->id])  }}"
                       onClick="postRead({{ $notification->id }})">
                        <p>{{ $notification->data['message']}}</p>
                    </a>
                </div>
                <div class="action-author">
                    {{\App\Models\User::find($notification->data['created_user'])->name}}
                </div>
            </li>
        @endforeach
    </ul>
</div>

@push('scripts')
    <script>
        id = {};

        function postRead(id) {
            $.ajax({
                type: 'post',
                url: '{{url('/notifications/markread')}}',
                data: {
                    id: id,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        }

    </script>
@endpush