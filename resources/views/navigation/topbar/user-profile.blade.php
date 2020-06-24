<div class="topbar-user__wrapper">
    <ul class="nav navbar-nav navbar-right">
        <li class="">
            <a href="#" class="dropdown-toggle topbar-user__head" data-toggle="dropdown">
                <span>@lang('Hi'),</span>
                <span class="topbar-user__name">{{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->avatar }}" class="topbar-user__image">
            </a>
            <ul class="dropdown-menu topbar-user__dropdown-menu">
                <div class="topbar-user__content-header">
                    <div class="topbar-user__card-wrapper">
                        <div class="topbar-user__card-image-wrapper">
                            <img src="{{ auth()->user()->avatar }}" class="topbar-user__card-image">
                        </div>
                        <div class="topbar-user__card-details">
                            <div class="topbar-user__card__name">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="topbar-user__card__info">
                                {{ auth()->user()->department->first()->name }}
                            </div>
                        </div>
                    </div>
                </div>
                <ul class="topbar-user__list-wrapper">
                    <li class="topbar-user__list">
                        <a href="{{url('/users', auth()->user()->external_id)}}" class="topbar-user__list-link">
                            <span class="user__list-icon">
                                 <i class="fa fa-user"></i>
                            </span>
                            <span class="user__list-text">
                                @lang('Profile')
                            </span>
                        </a>
                    </li>
                    <li class="topbar-user__list">
                        <a href="{{route('absence.create')}}" class="topbar-user__list-link">
                            <span class="user__list-icon">
                                 <i class="fa fa-clock-o"></i>
                            </span>
                            <span class="user__list-text">
                                @lang('Register absence')
                            </span>
                        </a>
                    </li>
                    <li class="topbar-user__list">
                        <a href="{{url('/users/' .auth()->user()->external_id . '/edit')}}" class="topbar-user__list-link">
                            <span class="user__list-icon">
                                 <i class="fa fa-cog"></i>
                            </span>
                            <span class="user__list-text">
                                @lang('Settings')
                            </span>
                        </a>
                    </li>
                    <li class="topbar-user__list">
                        <a href="{{url('/logout')}}" class="btn btn-outline-metal btn-hover-brand btn-upper btn-font-dark btn-sm btn-bold" style="margin-top: 15px;
    margin-left: 20px;">{{ __('Sign Out') }}</a>
                    </li>
                </ul>
            </ul>
        </li>
    </ul>
</div>
