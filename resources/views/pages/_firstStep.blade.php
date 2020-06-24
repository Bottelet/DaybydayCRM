<form class="form-horizontal" role="form" method="POST" action="{{route('settings.update.first_step')}}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    @lang('Update settings')
                </h4>
            </div>

            <div class="modal-body">
                <!-- Form Errors -->
                <div class="alert alert-info">
                    <p>@lang('This information can always be changed in Settings')</p>
                </div>

                <!-- Create Client Form -->

                <!-- Name -->
                <div class="form-group">
                    <label class="col-md-3 control-label">@lang('Company name')</label>

                    <div class="col-md-7">
                        <input id="create-client-name" type="text" name="company_name" required class="form-control">

                        <span class="help-block">
                                    @lang('Your company\'s name')
                            </span>
                    </div>
                </div>
            {{csrf_field()}}
            <!-- Redirect URL -->
                <div class="form-group">
                    <label class="col-md-3 control-label">@lang('Country')</label>

                    <div class="col-md-7">
                        <select name="country" class="form-control" required>
                            @foreach(App\Enums\Country::values() as $country)
                                <option value="{{$country->getCode()}}">{{__($country->getDisplayValue())}}</option>
                            @endforeach
                        </select>
                        <span class="help-block">
                                    @lang('Where is your company located?')
                            </span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">@lang('Business hours')</label>

                    <div class="col-md-3">
                        <input type="text" name="start_time" value="{{\Carbon\Carbon::parse('2020-01-01 08:00:00')->format(carbonTime())}}" class="form-control" id="start_time">
                        <span class="help-block">
                                    @lang('Start of business')
                        </span>
                    </div>
                    <div class="col-md-1">_</div>
                    <div class="col-md-3">
                        <input type="text" name="end_time" value="{{\Carbon\Carbon::parse('2020-01-01 17:00:00')->format(carbonTime())}}" class="form-control" id="end_time">
                        <span class="help-block">
                                    @lang('End of business')
                        </span>
                    </div>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="modal-footer">
                <input type="submit" class="btn btn-md btn-brand" class="" value="{{__('Confirm')}}">
            </div>
        </div>
    </div>
</form>
@push('scripts')
    <script>
        $(document).ready(function () {
            $('#start_time').pickatime({
                format:'{{frontendTime()}}',
                formatSubmit: 'HH:i',
                hiddenName: true
            })
            $('#end_time').pickatime({
                format:'{{frontendTime()}}',
                formatSubmit: 'HH:i',
                hiddenName: true
            })
        });
    </script>
@endpush
