@extends('company.projects._shell', ['currentTab' => 'show'])

@section('title', $project->name)

@section('project_tab')
    @include('company.projects.tabs.show')
@endsection
