@extends('layouts.master')

@section('content')
@push('styles')
    <style>
        /* Improve contrast and visibility for the tour dismiss (end) control */
        .popover.tour .popover-navigation .btn[data-role="end"],
        .popover.tour .popover-navigation .btn.btn-contrast {
            background-color: #c62828; /* deep red for strong contrast */
            border-color: #8e1b1b;
            color: #fff;
            font-weight: 600;
        }
        .popover.tour .popover-navigation .btn[data-role="end"]:hover,
        .popover.tour .popover-navigation .btn.btn-contrast:hover {
            background-color: #b71c1c;
            border-color: #7f1515;
            color: #fff;
        }
        .popover.tour .popover-title { font-weight: 600; }
    </style>
@endpush
@push('scripts')
    <script>
        $(document).ready(function () {
            if(!'{{$settings->company}}') {
                $('#modal-create-client').modal({backdrop: 'static', keyboard: false})
                $('#modal-create-client').modal('show');
            }
            $('[data-toggle="tooltip"]').tooltip(); //Tooltip on icons top

            $('.popoverOption').each(function () {
                var $this = $(this);
                $this.popover({
                    trigger: 'hover',
                    placement: 'left',
                    container: $this,
                    html: true,

                });
            });
        });
        @if(!config('app.tour_disabled'))
        $(document).ready(function () {
            var TOUR_COOKIE = 'dashboard_tour_dismissed';
            if(!getCookie(TOUR_COOKIE) && !getCookie("step_dashboard") && "{{$settings->company}}") {
                $("#clients").addClass("in");
                var tour = new Tour({
                    storage: false,
                    backdrop: true,
                    template: ''+
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
                        '</div>',
                    onEnd: function () {
                        setCookie(TOUR_COOKIE, '1', 3650);
                    },
                    steps: [
                        {
                            element: ".col-lg-12",
                            title: "{{trans("Dashboard")}}",
                            content: "{{trans("This is your dashboard, which you can use to get a quick overview of all your tasks, leads, etc.")}}",
                            placement: 'top'
                        },
                        {
                            element: "#myNavmenu",
                            title: "{{trans("Navigation")}}",
                            content: "{{trans("This is your primary navigation bar, which you can use to get around Daybyday CRM")}}"
                        }
                    ]
                });

                var canCreateClient = '{{ auth()->user()->can('client-create') }}';
                if(canCreateClient) {
                    tour.addSteps([
                        {
                            element: "#newClient",
                            title: "{{trans("Create New Client")}}",
                            content: "{{trans("Let's take our first step, by creating a new client")}}"
                        },
                        { path: '/clients/create' }
                    ]);
                }

                tour.init();
                tour.start();
            }
            function setCookie(key, value, days) {
                var expires = new Date();
                expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
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
        <!-- Small boxes (Stat box) -->
        @if(isDemo())
            <div class="alert alert-info">
                <strong>Info!</strong> Data on the demo environment is reset every 24hr.
            </div>
        @endif

        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-white">
                    <div class="inner" style="min-height: 100px">
                        <h3>
                            {{$totalTasks}}
                        </h3>

                        <p>{{ __('Total tasks') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-book-outline"></i>
                    </div>
                    <a href="{{route('tasks.index')}}" class="small-box-footer">{{ __('All Tasks') }} <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-white">
                    <div class="inner">
                        <h3>
                            {{$totalLeads}}
                         </h3>

                        <p>{{ __('Total leads') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="{{route('leads.index')}}" class="small-box-footer">{{ __('All Leads') }} <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-white">
                    <div class="inner">
                        <h3>{{$totalProjects}}</h3>
                        <p>{{ __('Total projects') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="{{route('projects.index')}}" class="small-box-footer">{{ __('All Projects') }} <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-white">
                    <div class="inner">
                        <h3>{{$totalClients}}</h3>

                        <p>{{ __('Total clients') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person"></i>
                    </div>
                    <a href="{{route('clients.index')}}" class="small-box-footer">{{ __('All clients') }} <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-8 col-xs-6">
                @include('pages._createdGraph')
            </div>
            <div class="col-lg-4 col-xs-6">
                @include('pages._users')
            </div>
            @if(auth()->user()->can('absence-view'))
                <div class="col-lg-4 col-xs-6">
                    @include('pages._absent')
                </div>
            @endif
        </div>
        <!-- /.row -->
@if(!$settings->company)
<div class="modal fade" id="modal-create-client" tabindex="-1" role="dialog">
    @include('pages._firstStep')
</div>
@endif
@endsection
