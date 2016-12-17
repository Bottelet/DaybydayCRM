@extends('layouts.master')
@section('heading')
@stop
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
                    html: true,
                    content: $this.find('#popover_content_wrapper').html()
                });
            });
        });
    </script>
@endpush
    <div class="row">
        @include('partials.clientheader')
        @include('partials.userheader')
    </div>
    <div class="row">
        <div class="col-md-8 currenttask">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#task">@lang('client.tabs.tasks')</a></li>
                <li><a data-toggle="tab" href="#lead">@lang('client.tabs.leads')</a></li>
                <li><a data-toggle="tab" href="#docuemnt">@lang('client.tabs.documents')</a></li>
                <li><a data-toggle="tab" href="#invoice">@lang('client.tabs.invoices')</a></li>

            </ul>
            <div class="tab-content">
                @include('clients.tabs.tasktab')
            </div>
            @include('clients.tabs.leadtab')
            @include('clients.tabs.documenttab')
            @include('clients.tabs.invoicetab')
        </div>
    </div>
    <div class="col-md-4 currenttask">
        <div class="boxspace">
            <!--Tasks stats at some point-->
        </div>
    </div>
    </div>
    </div>
@stop