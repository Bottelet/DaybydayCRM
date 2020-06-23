<div id="document" class="tab-pane">
<div class="tablet">
    <div class="tablet__head">
        <div class="tablet__head-label">
            <h3 class="tablet__head-title">{{ __('All Documents') }}</h3>
        </div>
    </div>
    <div class="tablet__body">
        @if($documents->count() == 0)
            <div class="tablet__item">
                <div class="tablet__item__pic">
                    <p class="title">@lang('No files')</p>
                </div>
            </div>
        @endif
        <div class="tablet__items">
            @foreach($documents as $document)
                <div class="tablet__item">
                    <div class="tablet__item__pic">
                        <img src="{{url('images/doc-icon.svg')}}" alt="">
                    </div>
                    <div class="tablet__item__info">

                        <a href="{{ route('document.view', $document->external_id) }}" class="tablet__item__title" target="_blank">{{$document->original_filename}}</a>
                        <div class="tablet__item__description">
                            {{$document->size}} MB
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
                                        <a href=" {{ route('document.view', $document->external_id) }}" target="_blank" class="nav-link">
                                            <i class="icon ion-md-eye"></i>
                                            <span class="nav-link-text">@lang('View')</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href=" {{ route('document.download', $document->external_id) }}" target="_blank" class="nav-link">
                                            <i class="icon ion-md-cloud-download"></i>
                                            <span class="nav-link-text">@lang('Download')</span>
                                        </a>
                                    </li>
                                    @if(Entrust::can('document-delete'))

                                        <li class="nav-item">
                                            <span class="nav-link">
                                                <i class="icon ion-md-trash"></i>
                                                <form method="POST" action="{{action('DocumentsController@destroy', $document->external_id)}}">
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
    <div class="tablet__footer">
        @if(Entrust::can('document-upload'))
                <form method="POST" action="{{ route('document.upload', $client->external_id) }}" class="dropzone" id="dropzone"
                      files="true" data-dz-removea
                      enctype="multipart/form-data">

                    <meta name="csrf-token" content="{{ csrf_token() }}">
                </form>
        @endif
    </div>
</div>
</div>
