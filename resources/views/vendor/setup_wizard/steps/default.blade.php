@extends('setup_wizard::layouts.wizard')

@section('page.title')
    {{ trans('setup_wizard::steps.' . $currentStep->getId() . '.title') }}
@endsection

@section('wizard.header')
    <h1 class="sw-step-title">{!! trans('setup_wizard::steps.' . $currentStep->getId() . '.title') !!}</h1>
@endsection

@section('wizard.breadcrumb')
    @include('setup_wizard::partials.breadcrumb')
@endsection

@section('wizard.errors')
    @if ($errors->has())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
@endsection

@section('wizard.description')
    <h2>{!! trans('setup_wizard::steps.' . $currentStep->getId() . '.description') !!}</h2>
@endsection

@section('wizard.form')
    @include('setup_wizard::partials.steps.' . $currentStep->getId(), $currentStep->getFormData())
@endsection

@section('wizard.navigation')
    @include('setup_wizard::partials.navigation')
@endsection
