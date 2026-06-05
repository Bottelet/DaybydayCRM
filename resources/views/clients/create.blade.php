@extends('layouts.master')
@section('content')
@push('scripts')
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip(); //Tooltip on icons top

            $('.popoverOption').each(function () {
                var $this = $(this);
                $this.popover({
                    trigger: 'hover',
                    placement: 'left',
                    container: $this,
                    html: true
                });
            });
        });
        @if(!config('app.tour_disabled'))
        $(document).ready(function () {
            if(!getCookie("step_client_create")) {
                $("#clients").addClass("in");

                var TOUR_TEMPLATE = ''+
                    '<div class="popover tour" role="dialog">'+
                    '  <div class="arrow"></div>'+
                    '  <button type="button" data-role="end" aria-label="{{ trans("Close tour") }}" '+
                    '    style="position:absolute;top:6px;right:10px;background:none;border:none;'+
                    '           font-size:22px;line-height:1;cursor:pointer;color:#555;z-index:1;" '+
                    '    title="{{ trans("Close tour") }}">&#215;</button>'+
                    '  <h3 class="popover-title"></h3>'+
                    '  <div class="popover-content"></div>'+
                    '  <div class="popover-navigation" style="padding:8px 14px 10px;display:flex;gap:6px;align-items:center;">'+
                    '    <button class="btn btn-sm btn-default" data-role="prev">&#8592; {{ trans("Prev") }}</button>'+
                    '    <button class="btn btn-sm btn-primary" data-role="next">{{ trans("Next") }} &#8594;</button>'+
                    '    <button class="btn btn-sm btn-danger" data-role="end" style="margin-left:auto;">&#10005; {{ trans("Don\'t show again") }}</button>'+
                    '  </div>'+
                    '</div>';

                var tour = new Tour({
                    storage: false,
                    backdrop: true,
                    template: TOUR_TEMPLATE,
                    onEnd: function () {
                        setCookie("step_client_create", '1', 3650);
                    },
                    steps: [
                        {
                            element: "#clientCreateForm",
                            title: "{{trans("Fill out the form")}}",
                            content: "{{trans("Fill out the form to get started, the only required fields are name, company name, and email")}}",
                            placement: 'top'
                        },
                        {
                            element: "#submitClient",
                            title: "{{trans("Click the submit button")}}",
                            content: "{{trans("Click the create new client button, and you're done")}}",
                            placement: 'top'
                        }
                    ]
                });

                tour.init();
                tour.start();
            }
            function setCookie(key, value, expiry) {
                var expires = new Date();
                expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
                document.cookie = key + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
            }
            function getCookie(key) {
                var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
                return keyValue ? keyValue[2] : null;
            }
        });
        @endif
    </script>
@endpush

    <?php
    $data = Session::get('data');
    ?>
<h1>Create Client</h1>
<hr>
    <form action="{{ url('/clients/create/cvrapi') }}" method="POST">
        @csrf
        @if($country && $country->getCode() == "DK")
        <div class="col-sm-3">
            <p style="font-size:1.2em; font-weight:300;">VAT</p>
        </div>
        <div class="col-sm-9">
         <div class="form-group">
            <div class="input-group">
                <input type="text" name="vat" class="form-control" placeholder="Insert company VAT">
                <div class="popoverOption input-group-addon"
                    rel="popover"
                    data-placement="left"
                    data-html="true"
                    data-original-title="<span>Only for danish VAT, atm.</span>">?
                </div>
            </div>
            <input type="submit" value="{{ __('Find Company') }}" class="btn btn-sm btn-brand clientvat">
        </div>
    </div>
    <hr>
    @endif
    </form>

    <form action="{{ route('clients.store') }}" method="POST" class="ui-form" id="clientCreateForm">
        @csrf
        @include('clients.form', ['submitButtonText' => __('Create New Client')])
    </form>


@stop
