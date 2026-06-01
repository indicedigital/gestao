@extends('company.projects._shell', ['currentTab' => 'team'])

@section('title', 'Time — '.$project->name)

@section('project_tab')
    @include('company.projects.tabs.team')
@endsection
