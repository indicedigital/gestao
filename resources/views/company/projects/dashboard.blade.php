@extends('company.projects._shell', ['currentTab' => 'dashboard'])

@section('title', 'Dashboard — '.$project->name)

@section('project_tab')
    @include('company.projects.tabs.dashboard')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.initProjectDashboardChart === 'function') {
        window.initProjectDashboardChart();
    }
});
</script>
@endpush
