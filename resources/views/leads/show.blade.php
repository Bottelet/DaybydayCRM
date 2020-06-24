@extends('layouts.master')


@section('content')

    <div class="row">
        @include('partials.clientheader')
        @include('partials.userheader')
    </div>
    <div class="row">
        <div class="col-md-9">
            @include('partials.comments', ['subject' => $lead])
        </div>
        <div class="col-md-3">
            <div class="tablet tablet--tabs tablet--height-fluid">
                <div class="tablet__head tablet__head__color-brand padding-15-sides">
                    <div class="tablet__head-toolbar">
                        <ul class="nav nav-tabs nav-tabs-line nav-tabs-line-brand nav-tabs-bold tablet-brand-color" role="tablist">
                            <li class="nav-item active">
                                <a class="nav-link text-white active" data-toggle="tab" href="#tab_information" role="tab">
                                    @lang('Information')
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" data-toggle="tab" href="#tab_activity" role="tab">
                                    @lang('Activity')
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tablet__body">
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="tab_information" role="tabpanel">
                            <div class="k-scroll ps ps--active-y" data-scroll="true" style="overflow: hidden;" data-mobile-height="350">
                                @include('leads._sidebar')
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab_activity" role="tabpanel">
                            <div class="k-scroll ps ps--active-y" data-scroll="true" style="overflow: hidden;" data-mobile-height="350">
                                @include('leads._timeline')
                            </div>
                        </div>
                    </div>

                </div>
                <div class="tablet__footer">
                    <div class="row">

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="ModalFollowUp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{{ __('Change deadline') }}</h4>
                </div>

                <div class="modal-body">
                    <form action="{{route('lead.followup', $lead->external_id)}}" method="POST">
                        @method('PATCH')
                        @csrf
                        <div class="form-group">
                            <label for="deadline" class="control-label thin-weight">@lang('Change deadline')</label>
                            <input type="text" id="deadline" name="deadline" data-value="{{$lead->deadline}}" class="form-control">
                            <input type="text" name="contact_time" value="{{$lead->deadline->format(carbonTime())}}" class="form-control" id="contact_time">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default col-lg-6"
                                    data-dismiss="modal">{{ __('Close') }}</button>
                            <div class="col-lg-6">
                                <input type="submit" value="{{__('Update deadline')}}" class="btn btn-brand form-control closebtn">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
@push('scripts')
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $('#deadline').pickadate({
                hiddenName:true,
                format: '{{frontendDate()}}',
                formatSubmit: 'yyyy/mm/dd',
                closeOnClear: false,
            });
            $('#contact_time').pickatime({
                format:'{{frontendTime()}}',
                formatSubmit: 'HH:i',
                hiddenName: true
            })
        });

    </script>
@endpush
