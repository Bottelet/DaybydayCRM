@extends('layouts.master')

@section('content')
<div class="row">
    @include('partials.clientheader')
    @include('partials.userheader', ['changeUser' => false])
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="project-board-ui">
            <nav class="navbar board text-black ">
                @if(!$project->isClosed())
                <a href="{{route('client.project.task.create', [$client->external_id, $project->external_id])}}" class="btn btn-md btn-brand" style="margin:1em;">@lang('New task')</a>
            @endif
        </nav>
            <div class="project-board-lists">
                @foreach($statuses as $status)
                <div class="project-board-list">
                    <header>{{ __($status->title)}}</header>
                    <ul class="sortable" id="{{$status->title}}" data-status-external-id="{{$status->external_id}}" style="min-height: 32em;">
                        @foreach($tasks as $task)
                        <li data-task-id="{{$task->external_id}}">
                            @if($task->status_id == $status->id)
                                <div class="project-board-card-wrapper">
                                  <div class="project-board-card">
                                    <div class="position-relative">
                                    </div>
                                    <p class="project-board-card-title"><a href="{{route('tasks.show', $task->external_id)}}" class="link-color">{{$task->title}}</a></p>
                                    <div class="project-board-card-description">{!! str_limit($task->description, 154, '...') !!}</div>
                                  </div>
                                  <div class="project-board-card-footer">
                                    <ul class="list-inline" style="padding: 8px; min-height: 3.3em;">
                                        <li class="project-board-card-list">20, February 2019</li>
                                        <li class="project-board-card-thumbnail text-right" style="float:right;">
                                        <a href="{{route('users.show', $task->user->external_id)}}" ><img src="{{$task->user->avatar}}" class="project-board-card-thumbnail-image" title="{{$task->user->name}}"/></a>
                                        </li>
                                    </ul>
                                  </div>
                                </div> 
                            @endif
                        @endforeach
                        </li>
                    </ul>
                </div>
                    @endforeach  
            </div>
        </div>
    </div>
</div>

<div class="row movedown">
    <div class="col-lg-8">
        <div class="tablet">
            <div class="tablet__body">
                <h3 class="tablet__head-title">@lang('Project completion progress')</h3>
                <div class="progress">
                  <div class="progress-bar" role="progressbar" style="width: {{$completionPercentage}}%;" aria-valuenow="{{$completionPercentage}}" aria-valuemin="0" aria-valuemax="{{$completionPercentage}}">{{$completionPercentage}}%</div>
                </div>
            </div> 
        </div>
    </div>
    <div class="col-lg-4">
        <div class="tablet">
            <div class="tablet__body">
                <h3 class="tablet__head-title">@lang('Collaborators')</h3>
                <ul class="list-inline">
                @foreach($collaborators as $collaborator)
                <li>
                     <a href="{{route('users.show', $collaborator->external_id)}}" >
                        <img src="{{$collaborator->avatar}}" class="project-board-card-thumbnail-image" title="{{$collaborator->name}}"/>
                    </a>
                </li>
                @endforeach
                </ul>
            </div> 
        </div>
    </div>
</div>

