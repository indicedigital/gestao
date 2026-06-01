@extends('company.projects._shell', ['currentTab' => 'kanban'])

@section('title', 'Quadro — '.$project->name)

@section('project_tab')
    @include('company.projects.tabs.kanban')
@endsection
