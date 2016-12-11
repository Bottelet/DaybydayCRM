@extends('layouts.master')

@section('content')
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

                });
            });
        });
    </script>

    <div class="div">

        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>
                            @foreach($taskCompletedThisMonth as $thisMonth)
                                {{$thisMonth->total}}
                            @endforeach
                        </h3>

                        <p>@lang('dashboard.tasks.completed_month')</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-book-outline"></i>
                    </div>
                    <a href="{{route('tasks.index')}}" class="small-box-footer">@lang('dashboard.tasks.all') <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>
                            @foreach($leadCompletedThisMonth as $thisMonth)
                                {{$thisMonth->total}}
                            @endforeach
                        </h3>

                        <p>@lang('dashboard.leads.completed_month')</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="{{route('leads.index')}}" class="small-box-footer">@lang('dashboard.leads.all') <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{$totalClients}}</h3>

                        <p>@lang('dashboard.clients.all')</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person"></i>
                    </div>
                    <a href="{{route('clients.index')}}" class="small-box-footer">@lang('dashboard.clients.all') <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>
                            @foreach($totalTimeSpent[0] as $sum => $value)

                                {{$value}}
                            @endforeach
                            @if($value == "")
                                0
                            @endif</h3>

                        <p>@lang('dashboard.time.total')</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="#" class="small-box-footer">@lang('dashboard.time.info') <i
                                class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
        </div>
        <!-- /.row -->

        <?php $createdTaskEachMonths = array(); $taskCreated = array();?>
        @foreach($createdTasksMonthly as $task)
            <?php $createdTaskEachMonths[] = date('F', strTotime($task->created_at)) ?>
            <?php $taskCreated[] = $task->month;?>
        @endforeach

        <?php $completedTaskEachMonths = array(); $taskCompleted = array();?>

        @foreach($completedTasksMonthly as $tasks)
            <?php $completedTaskEachMonths[] = date('F', strTotime($tasks->updated_at)) ?>
            <?php $taskCompleted[] = $tasks->month;?>
        @endforeach

        <?php $completedLeadEachMonths = array(); $leadsCompleted = array();?>
        @foreach($completedLeadsMonthly as $leads)
            <?php $completedLeadEachMonths[] = date('F', strTotime($leads->updated_at)) ?>
            <?php $leadsCompleted[] = $leads->month;?>
        @endforeach

        <?php $createdLeadEachMonths = array(); $leadCreated = array();?>
        @foreach($createdLeadsMonthly as $lead)
            <?php $createdLeadEachMonths[] = date('F', strTotime($lead->created_at)) ?>
            <?php $leadCreated[] = $lead->month;?>
        @endforeach
        <div class="row">

            @include('partials.dashboardone')


        </div>
@endsection
