@extends('layouts.app')

@section('content')
    <livewire:form-editor :form="$form" wire:key="form-editor-{{ $form->id }}" />
@endsection
