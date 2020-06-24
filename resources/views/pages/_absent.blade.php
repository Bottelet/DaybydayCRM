<div class="tablet">
    <div class="tablet__head">
        <div class="tablet__head-label">
            <h3 class="tablet__head-title">{{ __('Absent Today') }}</h3>
        </div>
    </div>
    <div class="tablet__body">
        <div class="tablet__items">
            @if($absencesToday->isEmpty())
                <h4>@lang('No registered absences today')</h4>
            @endif
            @foreach($absencesToday as $absence)
                    <div class="tablet__item">
                        <div class="tablet__item__pic">
                            <img src="{{$absence->user->avatar}}" style="border-radius:50px; width: 40px; height: 40px;" alt=""/>
                        </div>
                        <div class="tablet__item__info">
                            <a href="{{url('/users', $absence->user->external_id)}}"
                               class="tablet__item__title">{{$absence->user->name}}</a>
                            <div class="tablet__item__description">
                                {{$absence->start_at->format(carbonDateWithText())}} - {{$absence->end_at->format(carbonDateWithText())}}
                            </div>
                        </div>
                    </div>
            @endforeach
        </div>
    </div>
    <div class="tablet__footer">

    </div>
</div>
