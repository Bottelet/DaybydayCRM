<div class="col-md-6">
    <div class="panel panel-primary contact-header-box">
        <div class="panel-body">
            @if(\Route::getCurrentRoute()->getName() != "users.show")
            <a href="{{route('users.show', $contact->external_id)}}"><i class="ion ion-ios-redo " title="{{ __('Go to user') }}" style="
                float: right;
                margin-right: 1em;
                color:#61788b;
                "></i></a>
            @endif
            <div class="col-sm-2">
                <div class="profilepic"><img class="profilepicsize" src="{{ $contact->avatar }}"/></div>
            </div>
            <div class="col-sm-8">
            <?php isset($changeUser) ?: $changeUser = false ?>
            @if($changeUser == false )
                    <p class="name-text">{{ $contact->name }}</p>
            @else

               <span id="assignee-user" class="siderbar-list-value name-text"> {{ $contact->name }}
                   @if(Entrust::can('client-update'))
                       <i class="icon ion-md-create"></i>
                   @endif
                    </span>
                @if(Entrust::can('client-update'))
                    <span id="assignee-picker" class="hidden">
                        <form method="POST" action="{{url('clients/updateassign', $client->external_id)}}">
                            {{csrf_field()}}
                            <select name="user_external_id"
                                    class="small-form-control bootstrap-select assignee-selectpicker dropdown-user-selecter pull-right"
                                    id="user-search-select" data-live-search="true"
                                    data-style="btn btn-sm dropdown-toggle btn-light"
                                    data-container="body"
                                    onchange="this.form.submit()">
                                @foreach(\App\Models\User::all()->pluck('nameAndDepartment', 'external_id') as $key => $user)
                                    <option {{$contact->external_id == $key ? 'selected' : ''}} data-tokens="{{$user}}" value="{{$key}}">{{$user}}</option>
                                @endforeach
                            </select>
                        </form>
                    </span>
                @endif
            @endif
                <p class="department-text">
                    {{$contact->department()->first()->name}}
                </p>
                <!--MAIL-->
                @if($contact->email)
                    <p class="contact-paragraph">
                        <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                    </p>
                    <!--Work Phone-->
                @endif
                @if($contact->primary_number)
                    <p class="contact-paragraph">
                        <a href="tel:{{ $contact->primary_number }}">{{ $contact->primary_number }}</a>

                        @endif
                        @if($contact->secondary_number && $contact->primary_number)
                            /
                    @endif
                    @if($contact->secondary_number)
                        <!--Personal Phone-->
                            <a href="tel:{{ $contact->secondary_number }}">{{ $contact->secondary_number }}</a>
                    </p>
                @endif
            </div>
        </div>

    </div>
</div>

@if($changeUser == true)
@push('scripts')
    <script>
        $(document).ready(function () {
            $('.assignee-selectpicker').selectpicker()
            $('#assignee-user').on('click',function(){
                if($("#assignee-picker").hasClass("hidden")) {
                    $("#assignee-picker").removeClass("hidden");
                    $("#assignee-user").addClass("hidden");
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
        });

    </script>
@endpush
@endif
