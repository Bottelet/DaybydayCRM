@if(Entrust::can('document-upload'))
    <form method="POST" id="uploadFiles" action="{{ $route }}">
        <div class="dropzone dz-default dz-message" id="dropzone-images">
            <span>@lang('Drop files here or click to upload')</span>
        </div>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <input type="submit" class="btn btn-md btn-brand movedown" value="{{__('Upload')}}" style="margin:1em;">
    </form>
@endif


<script>
    Dropzone.autoDiscover = false;
    $(document).ready(function () {

        var myDropzone = new Dropzone("#uploadFiles", {
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 5,
            maxFiles: 50,
            addRemoveLinks: true,
            previewsContainer: "#dropzone-images",
            clickable:'#dropzone-images',
            paramName: 'files',
            acceptedFiles: "image/*,application/pdf, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.openxmlformats-officedocument.spreadsheetml.template, application/vnd.openxmlformats-officedocument.presentationml.template, application/vnd.openxmlformats-officedocument.presentationml.slideshow, application/vnd.openxmlformats-officedocument.presentationml.presentation, application/vnd.openxmlformats-officedocument.presentationml.slide, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.openxmlformats-officedocument.wordprocessingml.template, application/vnd.ms-excel.addin.macroEnabled.12, application/vnd.ms-excel.sheet.binary.macroEnabled.12,text/rtf,text/plain,audio/*,video/*,.csv,.doc,.xls,.ppt,application/vnd.ms-powerpoint,.pptx",

        });
        $('input[type="submit"]').on("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (myDropzone.getQueuedFiles().length > 0) {
                myDropzone.processQueue();

            }

        });

        myDropzone.on("success", function(file, response) {
            window.location.href = ("/{{$type}}" + "s/" + response)
        });

        myDropzone.on("processing", function(file, response) {
            $('input[type="submit"]').attr("disabled", true);
        });
        myDropzone.on("error", function(file, response) {
            $('input[type="submit"]').attr("disabled", false);
        });
    });


</script>
