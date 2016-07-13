<div class="form-group">
    {{ Form::textarea('file_content', $sampleContent, [
        'class' => 'form-control'
    ]) }}



    <p class="help-block">{!! trans('setup_wizard::steps.env.view.help_text') !!}</p>
</div>