@extends('layouts.master')
@section('content')
    <div class="row">
        @include('partials.clientheader')
        @include('partials.userheader', ['changeUser' => true])
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="tablet">
                <div class="tablet__head">
                    <div class="tablet__head-label">
                        <h3 class="tablet__head-title">@lang('Overview')</h3>
                    </div>
                </div>
                <div class="tablet__body">
                    <el-tabs active-name="tasks" style="width:100%">
                        <el-tab-pane label="{{ __('Projects') }}" name="projects">
                            @include('clients.tabs.projectstab')
                        </el-tab-pane>
                        <el-tab-pane label="{{ __('Tasks') }}" name="tasks">
                            @include('clients.tabs.tasktab')
                        </el-tab-pane>
                        <el-tab-pane label="{{ __('Leads') }}" name="leads">
                            @include('clients.tabs.leadtab')
                        </el-tab-pane>
                        @if(Entrust::can('invoice-see'))
                            <el-tab-pane label="{{ __('Invoices') }}" name="invoices">
                                @include('clients.tabs.invoicetab')
                            </el-tab-pane>
                        @endif
                        @if($filesystem_integration)
                            <el-tab-pane label="{{ __('Documents') }}" name="document">
                                @include('clients.tabs.documenttab')
                            </el-tab-pane>
                        @endif
                    </el-tabs>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tablet">
                <div class="tablet__head">
                    <span class="tablet__head-label">
                        <h3 class="tablet__head-title">{{ __('Recent appointments') }}</h3>
                        <span>
                            <create-appointment
                                    v-on:created-appointment="reload()"
                                    client-external-id="{{$client->external_id}}"
                                    button-style="font-size:22px; background-color:transparent; color:#242939;  margin-right:0em; margin-left:2.8em; margin-top:0.2em; border-radius:10%;">
                            </create-appointment>
                        </span>
                    </span>
                </div>
                <div class="tablet__body">
                    @if($recentAppointments->isEmpty())
                        <h5 style="font-weight: 300;">@lang('No recent appointments for the last 3 months')</h5>
                    @endif
                    <ul style="padding: 0px;">
                        @foreach($recentAppointments as $recentAppointment)
                            <li style="list-style: none;">
                                <p>{{$recentAppointment->title}} <br>
                                <span style="font-size:10px;">
                                    {{$recentAppointment->start_at->format(carbonFullDateWithText())}} - {{$recentAppointment->end_at->format(carbonFullDateWithText())}}
                                    <hr style="background: {{$recentAppointment->color}};">
                                </span>
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

