@extends('layouts.master')

@section('heading')

@stop

@section('content')
@push('scripts')
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush
    <div class="row">
        @include('partials.clientheader')
        @include('partials.userheader')
    </div>

    <div class="row">
        <div class="col-md-9">
            @include('partials.comments', ['subject' => $lead])
        </div>
        <div class="col-md-3">
            <div class="sidebarheader">
                <p> {{ __('Lead information') }}</p>
            </div>
            <div class="sidebarbox">
                <p>{{ __('Assigned to') }}:
                    <a href="{{route('leads.show', $lead->user->id)}}">
                        {{$lead->user->name}}</a></p>
                <p>{{ __('Created at') }}: {{ date('d F, Y, H:i', strtotime($lead->created_at))}} </p>
                @if($lead->days_until_contact < 2)
                    <p>{{ __('Follow up') }}: <span style="color:red;">{{date('d, F Y, H:i', strTotime($lead->contact_date))}}

                            @if($lead->status == 1) ({!! $lead->days_until_contact !!}) @endif</span> <i
                                class="glyphicon glyphicon-calendar" data-toggle="modal"
                                data-target="#ModalFollowUp"></i></p> <!--Remove days left if lead is completed-->

                @else
                    <p>{{ __('Follow up') }}: <span style="color:green;">{{date('d, F Y, H:i', strTotime($lead->contact_date))}}

                            @if($lead->status == 1) ({!! $lead->days_until_contact !!})<i
                                    class="glyphicon glyphicon-calendar" data-toggle="modal"
                                    data-target="#ModalFollowUp"></i>@endif</span></p>
                    <!--Remove days left if lead is completed-->
                @endif
                @if($lead->status == 1)
                    {{ __('Status') }}: {{ __('Contact') }}
                @elseif($lead->status == 2)
                    {{ __('Status') }}: {{ __('Completed') }}
                @elseif($lead->status == 3)
                    {{ __('Status') }}: {{ __('Not interested') }}
                @endif

            </div>
            @if($lead->status == 1)
                {!! Form::model($lead, [
               'method' => 'PATCH',
                'url' => ['leads/updateassign', $lead->id],
                ]) !!}
                {!! Form::select('user_assigned_id', $users, null, ['class' => 'form-control ui search selection top right pointing search-select', 'id' => 'search-select']) !!}
                {!! Form::submit(__('Assign new user'), ['class' => 'btn btn-primary form-control closebtn']) !!}
                {!! Form::close() !!}
                {!! Form::model($lead, [
               'method' => 'PATCH',
               'url' => ['leads/updatestatus', $lead->id],
               ]) !!}

                {!! Form::submit(__('Complete Lead'), ['class' => 'btn btn-success form-control closebtn movedown']) !!}
                {!! Form::close() !!}
            @endif

            <div class="activity-feed movedown">
                @foreach($lead->activity as $activity)

                    <div class="feed-item">
                        <div class="activity-date">{{date('d, F Y H:i', strTotime($activity->created_at))}}</div>
                        <div class="activity-text">{{$activity->text}}</div>

                    </div>
                @endforeach
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

                    {!! Form::model($lead, [
                      'method' => 'PATCH',
                      'route' => ['leads.followup', $lead->id],
                      ]) !!}
                    {!! Form::label('contact_date', __('Next follow up'), ['class' => 'control-label']) !!}
                    {!! Form::date('contact_date', \Carbon\Carbon::now()->addDays(7), ['class' => 'form-control']) !!}
                    {!! Form::time('contact_time', '11:00', ['class' => 'form-control']) !!}


                    <div class="modal-footer">
                        <button type="button" class="btn btn-default col-lg-6"
                                data-dismiss="modal">{{ __('Close') }}</button>
                        <div class="col-lg-6">
                            {!! Form::submit( __('Update follow up'), ['class' => 'btn btn-success form-control closebtn']) !!}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
       

   