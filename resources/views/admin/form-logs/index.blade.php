@extends('layouts.app')

@section('title', 'Журнал действий с осмотрами')
@section('sidebar', 1)

@section('content')
    <form-logs-index />
@endsection

@push('setup-scripts')
    <script>
        window.PAGE_SETUP.usersOption = @json($users);

        window.PAGE_SETUP.modelsOption = @json($modelTypes);
        window.PAGE_SETUP.modelsMap = @json(array_column($modelTypes, 'text', 'id'))

        window.PAGE_SETUP.actionsOption = @json($actionTypes);
        window.PAGE_SETUP.actionsMap = @json(array_column($actionTypes, 'text', 'id'))

        window.PAGE_SETUP.fieldPromptsMap = @json($fieldPromptsMap);

        window.PAGE_SETUP.tableDataUrl = '/admin/form-logs/list';

        window.PAGE_SETUP.MODEL_SEARCHER = {
            tableDataUrl: '/elements/search'
        }
    </script>
@endpush


