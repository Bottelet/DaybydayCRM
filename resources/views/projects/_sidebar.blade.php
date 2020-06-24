<div class="row">
    <div class="col-md-3">{{ __('Assigned') }}</div>
    <div class="col-md-9">
                <span id="assignee-user" class="siderbar-list-value">{{$project->assignee->name}}
                    @if(Entrust::can('can-assign-new-user-to-task'))
                        <i class="icon ion-md-create"></i>
                    @endif
                </span>

        @if(Entrust::can('can-assign-new-user-to-task'))
                <span id="assignee-picker" class="hidden">
                    <form method="POST" action="{{url('projects/updateassign', $project->external_id)}}">
                        {{csrf_field()}}
                        <select name="user_assigned_id"
                                class="small-form-control bootstrap-select assignee-selectpicker dropdown-user-selecter pull-right"
                                id="user-search-select" data-live-search="true"
                                data-style="btn btn-sm dropdown-toggle btn-light"
                                data-container="body"
                                data-dropup-auto="false"
                                onchange="this.form.submit()">
                            @foreach($users as $key => $user)
                                <option data-tokens="{{$user}}"
                                        {{$project->user_assigned_id == $key ? 'selected' : ''}} value="{{$key}}">{{$user}}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </span>
        @endif

    </div>
</div>
<div class="row margin-top-10">
    <div class="col-md-3">{{ __('Created at') }}</div>
    <div class="col-md-9">
        {{ date(carbonFullDateWithText(), strtotime($project->created_at))}}
    </div>
</div>
<div class="row margin-top-10">
    <div class="col-md-3">{{ __('Deadline') }}</div>
    <div class="col-md-9">
                    <span {{Entrust::can('task-update-deadline') ? 'data-toggle=modal data-target=#ModalUpdateDeadline' : ''}}  class="siderbar-list-value {{$project->isCloseToDeadline() ? 'text-danger' : ''}}">{{date(carbonDate(), strTotime($project->deadline))}}
                        @if($project->isCloseToDeadline())
                            <span class="small text-black">({!! $project->days_until_deadline !!})</span>
                        @endif
                        @if(Entrust::can('task-update-deadline'))
                            <i class="icon ion-md-create"></i>
                        @endif
                    </span>


    </div>
</div>
<div class="row margin-top-10">
    <div class="col-md-3">{{ __('Status') }}</div>
    <div class="col-md-9">
                    <span id="status-text" class="siderbar-list-value">
                    {{ $project->status->title }}
                        @if(Entrust::can('task-update-status'))
                            <i class="icon ion-md-create"></i>
                        @endif
                    </span>
        @if(Entrust::can('task-update-status'))
                <span id="status-picker" class="hidden">
                    <form method="POST" action="{{url('projects/updatestatus', $project->external_id)}}">
                        {{csrf_field()}}
                        <select name="status_id"
                                class="small-form-control bootstrap-select status-selectpicker dropdown-user-selecter"
                                id="status-search-select"
                                data-style="btn btn-sm dropdown-toggle btn-light"
                                data-container="body"
                                onchange="this.form.submit()">
                            @foreach($statuses->pluck('title', 'id') as $key => $status)
                                <option
                                        {{$project->status->id == $key ? 'selected' : ''}} value="{{$key}}">{{$status}}</option>
                            @endforeach
                        </select>
                    </form>
                </span>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            $('select').selectpicker();

            $('#assignee-user').on('click',function(){
                if($("#assignee-picker").hasClass("hidden")) {
                    $("#assignee-picker").removeClass("hidden");
                    $("#assignee-user").addClass("hidden");

                    $('.assignee-selectpicker').selectpicker('toggle')
                }
            });

            $('body').on('click',function(e){
                var container = $("#assignee-picker");

                // if the target of the click isn't the container nor a descendant of the container
                if (!container.is(e.target) && container.has(e.target).length === 0)
                {
                    if ($("#assignee-user").is(e.target)) {
                        return
                    }

                    container.addClass("hidden");
                    $("#assignee-user").removeClass("hidden");
                }

            });

            $('#status-text').on('click',function(){
                if($("#status-picker").hasClass("hidden")) {
                    $("#status-picker").removeClass("hidden");
                    $("#status-text").addClass("hidden");

                    $('.status-selectpicker').selectpicker('toggle')
                }
            });

            $('body').on('click',function(e){
                var container = $("#status-picker");

                // if the target of the click isn't the container nor a descendant of the container
                if (!container.is(e.target) && container.has(e.target).length === 0)
                {
                    if ($("#status-text").is(e.target)) {
                        return
                    }

                    container.addClass("hidden");
                    $("#status-text").removeClass("hidden");
                }

            });
        });

    </script>
@endpush
