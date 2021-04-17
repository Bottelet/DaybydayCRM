@extends('layouts.master')

@section('content')
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
        $(document).ready(function () {
            if(!getCookie("step_dashboard") && "{{$settings->company}}") {
                $("#clients").addClass("in");
                // Instance the tour
                var tour = new Tour({
                    storage: false,
                    backdrop: true,
                    steps: [
                        {
                            element: ".col-lg-12",
                            title: "{{trans("Dashboard")}}",
                            content: "{{trans("This is your dashboard, which you can use to get a fast and nice overview, of all your tasks, leads, etc.")}}",
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
                        {
                            path: '/clients/create'
                        }
                    ])
                }

                // Initialize the tour
                tour.init();

                tour.start();
                setCookie("step_dashboard", true, 1000)
            }
            function setCookie(key, value, expiry) {
                var expires = new Date();
                expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 2000));
                document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
            }

            function getCookie(key) {
                var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
                return keyValue ? keyValue[2] : null;
            }
        });
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
