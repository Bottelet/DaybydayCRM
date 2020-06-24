<div class="action-panel">
    <div class="action-panel-header">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#notifications" class="action-panel-header__nav-link" aria-controls="home" role="tab" data-toggle="tab">{{__('Notifications')}}</a>
            </li>
            <li role="presentation">
                <a href="#actions" class="action-panel-header__nav-link" aria-controls="profile" role="tab" data-toggle="tab">{{__('Actions')}}</a>
            </li>
        </ul>
        <button id="close-sidebar" class="action-panel-header__btn-close">
            <i class="flaticon-close"></i>
        </button>
    </div>
    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="notifications">
            @include('partials.action-panel._notification-list')
        </div>
        <div role="tabpanel" class="tab-pane" id="actions">
                <ul>
                    <div class="action-content">
                        <h3 style="position:  absolute; margin-top:  180px; margin-left: 10px;" class="title">@lang('No new actions')</h3>
                        <img src="{{url('/images/undraw_no_data.svg')}}" class="not_found_image_wrapper">
                    </div>
                </ul>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $('#grid-action').click(function (e) {
            e.stopPropagation();
            $(".action-panel").toggleClass('bar')
        });
        $('#page-content-wrapper').click(function (e) {
            if ($('.action-panel').hasClass('bar')) {
                $(".action-panel").toggleClass('bar')
            }
        });

        $('#close-sidebar').click(function (e) {
            if ($('.action-panel').hasClass('bar')) {
                $(".action-panel").toggleClass('bar')
            }
        });

        id = {};
    </script>
@endpush