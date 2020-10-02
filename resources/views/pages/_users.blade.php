<div class="tablet">
    <div class="tablet__head">
        <div class="tablet__head-label">
            <h3 class="tablet__head-title">{{ __('Online Users') }}</h3>
        </div>
    </div>
    <div class="tablet__body">
        <div class="tablet__items">
            @foreach($users as $user)
                @if($user->isOnline())
                <div class="tablet__item">
                    <div class="tablet__item__pic">
                        <img src="{{$user->avatar}}" style="border-radius:50px; width: 40px; height: 40px;" alt=""/>
                    </div>
                    <div class="tablet__item__info">
                        <a href="{{url('/users', $user->external_id)}}"
                           class="tablet__item__title">{{$user->name}}</a>
                        <div class="tablet__item__description">
                            {{optional($user->department->first())->name}}
                        </div>
                    </div>
                    <div class="tablet__item__toolbar">
                            <i class="dot-online" data-toggle="tooltip" title="Online" data-placement="left"></i>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    <div class="tablet__footer">

    </div>
</div>
