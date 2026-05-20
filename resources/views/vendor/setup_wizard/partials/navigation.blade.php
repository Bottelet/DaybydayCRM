@unless (SetupWizard::isFirst())
    <input type="submit" name="wizard-action-back" value="{{ trans('setup_wizard::views.nav.back') }}" class="btn btn-default btn-back">
@endunless

@if (SetupWizard::isLast())
    <input type="submit" name="wizard-action-next" value="{{ trans('setup_wizard::views.nav.done') }}" class="btn btn-primary">
@else
    <input type="submit" name="wizard-action-next" value="{{ trans('setup_wizard::views.nav.next') }}" class="btn btn-primary">
@endif