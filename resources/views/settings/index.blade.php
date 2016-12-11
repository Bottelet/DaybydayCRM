@extends('layouts.master')
@section('heading')
    <h1>@lang('setting.headers.settings')</h1>
@stop
@section('content')



    <div class="row">
        <table class="table table-responsive table-hover table_wrapper" id="clients-table">
            <thead>
            <tr>
                <th></th>
                @foreach($permission as $perm)

                    <th>{{$perm->display_name}}</th>

                @endforeach
                <th></th>
            </tr>


            </thead>


            @foreach($roles as $role)
                <tr>
                    <div class="col-lg-4">
                        {!! Form::model($permission, [
                        'method' => 'PATCH',
                        'url'    => 'settings/permissionsUpdate',
                        ]) !!}

                        <th>{{$role->display_name}}</th>


                        <input type="hidden" name="role_id" value="{{ $role->id }}"/>
                        @foreach($permission as $perm)
                            <?php $isEnabled = !current(
                                    array_filter(
                                            $role->permissions->toArray(),
                                            function ($element) use ($perm) {
                                                return $element['id'] === $perm->id;
                                            }
                                    )
                            );  ?>

                            <td><input type="checkbox"
                                       <?php if (!$isEnabled) echo 'checked' ?> name="permissions[ {{ $perm->id }} ]"
                                       value="1">
                                <span class="perm-name"></span><br/></td>


                    @endforeach

                </tr>
    </div>
    <td>{!! Form::submit(Lang::get('setting.headers.save_role'), ['class' => 'btn btn-primary']) !!}</td>
    {!! Form::close() !!}
    @endforeach


    </tbody>
    </table>
    </div>




    <div class="row">
        <div class="col-lg-12">
            <div class="sidebarheader movedown"><p>@lang('setting.headers.overall')</p></div>


            {!! Form::model($settings, [
               'method' => 'PATCH',
               'url' => 'settings/overall'
               ]) !!}

                    <!-- *********************************************************************
     *                     Task complete       
     *********************************************************************-->
            <div class="panel panel-default movedown">
                <div class="panel-heading">@lang('setting.overall.task.completion')</div>
                <div class="panel-body">

                    @lang('setting.overall.task.completion_allowed') <br/>
                    @lang('setting.overall.task.completion_not_allowed')
                </div>
            </div>
            {!! Form::select('task_complete_allowed', [1 => Lang::get('setting.headers.allowed'), 2 => Lang::get('setting.headers.not_allowed')], $settings->task_complete_allowed, ['class' => 'form-control']) !!}
                    <!-- *********************************************************************
     *                     Task assign       
     *********************************************************************-->
            <div class="panel panel-default movedown">
                <div class="panel-heading">@lang('setting.overall.task.assigned')</div>
                <div class="panel-body">

                    @lang('setting.overall.task.assigned_allowed') <br/>
                    @lang('setting.overall.task.assigned_not_allowed')
                </div>
            </div>
            {!! Form::select('task_assign_allowed', [1 => Lang::get('setting.headers.allowed'), 2 => Lang::get('setting.headers.not_allowed')], $settings->task_assign_allowed, ['class' => 'form-control']) !!}
                    <!-- *********************************************************************
     *                     Lead complete       
     *********************************************************************-->

            <div class="panel panel-default movedown">
                <div class="panel-heading">@lang('setting.overall.lead.completion')</div>
                <div class="panel-body">

                    @lang('setting.overall.lead.completion_allowed')<br/>
                    @lang('setting.overall.lead.completion_not_allowed')
                </div>
            </div>
            {!! Form::select('lead_complete_allowed', [1 => Lang::get('setting.headers.allowed'), 2 => Lang::get('setting.headers.not_allowed')], $settings->lead_complete_allowed, ['class' => 'form-control']) !!}
                    <!-- *********************************************************************
     *                     Lead assign       
     *********************************************************************-->
            <div class="panel panel-default movedown">
                <div class="panel-heading">@lang('setting.overall.lead.assigned')</div>
                <div class="panel-body">

                    @lang('setting.overall.lead.assigned_allowed')<br/>
                    @lang('setting.overall.lead.assigned_not_allowed')
                </div>
            </div>
            {!! Form::select('lead_assign_allowed', [1 => Lang::get('setting.headers.allowed'), 2 => Lang::get('setting.headers.not_allowed')], $settings->lead_assign_allowed, ['class' => 'form-control']) !!}
            <br/>
            {!! Form::submit(Lang::get('setting.headers.save_overall'), ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    </div>
    </div>

@stop

