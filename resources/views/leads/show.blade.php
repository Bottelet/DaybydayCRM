@extends('layouts.master')


@section('content')

    <div class="row">
        @include('partials.clientheader')
        @include('partials.userheader')
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="tablet tablet--tabs tablet--height-fluid">
                <div class="tablet__head ">
                    <div class="tablet__head-toolbar">
                        Offers
                    </div>
                    @if(Entrust::can('offer-create'))
                    <div class="tablet__head"  style="padding: 6px 2px;">
                        <button class="btn btn-brand" id="create-offer-btn">@lang('New Offer')</button>
                    </div>
                    @endif
                </div>
                <div class="tablet__body">
                    <table class="table table-hover" id="leads-table">
                    <thead>
                        <tr>
                            <th>{{ __('Indicator') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Created at') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                        <tbody>
                        @foreach($offers as $index => $offer)
                            <tr>
                                <td>{{$index +1}}</td>
                                <td>{{formatMoney($offer->getTotalPrice())}}</td>
                                <td>{{$offer->getInvoice()->status}}</td>
                                <td>{{$offer->getInvoice()->created_at->format(carbonDateWithText()) }}</td>
                                <td>
                                @if(!in_array($offer->getInvoice()->status, ['won', 'lost']))
                                    @if(Entrust::can('offer-edit'))
                                        <button class="btn btn-brand" data-toggle="modal" data-target="#ModalWonOffer" data-offer-external_id="{{$offer->getInvoice()->external_id}}"><span class="fa fa-check"></span></button>
                                        <button class="btn btn-warning" data-toggle="modal" data-target="#ModalLostOffer" data-offer-external_id="{{$offer->getInvoice()->external_id}}"><span class="fa fa-times"></span></button>
                                        <button class="btn btn-info edit-offer-btn" data-offer-external_id="{{$offer->getInvoice()->external_id}}"><span class="fa fa-pencil"></span></button>
                                    @endif
                                @endif
                      
                                @if($offer->getInvoice()->invoice)
                                <button class="btn view-offer-btn" data-offer-external_id="{{$offer->getInvoice()->external_id}}"><span class="fa fa-eye"></span></button>
                                <a href="{{route('invoices.show', $offer->getInvoice()->invoice->external_id)}}">
                                    <button class="btn btn-brand"><span class="fa fa-file-text-o"></span></button>
                                </a>
                                @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @if(Entrust::can('offer-create'))
    <div class="modal fade" id="create-offer" tabindex="-1" role="dialog" aria-hidden="true"
         style="display:none;">
        <div class="modal-dialog modal-lg" style="background:white;">
            <invoice-line-modal type="offer" :resource="{{$lead}}"/>
        </div>
    </div>
    @endif
    <div class="modal fade" id="view-offer" tabindex="-1" role="dialog" aria-hidden="true"
         style="display:none;">
        <div class="modal-dialog modal-lg view-offer-inner" style="background:white;">
            
        </div>
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
    @if(Entrust::can('offer-edit'))
        <div class="modal fade" id="ModalWonOffer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">{{ __('Offer won') }}</h4>
                    </div>

                    <div class="modal-body">
                    <p>
                        @lang('This will set the offer as won, and convert it to a sale.')
                    </p>
                        <form action="{{route('offer.won')}}" method="POST">
                            @csrf
                            <input type="hidden" name="offer_external_id" >
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default col-lg-6"
                                        data-dismiss="modal">{{ __('Close') }}</button>
                                <div class="col-lg-6">
                                    <input type="submit" value="{{__('Confirm')}}" class="btn btn-brand form-control closebtn">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="ModalLostOffer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">{{ __('Offer lost') }}</h4>
                    </div>

                    <div class="modal-body">
                    <p>
                        @lang('This will set the offer as lost, and lose the offer.')
                    </p>
                        <form action="{{route('offer.lost')}}" method="POST">
                            @csrf
                            <input type="hidden" name="offer_external_id" >
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default col-lg-6"
                                        data-dismiss="modal">{{ __('Close') }}</button>
                                <div class="col-lg-6">
                                    <input type="submit" value="{{__('Confirm')}}" class="btn btn-brand form-control closebtn">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop
@push('scripts')
    <script>
        $(document).ready(function () {      
            $('#ModalWonOffer').on('show.bs.modal', function(e) {
                var offerExternalId = $(e.relatedTarget).data('offer-external_id');
                $(e.currentTarget).find('input[name="offer_external_id"]').val(offerExternalId);
            }); 
            $('#ModalLostOffer').on('show.bs.modal', function(e) {
                var offerExternalId = $(e.relatedTarget).data('offer-external_id');
                $(e.currentTarget).find('input[name="offer_external_id"]').val(offerExternalId);
            }); 
            $('#create-offer-btn').on('click', function () {
                $('#create-offer').modal('show');
            });

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
