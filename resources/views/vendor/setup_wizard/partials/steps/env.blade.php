<div class="form-group">
    <textarea name="file_content" class="form-control">{{ old('file_content', $sampleContent) }}</textarea>

    <p class="help-block">{!! trans('setup_wizard::steps.env.view.help_text') !!}</p>
</div>