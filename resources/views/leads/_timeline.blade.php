<div class="activity-feed">
    @foreach($lead->activity()->orderBy('id', 'desc')->get() as $activity)
        <div class="feed-item">
            <div class="activity-date">{{date(carbonFullDateWithText(), strTotime($activity->created_at))}}</div>
            <div class="activity-text">{{$activity->text}}</div>
        </div>
    @endforeach
</div>
