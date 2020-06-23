<div class="tablet">
    <div class="tablet__head">
        <div class="tablet__head-label">
            <h3 class="tablet__head-title">{{ __('Created last 14 days') }}</h3>
        </div>
    </div>
    <div class="tablet__body">
        <div class="tablet__items">
            <graphline class="chart" :datasheet="{{json_encode($datasheet)}}"></graphline>
        </div>
    </div>
    <div class="tablet__footer">

    </div>
</div>
