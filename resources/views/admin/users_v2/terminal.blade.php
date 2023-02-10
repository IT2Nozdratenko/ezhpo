@extends('layouts.app')

@section('title', 'Терминалы')
@section('sidebar', 1)
@php
    $points = \App\Town::with(['pvs'])->get();
    $roles = \App\Role::whereNull('deleted_at')->get();
    $all_permissions = \Spatie\Permission\Models\Permission::orderBy('guard_name')->get();

    $current_user_permissions = [
            'permission_to_edit' => user()->access('employee_update'),
            'permission_to_view' => user()->access('employee_read'),
            'permission_to_create' => user()->access('employee_create'),
            'permission_to_delete' => user()->access('employee_delete'),
            'permission_to_trash' => user()->access('employee_trash'),
        ];
    $permissionToTrashView  = user()->access('employee_trash');
@endphp
@section('content')
    <admin-terminals-index
            :roles='@json($roles)'
            :deleted='{{ request()->get('deleted', 0) }}'
            :current_user_permissions='@json($current_user_permissions)'
            :all_permissions='@json($all_permissions)'
            :points='@json($points->map( function ($q) {
                $res['label'] = $q->name;
                foreach ($q->pvs as $pv){
                    $res['options'][] = ['value' => $pv['id'], 'text' => $pv['name']];
                }
                return $res;
            }))'
            :fields='@json($fields)'
    >
        <div class="card mb-3">
            <div class="card-body">
                @if($current_user_permissions['permission_to_view'])
                    <form action="" class="row" method="GET">
                        @isset($_GET['deleted'])
                            <input type="hidden" value="1" name="deleted">
                        @endif

                        <div class="col-lg-3">
                            @include('templates.elements_field', [
                                'v' => [
                                    'type' => 'select',
                                    'values' => 'User',
                                    'getField' => 'name',
                                    'getFieldKey' => 'hash_id',
                                    'multiple' => 1,
                                    'concat' => 'hash_id'
                                ],
                                'model' => 'User',
                                'k' => 'hash_id',
                                'is_required' => '',
                                'default_value' => request()->get('hash_id')
                            ])
                        </div>

                        <div class="col-lg-3 form-group">
                            @include('profile.ankets.components.pvs', [
                                'defaultShowPvs' => 1,
                                'classesPvs' => 'form-control',
                                'points' => $points->toArray(),
                                'roles' => $roles
                            ])
                        </div>

                        <div class="col-lg-2 form-group">
                            <input type="submit" class="btn btn-success btn-sm" value="Поиск">
                            <a href="{{ route('users') }}" class="btn btn-danger btn-sm">Сбросить</a>
                        </div>

                    </form>
                @endif
            </div>
        </div>
    </admin-terminals-index>
@endsection
