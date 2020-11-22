<script>
    $('#delete_avatar').click(function() {
        $('#avatar_image').remove();
        $('#input_avatar').append('<input type="file" name="image_path" id="avatar_image" onchange="loadPreview(this);">');
        $('#preview_avatar').attr('src', '/images/default_avatar.jpg');
    });
    function loadPreview(input, id) {
        id = id || '#preview_avatar';
        if (input.files && input.files[0]) {
            var reader = new FileReader();
 
            reader.onload = function (e) {
                $(id).attr('src', e.target.result)
            };
 
            reader.readAsDataURL(input.files[0]);

        } else {
            $('#preview_avatar').attr('src', '/images/default_avatar.jpg');
        }
    }
</script>