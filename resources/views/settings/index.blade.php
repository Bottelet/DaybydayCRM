@extends('layouts.master')
@section('heading')
    {{ __('Settings') }}
@stop
@section('content')
    <div class="row">
        <form action="{{route('settings.update')}}" method="POST">
            {!! method_field('PATCH') !!}
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="sidebarheader"><p>{{ __('Overall Settings') }}</p></div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="tablet movedown">
                        <div class="tablet__head slim">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">@lang('Company name')</h3>
                            </div>
                        </div>
                        <div class="tablet__body">
                            <p class="small">@lang('Your company\'s name')</p>
                            <br>
                            <input name="company" type="text" class="form-control company_name" value="{{$settings->company}}" {{!$settings->company ? '' : 'disabled'}} style="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="tablet movedown">
                        <div class="tablet__head slim">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">@lang('Country')</h3>
                            </div>
                        </div>
                        <div class="tablet__body">
                            <p class="small">@lang('Where is your company located?')</p>
                            <br>
                            <select class="form-control" name="country">
                                @foreach(App\Enums\Country::values() as $country)
                                    <option {{ $country->getCode() === $settings->country ? 'selected' : '' }} value="{{$country->getCode()}}">{{__($country->getDisplayValue())}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="tablet movedown">
                        <div class="tablet__head slim">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">@lang('Language')</h3>
                            </div>
                        </div>
                        <div class="tablet__body">
                            <p class="small">@lang('This is the default language for new users, the language can be changed for each user under their profile')</p>
                            <br>
                            <select class="form-control" name="language">
                                <option value="EN">@lang("English")</option>
                                <option value="DK" {{$settings->language == "DK" ? "selected" : ""}}>@lang("Danish")</option>
                                <option value="ES" {{$settings->language == "ES" ? "selected" : ""}}>@lang("Spanish")</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="tablet movedown">
                        <div class="tablet__head slim">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">@lang('Business hours')</h3>
                            </div>
                        </div>
                        <div class="tablet__body">
                            <p class="small">@lang('Your business primary working working hours')</p>
                            <br>
                            <div class="form-group">
                                <div class="col-md-5">
                                    <input type="text" name="start_time" value="{{\Carbon\Carbon::parse('2020-01-01 '. $businessHours["open"])->format(carbonTime())}}" class="form-control" id="start_time" required>
                                    <span class="help-block">
                                    @lang('Start of business')
                        </span>
                                </div>
                                <div class="col-md-1">_</div>
                                <div class="col-md-5">
                                    <input type="text" name="end_time" value="{{\Carbon\Carbon::parse('2020-01-01 ' . $businessHours["close"])->format(carbonTime())}}" class="form-control" id="end_time" required>
                                    <span class="help-block">
                                    @lang('End of business')
                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="sidebarheader"><p>{{ __('Client') }}</p></div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="tablet movedown">
                        <div class="tablet__head slim">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">@lang('Next client number')</h3>
                            </div>
                        </div>
                        <div class="tablet__body">
                            <p class="small">@lang('Change next number generated for a client. This will not affect previously created clients. Has to be higer then previously created client number')</p>
                            <input name="client_number" type="number" class="form-control" min="1" max="9999999" value="{{$clientNumber}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="sidebarheader"><p>{{ __('Invoice') }}</p></div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="tablet movedown">
                        <div class="tablet__head slim">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">@lang('Next invoice number')</h3>
                            </div>
                        </div>
                        <div class="tablet__body">
                            <p class="small">@lang('Change next number generated for a invoice. This will not affect previously created invoices. Has to be higer then previously created invoice number')</p>
                            <input name="invoice_number" type="number" class="form-control" min="1" max="9999999" value="{{$invoiceNumber}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="tablet movedown">
                        <div class="tablet__head slim">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">@lang('Currency')</h3>
                            </div>
                        </div>
                        <div class="tablet__body">
                            <p class="small">@lang('If the currency is changed, the invoice will not be recalculated to the new currency, only the visual is changed, like the prefix, separator, symbol etc')</p>
                            <select class="form-control" name="currency">
                                @foreach($currencies as $currency)
                                    <option value="{{$currency["code"]}}" {{$currentCurrency == $currency["code"] ? 'selected' : ''}}>{{$currency["title"]}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="tablet movedown">
                        <div class="tablet__head slim">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">@lang('Vat percentage')</h3>
                            </div>
                        </div>
                        <div class="tablet__body">
                            <p class="small">@lang('Control the percentage of vat calculated on invoices. If any billing integration is active, will the integration control the VAT, we will only send the full amount')</p>
                            <input name="vat" type="number" class="form-control" min="0" max="100" step=".01" value="{{$vatPercentage}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <input type="submit" class="btn btn-md btn-brand" value="@lang('Save settings')">
            </div>
        </form>
    </div>

@stop


@push('scripts')
    <script>
        $(document).ready(function () {
            $('#start_time').pickatime({
                format:'{{frontendTime()}}',
                formatSubmit: 'HH:i',
                hiddenName: true,
                clear: false,
            })
            $('#end_time').pickatime({
                format:'{{frontendTime()}}',
                formatSubmit: 'HH:i',
                hiddenName: true,
                clear: false,
            })
        });
    </script>
@endpush
