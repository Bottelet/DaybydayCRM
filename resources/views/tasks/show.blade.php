@extends('layouts.master')
@section('content')
    @push('scripts')
        <script>
            $(document).ready(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
            $(document).ready(function () {
                var $btnSets = $('#responsive'),
                    $btnLinks = $btnSets.find('a');

                $btnLinks.click(function (e) {
                    e.preventDefault();
                    $(this).siblings('a.active').removeClass("active");
                    $(this).addClass("active");
                    var index = $(this).index();
                    $("div.user-menu>div.user-menu-content").removeClass("active");
                    $("div.user-menu>div.user-menu-content").eq(index).addClass("active");
                });
            });



            $(document).ready(function () {
                $("[rel='tooltip']").tooltip();

                $('.view').hover(
                    function () {
                        $(this).find('.caption').slideDown(250); //.fadeIn(250)
                    },
                    function () {
                        $(this).find('.caption').slideUp(250); //.fadeOut(205)
                    }
                );
            });
        </script>
    @endpush

    <div class="row">
        @include('partials.clientheader')
        @include('partials.userheader', ['changeUser' => false])
    </div>

    <div class="row">
        <div class="col-md-9">
            @include('partials.comments', ['subject' => $tasks])
        </div>
        <div class="col-md-3">
            <div class="tablet tablet--tabs tablet--height-fluid">
                <div class="tablet__head tablet__head__color-brand padding-15-sides">
                    <div class="tablet__head-toolbar">
                        <ul class="nav nav-tabs nav-tabs-line nav-tabs-line-brand nav-tabs-bold tablet-brand-color" role="tablist">
                            <li class="nav-item active">
                                <a class="nav-link text-white active" data-toggle="tab" href="#tab_information" role="tab">
                                    @lang('Information')
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" data-toggle="tab" href="#tab_activity" role="tab">
                                    @lang('Activity')
                                </a>
                            </li>
                            @if(Entrust::can('invoice-see') && $tasks->invoice)
                            <li class="nav-item">
                                <a class="nav-link text-white" data-toggle="tab" href="#tab_invoice_lines" role="tab">
                                    @lang('Hours')
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="tablet__body">
                    <div class="tab-content">

                        <div class="tab-pane fade active in" id="tab_information" role="tabpanel">
                            <div class="k-scroll ps ps--active-y" data-scroll="true" style="overflow: hidden;" data-mobile-height="350">
                                @include('tasks._sidebar')
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab_activity" role="tabpanel">
                            <div class="k-scroll ps ps--active-y" data-scroll="true" style="overflow: hidden;" data-mobile-height="350">
                                @include('tasks._timeline')
                            </div>
                        </div>
                        @if(Entrust::can('invoice-see') && $tasks->invoice)
                            <div class="tab-pane fade" id="tab_invoice_lines" role="tabpanel">
                                <div class="k-scroll ps ps--active-y" data-scroll="true" style="overflow: hidden;" data-mobile-height="350">
                                    @include('tasks._time_management')
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            <div class="tablet__footer">

            </div>
            </div>
            @if(Entrust::can('task-upload-files') && $filesystem_integration)
                <div id="document" class="tab-pane">
                    <div class="tablet">
                        <div class="tablet__head">
                            <div class="tablet__head-label">
                                <h3 class="tablet__head-title">{{ __('All Files') }}</h3>
                                <button id="add-files" style="
                                margin-left: 18rem !important;
                                border: 0;
                                padding: 0;
                                background: transparent;
                                font-size:2em;">
                                    <i class="icon ion-md-add-circle"></i>
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
                                                    <i class="fa fa-ellipsis-h text-3xl" style="font-size: 2.5em;"></i>
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
                @endif


        </div>

    </div>
    @if(Entrust::can('task-update-deadline'))
        <div class="modal fade" id="ModalUpdateDeadline" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">{{ __('Change deadline') }}</h4>
                    </div>

                    <div class="modal-body">

                        <form action="{{route('task.update.deadline', $tasks->external_id)}}" method="POST">
                            @method('PATCH')
                            @csrf
                            <div class="form-group">
                                <label for="deadline_date" class="control-label thin-weight">@lang('Change deadline')</label>
                                <input type="text" id="deadline_date" name="deadline_date" data-value="{{now()->addDays(3)}}" class="form-control">
                            </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default col-lg-6"
                                    data-dismiss="modal">{{ __('Close') }}</button>
                            <div class="col-lg-6">
                                <input type="submit" value="{{__('Update deadline')}}" class="btn btn-brand form-control closebtn">
                            </div>
                        </div>
                        </form>
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
@stop
@push('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#deadline_date').pickadate({
                hiddenName:true,
                format: "{{frontendDate()}}",
                formatSubmit: 'yyyy/mm/dd',
                closeOnClear: false,
            });

            @if(Entrust::can('task-upload-files') && $filesystem_integration)
            $('#add-files').on('click', function () {
                $('#add-files-modal .modal-content').load('/add-documents/{{$tasks->external_id}}' + '/task');
                $('#add-files-modal').modal('show');
            });
            @endif
        });

        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            function validateHhMm(inputField) {
                var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(inputField.value);

                return isValid;
            }

            function isNumberKey(evt) {
                var charCode = (evt.which) ? evt.which : event.keyCode;
                if (charCode)
                    return false;

                return true;
            }
        });

    </script>
@endpush
