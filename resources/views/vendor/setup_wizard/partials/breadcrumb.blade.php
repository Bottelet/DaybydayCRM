<ol>
    @php($isCurrent = true)
    @foreach($allSteps as $id => $step)
        @php($cssClass = ($isCurrent ? 'sw-current' : ''))

        <li class="sw-step-divider {{ $cssClass }}"></li>
        <li class="sw-step {{ $cssClass }}">{!! trans('setup_wizard::steps.' . $id . '.breadcrumb') !!}</li>

        @if(\SetupWizard::isCurrent($id))
            @php($isCurrent = false)
        @endif
    @endforeach

    <li class="sw-step-divider"></li>
</ol>