@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="tablet">
                <div class="tablet__head">
                    <div class="tablet__head-label text-center">
                        <h3 class="tablet__head-title">{{ __('Invoice summary') }}</h3>
                    </div>

                </div>
                <div class="tablet__body">
                    <div class="tablet__items">
                        @foreach($invoice->invoiceLines as $invoice_line)
                                <div class="tablet__item" style="padding: 0;">
                                    <div class="tablet__item__info">
                                        <p class="invoice-title">{{$invoice_line->title}}</p>

                                        <div class="tablet__item__description">
                                            <p class="invoice-info">{{$invoice_line->quantity}} x {{$invoice_line->price_converted}}</p>
                                            <p class="invoice-info small">{{ __($invoice_line->type) }}</p>
                                        </div>
                                    </div>
                                    <div class="tablet__item__toolbar">
                                        <div class="dropdown dropdown-inline">
                                            @if($invoice->canUpdateInvoice())
                                            <form action="{{route('invoiceLine.destroy', $invoice_line->external_id)}}" method="post">
                                                @method('delete')
                                                {{csrf_field()}}
                                                <p>
                                            @endif
                                                    {{$invoice_line->total_value_converted}}
                                            @if($invoice->canUpdateInvoice())
                                                <button type="submit" class="fa fa-btn fa-trash-o btn btn-clean trashcan-icon"></button>
                                                </p>
                                            </form>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                                <hr style="margin-top: 5px;">
                        @endforeach
                            @if(Entrust::can('modify-invoice-lines'))
                                @if(!$invoice->sent_at)
                                <!-- Insert new item part--->
                                    <div class="tablet__item" style="padding: 0;">
                                        <div class="tablet__item__info">
                                            <button id="time-manager" style="
                                                border: 0;
                                                padding: 0;
                                                background: transparent;
                                                font-size:1.5em;
                                                color:#337ab7;">
                                                <i class="icon ion-md-add-circle"></i>
                                                <span style="font-size:0.7em; font-weight:400;">@lang('Insert new invoice line')</span>
                                            </button>
                                        </div>
                                    </div>
                                    <hr style="margin-top: 5px;">
                            @endif
                        @endif

                        <!-- Vat Total price--->
                            <div class="tablet__item" style="padding: 0;">
                                <div class="tablet__item__info">
                                    <span>@lang('Tax')</span>
                                </div>
                                <div class="tablet__item__toolbar">
                                    <div class="dropdown dropdown-inline">
                                        <span>{{$vatPrice}}</span>
                                    </div>
                                </div>
                            </div>
                        <!-- Sub Total price--->
                            <div class="tablet__item" style="padding: 0;">
                                <div class="tablet__item__info">
                                    <span>@lang('Sub Total')</span>
                                </div>
                                <div class="tablet__item__toolbar">
                                    <div class="dropdown dropdown-inline">
                                        <span>{{$subPrice}}</span>
                                    </div>
                                </div>
                            </div>
                        <!-- Total price--->
                            <div class="tablet__item" style="padding: 0;">
                                <div class="tablet__item__info">
                                    <span class="final-price">@lang('Total')</span>
                                </div>
                                <div class="tablet__item__toolbar">
                                    <div class="dropdown dropdown-inline">
                                        <span class="final-price">{{$finalPrice}}</span>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
                <div class="tablet__footer">
                    <div class="row">
                        <div class="col-lg-6 col-sm-12">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="tablet">
                <div class="tablet__body" style="padding-bottom: 3em;">
                    <p class="invoice-title">{{$client->company_name}}
                        <a href="{{route('clients.show', $client->external_id)}}"><i class="ion ion-ios-redo " title="{{ __('Go to client') }}" style="
                        float: right;
                        margin-right: 1em;
                        color:#61788b;
                        "></i></a>
                    </p>
                    <p class="invoice-info">{{$contact_info->name}}</p>
                    <p class="invoice-info">{{$contact_info->email}}</p>
                    <hr style="margin-top: 5px;">
                    <div class="row">
                        <div class="col-md-6" style="padding-bottom: 1em;">
                            <p class="invoice-info-title">@lang('Invoice created')</p>
                            <p class="invoice-info-subtext">{{date(carbonDate(), strtotime($invoice->created_at))}}</p>
                        </div>
                        <div class="col-md-6" style="padding-bottom: 1em;">
                            <p class="invoice-info-title">@lang('Invoice date')</p>
                            <p class="invoice-info-subtext">{{ !$invoice->sent_at ? __('Not send') : date(carbonDate(), strtotime($invoice->sent_at))}}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="invoice-info-title">@lang('Due date')</p>
                            <p class="invoice-info-subtext">{{ !$invoice->due_at ? __('Not set') : date(carbonDate(), strtotime($invoice->due_at))}}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="invoice-info-title">@lang('Amount due')</p>
                            <p class="invoice-info-subtext">{{$amountDueFormatted}}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="invoice-info-title">@lang('Status')</p>
                            <p class="invoice-info-subtext">{{\App\Enums\InvoiceStatus::fromStatus($invoice->status)->getDisplayValue()}}</p>
                        </div>
                        @if($source)
                            <div class="col-md-6">
                                <p class="invoice-info-title">@lang('Reference')</p>
                                <p class="invoice-info-subtext">
                                    <a href="{{$source->getShowRoute()}}">{{__(class_basename(get_class($source)))}}</a>
                                </p>
                            </div>
                        @endif
                        @if($invoice->invoice_number != null)
                            <div class="col-md-6">
                                <p class="invoice-info-title">@lang('Invoice number')</p>
                                <p class="invoice-info-subtext">
                                    {{$invoice->invoice_number}}
                                </p>
                            </div>
                        @endif
                        @if($invoice->offer)
                            <div class="col-md-6">
                                <p class="invoice-info-title">@lang('Based on')</p>
                                <p class="invoice-info-subtext">
                                    <button data-offer-external_id={{$invoice->offer->external_id}} class="btn btn-link" style="padding: 0px;" id="view-original-offer">@lang('Offer')</button> 
                                </p>
                            </div>
                        @endif
                        <hr>
                        <div class="col-md-6">
                            @if(Entrust::can('invoice-pay'))
                                        <button type="button" id="update-payment" class="btn btn-md btn-brand btn-full-width closebtn"
                                                <?php $titleText =  !$invoice->isSent() ? __("Can't pay an invoice with status draft. Send invoice first or force a new status") : "" ?> title="{{$titleText}}"
                                                {{ !$invoice->isSent() ? 'disabled ' : "" }}
                                                data-toggle="modal" data-target="#update-payment-modal">@lang('Register payment')</button>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if(Entrust::can('invoice-send'))
                                <button type="button" id="sendInvoice" class="btn btn-md btn-brand btn-full-width closebtn" value="add_time_modal"
                                        <?php $titleText =  $invoice->isSent() ? __('Invoice already sent') : "" ?> title="{{$titleText}}"
                                        {{ $invoice->isSent() ? 'disabled ' : "" }}
                                        data-toggle="modal" data-target="#SendInvoiceModalConfirm" >
                                    {{ __('Send invoice') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            @if($invoice->payments->isNotEmpty())
                @include('invoices._paymentList')
            @endif
        </div>
    </div>
    <div class="modal fade" id="view-offer" tabindex="-1" role="dialog" aria-hidden="true"
         style="display:none;">
        <div class="modal-dialog modal-lg view-offer-inner" style="background:white;">
            
        </div>
    </div>
@if(!$invoice->sent_at)
<div class="modal fade" id="SendInvoiceModalConfirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">

                {{ __('Are you sure?') }}
                    </h4>
                    <p>{{ __('Once a invoice has been send, no new invoice lines can be added') }}</p>
                 {!! Form::open([
                    'method' => 'post',
                    'route' => ['invoice.sent', $invoice->external_id],
                    ]) !!}
                    @if($apiconnected)
                    <p>{{ __('We have found this contact from your billing integration, do you wish for us to create the invoice in your your billing system as well?, than please choose a contact below') }}</p>
                    <select name="invoiceContact"
                            class="small-form-control bootstrap-select contacts-selectpicker"
                            id="user-search-select" data-live-search="true"
                            data-style="btn btn-md dropdown-toggle btn-light"
                            data-container="body">
                        <option value="" style="color:lightslategrey"> @lang("Nothing selected")</option>
                        @foreach($contacts as $contact)
                            <option data-tokens="{{$contact['name']}}"
                                    value="{{$contact['guid']}}" {{optional($primaryContact)["guid"] == $contact['guid'] ? "selected" : ""}}>{{$contact['name']}}
                            </option>
                        @endforeach

                    </select>
                <div id="sendMailBox">
                    <label for="sendMail" class="control-label" id="sendMailCheckboxLabel">@lang('Send mail with invoice to Customer?(Cheked = Yes):')</label>
                    <input type="checkbox" name="sendMail" id="sendMailCheckbox">
                    <p style="font-size:10px;">{{ __('The Mail will be send with your default settings and template from your billing integration.')}}</p>
                </div>
                @endif
                <div id="send-mail" style="display: none">
                    @lang('Attach invoice as PDF')
                    <input type="checkbox" name="attachPdf" value="1"> <br>
                    @lang('Recipient')
                    <input type="text" class="form-control" name="recipientMail" value="{{$invoice->client->primaryContact->email}}">
                    @lang('Subject')
                    <input type="text" class="form-control" name="subject" value="{{__('Invoice from :company', ["company" => $companyName])}}">

                    @lang('Message') (@lang("[link-to-pdf], will be replaced when invoice is send, with the actual link to the PDF"))

                    <textarea name="message" id="" rows="13" class="form-control">@lang("Dear :name\n\nThank you, for being a customer at :company\n\nHere is you Invoice on :price\n\nClick the link below to download the invoice\n\n[link-to-pdf]\n\nRegards\n---\n:company", ["name" => $invoice->client->primaryContact->name, "company" => $companyName, "price" => $finalPrice])</textarea>
                </div>
                <input type="submit" value="{{__('Send invoice')}}" class="btn btn-md btn-brand btn-full-width closebtn" id="close-invoice">
            {!! Form::close() !!}
            </div>

        </div>
    </div>
</div>
@endif
    <div class="modal fade" id="add-invoice-line-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
        <div class="modal-dialog modal-lg" style="background:white;">
            <invoice-line-modal type="invoiceLine" :resource="{{$invoice}}"/>
        </div>
    </div>
    <div class="modal fade" id="update-payment-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
        <div class="modal-dialog">
            <div class="modal-content" style="padding:2em;">
                @include('invoices._updatePaymentModal')
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            if($('#sendMailCheckbox').val() == ""){
                $('#sendMailBox').hide();
            }
            $('#time-manager').on('click', function () {
                $('#add-invoice-line-modal').modal('show');
            });

            $('.contacts-selectpicker').selectpicker();

            $('#user-search-select').change(function(){
                if($(this).val() == ""){
                    $('#sendMailBox').hide();
                    $('#sendMailCheckbox').prop("checked", false);
                    $('#send-mail').hide(150);
                } else {
                    $('#sendMailBox').show();
                }
            });

            $('#sendMailCheckbox').change(function(){
                if(this.checked)
                    $('#send-mail').show(150);
                else
                    $('#send-mail').hide(150);
            });

        });
    </script>
@endpush
