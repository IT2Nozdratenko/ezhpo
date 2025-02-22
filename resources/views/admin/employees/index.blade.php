@extends('layouts.app')

@section('title', 'Сотрудники')
@section('sidebar', 1)

@php
    $selectedEmployees = request()->get('employee_id') ?? [];
    $selectedPoints = request()->get('point_id') ?? [];
    $selectedRole = request()->get('role') ?? null;
    $selectedEmail = request()->get('email') ?? null;
@endphp

@section('content')
    <employees-index-widget></employees-index-widget>
@endsection

@push('setup-scripts')
    <script>
      window.PAGE_SETUP = {
        fields: @json($fields),
        isTrashMode: {{ request()->get('deleted', 0)  }},
        currentUserPermissions: @json($currentUserPermissions),

        // modal
        rolesModalOptions: @json($rolesModalOptions),
        pointsModalOptions: @json($pointsModalOptions),
        allPermissions: @json($allPermissions),
        clientRoleId: @json($clientRoleId),
        headOperatorSdpoRoleId: @json($headOperatorSdpoRoleId),

        // filter
        rolesFilterOptions: @json($rolesFilterOptions),
        pointsFilterOptions: @json($pointsFilterOptions),
        employeesFilterOptions: @json($employeesFilterOptions),

        // filter values
        selectedEmployeeIds: @json($selectedEmployees),
        selectedPointIds: @json($selectedPoints),
        selectedRoleId: @json($selectedRole),
        selectedEmail: @json($selectedEmail),

        // logs
        LOGS_MODAL: {
          tableDataUrl: '{{ route('logs.list-model') }}',
          mapDataUrl: '{{ route('logs.list-model-map') }}',
          model: '{{ 'users' }}',
        },
        MODEL_SEARCHER: {
          tableDataUrl: '{{ route('searchElement') }}',
        },
      };
    </script>
@endpush
