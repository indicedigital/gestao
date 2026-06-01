@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" id="project-tab-root" data-current-tab="{{ $currentTab ?? '' }}">
    @include('company.projects._header')

    <div id="project-tab-panel" class="project-tab-panel">
        @yield('project_tab')
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/project-tabs.js') }}" defer></script>
@endpush
