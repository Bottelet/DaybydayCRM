<div class="row">
    <div class="col-md-3">{{ __('Assigned') }}</div>
    <div class="col-md-9">
                <span id="assignee-user" class="siderbar-list-value">{{$tasks->user->name}}
                    @if(Entrust::can('can-assign-new-user-to-task'))
                        <i class="icon ion-md-create"></i>
                    @endif
                </span>

        @if(Entrust::can('can-assign-new-user-to-task'))
            @if(!$tasks->isClosed())
                <span id="assignee-picker" class="hidden">
                    <form method="POST" action="{{url('tasks/updateassign', $tasks->external_id)}}">
                        {{csrf_field()}}
                        <select name="user_assigned_id"
                                class="small-form-control bootstrap-select assignee-selectpicker dropdown-user-selecter pull-right"
                                id="user-search-select" data-live-search="true"
                                data-style="btn btn-sm dropdown-toggle btn-light"
                                data-container="body"
                                onchange="this.form.submit()">
                            @foreach($users as $key => $user)
                                <option data-tokens="{{$user}}"
                                        {{$tasks->user_assigned_id == $key ? 'selected' : ''}} value="{{$key}}">{{$user}}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </span>
            @endif
        @endif

    </div>
</div>
<div class="row margin-top-10">
    <div class="col-md-3">{{ __('Created at') }}</div>
    <div class="col-md-9">
        {{ date(carbonDateWithText(), strtotime($tasks->created_at))}}
    </div>
</div>
<div class="row margin-top-10">
    <div class="col-md-3">{{ __('Deadline') }}</div>
    <div class="col-md-9">
                    <span {{Entrust::can('task-update-deadline') ? 'data-toggle=modal data-target=#ModalUpdateDeadline' : ''}}  class="siderbar-list-value {{$tasks->isCloseToDeadline() ? 'text-danger' : ''}}">{{date(carbonDateWithText(), strTotime($tasks->deadline))}}
                        @if($tasks->isCloseToDeadline())
                            <span class="small text-black">({!! $tasks->days_until_deadline !!})</span>
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
                    {{ $tasks->status->title }}
                        @if(Entrust::can('task-update-status'))
                            <i class="icon ion-md-create"></i>
                        @endif
                    </span>
        @if(Entrust::can('task-update-status'))
            @if(!$tasks->isClosed())
                <span id="status-picker" class="hidden">
                    <form method="POST" action="{{url('tasks/updatestatus', $tasks->external_id)}}">
                        {{csrf_field()}}
                        <select name="status_id"
                                class="small-form-control bootstrap-select status-selectpicker dropdown-user-selecter"
                                id="status-search-select"
                                data-style="btn btn-sm dropdown-toggle btn-light"
                                data-container="body"
                                onchange="this.form.submit()">
                            @foreach($statuses as $key => $status)
                                <option
                                        {{$tasks->status->id == $key ? 'selected' : ''}} value="{{$key}}">{{$status}}</option>
                            @endforeach
                        </select>
                    </form>
                </span>
            @endif
        @endif
    </div>
</div>

<div class="row margin-top-10">
    <div class="col-md-3">{{ __('Project') }}</div>
    <div class="col-md-9">
                    <span id="project-text" class="siderbar-list-value">
                    {{ !is_null($tasks->project) ? $tasks->project->title : __('None')  }}
                        @if(Entrust::can('task-update-status'))
                            <i class="icon ion-md-create"></i>
                        @endif
                    </span>
        @if(Entrust::can('task-update-linked-project'))
                <span id="project-picker" class="hidden">
                    <form method="POST" action="{{route('tasks.update.project', $tasks->external_id)}}">
                        {{csrf_field()}}
                        <select name="project_external_id"
                                class="small-form-control bootstrap-select project-selectpicker dropdown-user-selecter pull-right"
                                id="project-search-select"
                                data-style="btn btn-sm dropdown-toggle btn-light"
                                data-container="body"
                                onchange="this.form.submit()">
                                <option value=""></option>
                            @foreach($projects as $key => $project)
                                <option
                                        {{optional($tasks->project)->external_id == $key ? 'selected' : ''}} value="{{$key}}">{{$project}}</option>
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


            $('#project-text').on('click',function(){
                if($("#project-picker").hasClass("hidden")) {
                    $("#project-picker").removeClass("hidden");
                    $("#project-text").addClass("hidden");

                    $('.project-selectpicker').selectpicker('toggle')
                }
            });

            $('body').on('click',function(e){
                var container = $("#status-picker");
                var containerTwo = $("#project-picker");

                // if the target of the click isn't the container nor a descendant of the container
                if (!container.is(e.target) && container.has(e.target).length === 0)
                {
                    if ($("#status-text").is(e.target)) {
                        return
                    }

                    container.addClass("hidden");
                    $("#status-text").removeClass("hidden");
                }
                                // if the target of the click isn't the container nor a descendant of the containerTwo
                if (!containerTwo.is(e.target) && containerTwo.has(e.target).length === 0)
                {
                    if ($("#project-text").is(e.target)) {
                        return
                    }

                    containerTwo.addClass("hidden");
                    $("#project-text").removeClass("hidden");
                }

            });
        });

    </script>
@endpush
