@extends('layouts.master')
@section('heading')
{{ __('Integrations') }}
@stop

@section('content')

<div class="row">
<div class="col-sm-12">
    <h4>{{ __('Billing integrations')}}</h4>
        <p>
        @if($billing_integration)
            {{ __('Connected with') }} {{ class_basename($billing_integration)}}
        @endif
        </p>

    </div>

    <div class="col-sm-12 movedown">
    <br>
        <h4>{{ __('Filesystem integrations')}}</h4>
        @if($filesystem_integration)
        <p>
            {{ __('Connected with') }} {{ class_basename($filesystem_integration->name) }}
        </p>
        @endif
        <div class="col-sm-4 movedown">
        <img src="imagesIntegration/dropbox-logo.svg" width="60%" align="center" alt=""> <br>
        @if($filesystem_integration && $filesystem_integration->name == \App\Services\Storage\Dropbox::class)
            <form action="{{route('integration.revoke-access')}}" method="POST">
                                    {{ csrf_field() }}
                    <input type="submit" value="Unlink" class="btn btn-warning movedown">
            </form>
        @else
            <a href="{{$dropbox_auth_url}}"><button {{$filesystem_integration ? 'disabled' : ''}} class="btn btn-md btn-brand movedown">Link Dropbox</button></a>
        @endif
        </div>
        <div class="col-sm-4 movedown">
        <img src="imagesIntegration/google-drive-logo.png" width="70%" align="center" alt=""> <br>
        @if($filesystem_integration && $filesystem_integration->name == \App\Services\Storage\GoogleDrive::class)
             <form action="{{route('integration.revoke-access')}}" method="POST">
                    {{ csrf_field() }}
                    <input type="submit" value="Unlink" class="btn btn-warning movedown">
            </form>

        @else
            <a href="{{$google_drive_auth_url}}"><button {{$filesystem_integration ? 'disabled' : ''}} class="btn btn-md btn-brand movedown">Link Google Drive</button></a>
        @endif
        </div>
    </div>
</div>

@stop

@if(!empty(Session::get('organizations')))
    @push('scripts')
    <script>
        $(function() {
            $('#dinero-organization-modal').modal('show');
        });
    </script>
    @endpush
@endif