<div class="row movedown">
      <div class="col-sm-8">
          @include('partials.comments', ['subject' => $project])
      </div>
      <div class="col-sm-4">
      <div class="tablet">
          <div class="tablet__head tablet__head__color-brand">
              <div class="tablet__head-label">
                  <h3 class="tablet__head-title text-white">@lang('Information')</h3>
              </div>
          </div>
          <div class="tablet__body">
            @include('projects._sidebar')
          </div>
      </div>
  </div>
  <div class="col-sm-4">@if(Entrust::can('project-upload-files') && $filesystem_integration)
                <div id="document" class="tab-pane">
                    <div class="tablet">
                        <div class="tablet__head">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">{{ __('All Files') }}</h3>
                                <button id="add-files" style="
                                margin-left: 30rem !important;
                                border: 0;
                                padding: 0;
                                background: transparent;
                                font-size:2em;">
                                    <i class="icon ion-md-add-circle text-right"></i>
                                </button>
                            </div>

                        </div>
                        <div class="tablet__body">
                            @if($files->count() == 0)
                                <div class="tablet__item">
                                    <div class="tablet__item__pic">
                                        <p class="title">@lang('No files')</p>
                                    </div>
                                </div>
                            @endif
                            <div class="tablet__items">
                                @foreach($files as $file)
                                    <div class="tablet__item">
                                        <div class="tablet__item__pic">
                                            <img src="{{url('images/doc-icon.svg')}}" alt="">
                                        </div>
                                        <div class="tablet__item__info">

                                            <a href="{{ route('document.view', $file->external_id) }}" class="tablet__item__title" target="_blank">{{$file->original_filename}}</a>
                                            <div class="tablet__item__description">
                                                {{$file->size}} MB
                                            </div>
                                        </div>
                                        <div class="tablet__item__toolbar">
                                            <div class="dropdown dropdown-inline">
                                                <button type="button" class="btn btn-clean btn-sm btn-icon btn-icon-md"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="icon ion-md-more" style="font-size: 2.5em;"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <ul class="tablet__nav">
                                                        <li class="nav-item">
                                                            <a href=" {{ route('document.view', $file->external_id) }}" target="_blank" class="nav-link">
                                                                <i class="icon ion-md-eye"></i>
                                                                <span class="nav-link-text">@lang('View')</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href=" {{ route('document.download', $file->external_id) }}" target="_blank" class="nav-link">
                                                                <i class="icon ion-md-cloud-download"></i>
                                                                <span class="nav-link-text">@lang('Download')</span>
                                                            </a>
                                                        </li>
                                                        @if(Entrust::can('document-delete'))

                                                            <li class="nav-item">
                                            <span class="nav-link">
                                                <i class="icon ion-md-trash"></i>
                                                <form method="POST" action="{{action('DocumentsController@destroy', $file->external_id)}}">
                                                    <input type="hidden" name="_method" value="delete"/>
                                                    <input type="hidden" name="_token" value="{{csrf_token()}}"/>
                                                    <button type="submit" class="btn btn-clean nav-link-text">{{__('Delete')}}</button>
                                                </form>
                                            </span>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
                @endif</div>
</div>


@if(Entrust::can('project-update-deadline'))
    <div class="modal fade" id="ModalUpdateDeadline" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{{ __('Change deadline') }}</h4>
                </div>

                <div class="modal-body">

                    {!! Form::model($project, [
                      'method' => 'PATCH',
                      'route' => ['project.update.deadline', $project->external_id],
                      ]) !!}
                    {!! Form::label('deadline_date', __('Change deadline'), ['class' => 'control-label']) !!}
                    {!! Form::date('deadline_date', \Carbon\Carbon::now()->addDays(7), ['class' => 'form-control']) !!}
                    {!! Form::text('deadline_time', '15:00', ['class' => 'form-control', 'onkeydown' => 'return isNumberKey(this)', 'onchange' => 'validateHhMm(this)', 'id' => 'deadline_time']) !!}




                    <div class="modal-footer">
                        <button type="button" class="btn btn-default col-lg-6"
                                data-dismiss="modal">{{ __('Close') }}</button>
                        <div class="col-lg-6">
                            {!! Form::submit( __('Update deadline'), ['class' => 'btn btn-success form-control closebtn']) !!}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
<div class="modal fade" id="add-files-modal" tabindex="-1" role="dialog" aria-hidden="true"
         style="display:none;">
    <div class="modal-dialog">
        <div class="modal-content"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$( ".sortable" ).sortable({
    axis: 'X',
    connectWith: '.sortable',
    receive: function (event, ui,) {
        var taskExternalId = ui.item.attr('data-task-id');
        var statusExternalId =  $(this).attr('data-status-external-id')
        var url = '{{ route("task.update.status", ":taskExternalId") }}';
        url = url.replace(':taskExternalId', taskExternalId);
    // POST to server using $.post or $.ajax
    $.ajax({
        data: {
            "_token": "{{ csrf_token() }}",
            "statusExternalId": statusExternalId,
            "test": $(this).parent().attr('id')
        },
        type: 'PATCH',
        url: url
    });
}
});
    @if(Entrust::can('project-upload-files') && $filesystem_integration)
    $('#add-files').on('click', function () {
        $('#add-files-modal .modal-content').load('/add-documents/{{$project->external_id}}' + '/project');
        $('#add-files-modal').modal('show');
    });
    @endif
</script>

@endpush
