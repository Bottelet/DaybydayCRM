@extends('layouts.master')
@section('content')

        <form action="{{route('absence.store')}}" method="POST">
            <div class="tablet">
                <div class="tablet__body">
                    <div class="row">
                        @if($users)
                            <div class="col-lg-4">
                                @lang("For which user are you registering absence?")
                            </div>
                            <div class="col-lg-8">
                                <select name="user_external_id"
                                        class="form-control"
                                        id="user-search-select" data-live-search="true"
                                        data-style="btn dropdown-toggle btn-light"
                                        data-container="body"
                                        data-dropup-auto="false"
                                        required>
                                    <option disabled selected value> -- @lang('Select an option') -- </option>
                                    @foreach($users as $key => $user)
                                        <option data-tokens="{{$user}}" value="{{$key}}">{{$user}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <hr style="margin-top:4em;">
                        @endif
                        <div class="col-lg-4">
                            @lang("What's the reason for the absence?")
                        </div>
                        <div class="col-lg-8">
                            <select name="reason" id="reason" class="form-control">
                                @foreach($reasons as $reason)
                                <option value="{{$reason->getReason()}}">{{__($reason->getDisplayValue())}}</option>
                                @endforeach
                            </select>
                        </div>
                        <hr style="margin-top:5em;">

                        <div class="col-lg-4">
                            @lang('When does the absence start?')
                        </div>
                        <div class="col-lg-8">
                            <input type="text" name="start_date" id="start_date" class="form-control" data-value="{{today()->format(carbonDate())}}" >
                        </div>
                        <hr style="margin-top:5em;">

                        <div class="col-lg-4">
                            @lang('How long will the absence be for?')
                        </div>
                        <div class="col-lg-8">
                            <input type="text" name="end_date" id="end_date" class="form-control" data-value="{{today()->format(carbonDate())}}" >
                        </div>
                        <hr style="margin-top:5em;">
                        <div id="medical-certificate">
                            <div class="col-lg-4">
                                @lang('Do you have a medical certificate?')
                            </div>
                            <div class="col-lg-8">
                                <div class="col-lg-4" style="padding-left: 0px;">
                                    <div class="checkboxInputGroup">
                                        <input id="radio1" name="radio" type="radio" value="true"/>
                                        <label for="radio1">@lang('Yes')</label>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="checkboxInputGroup">
                                        <input id="radio2" name="radio" type="radio" value="false"/>
                                        <label for="radio2">@lang('No')</label>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="checkboxInputGroup">
                                        <input id="radio3" name="radio" type="radio" value="irrelevant" checked/>
                                        <label for="radio3">@lang('Irrelevant')</label>
                                    </div>
                                </div>

                            </div>
                            <hr style="margin-top:5em;">
                        </div>
                        <div class="col-lg-12">
                            @lang('Additional comments')
                            <textarea name="comment" class="form-control movedown" id="comment" cols="30" rows="10"></textarea>
                        </div>
                    </div>
                </div>
                <div class="tablet__footer">
                    <input type="submit" class="btn btn-md btn-brand" id="createTask" value="{{__('Confirm')}}" style="margin:1em;">
                </div>
            </div>
            {{csrf_field()}}
        </form>


@stop

@push('scripts')
    <script>
        $('#user-search-select').selectpicker();
        $endDateInput = $('#end_date').pickadate({
            hiddenName:true,
            format: "{{frontendDate()}}",
            formatSubmit: 'yyyy/mm/dd',
            min: true,
            clear: false,
        });

        let endDatePicker = $endDateInput.pickadate('picker')

        $startDateInput = $('#start_date').pickadate({
            hiddenName:true,
            format: "{{frontendDate()}}",
            formatSubmit: 'yyyy/mm/dd',
            clear: false,
            style: "background:#fff",
            onSet: function(context) {
                let minDate = new Date(context.select)
                if(minDate > new Date(endDatePicker.get('select', 'yyyy/mm/dd'))) {
                    endDatePicker.set('select', minDate)
                }
                endDatePicker.set('min', minDate)
            }
        });
        let startDatePicker = $startDateInput.pickadate('picker')

        $('#reason').change(function() {
            if ($(this).val() === 'sick_leave') {
                $("#medical-certificate").show();
            } else {
                $("#medical-certificate").hide();
            }
        });
    </script>
@endpush

