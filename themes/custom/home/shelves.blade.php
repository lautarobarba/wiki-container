@extends('layouts.tri')

@section('body')
    @include('shelves.parts.list', ['shelves' => $shelves, 'view' => $view])
@stop

@section('left')
    @include('home.parts.sidebar')
@stop

@section('right')
    <div class="actions mb-xl">
        <h5>{{ trans('common.actions') }}</h5>
        <div class="icon-list text-link">
            @if(user()->can('bookshelf-create-all'))
                <a href="{{ url("/create-shelf") }}" class="icon-list-item">
                    <span>@icon('add')</span>
                    <span>{{ trans('entities.shelves_new_action') }}</span>
                </a>
            @endif

            {{-- Roles que tienen permiso para ver esta pagina --}}
            {{-- <div class="mb-xl">
                <h5>{{ trans('common.roles') }} con Permisos</h5>
                <div class="text-muted text-small mb-s">Roles que pueden ver estantes:</div>
                <div class="text-small blended-links">
                    @php
                        // Obtener roles que tienen permisos para ver estantes
                        $rolesWithShelfPermission = collect();
                        try {
                            $rolesWithShelfPermission = \BookStack\Auth\Role::query()
                                ->where(function($query) {
                                    $query->whereHas('permissions', function($subQuery) {
                                        $subQuery->where('name', 'LIKE', '%bookshelf%')
                                                 ->where('name', 'LIKE', '%view%');
                                    })
                                    ->orWhere('system_name', 'admin')
                                    ->orWhere('system_name', 'public');
                                })
                                ->orderBy('display_name')
                                ->get();
                        } catch (\Exception $e) {
                            // En caso de error, solo mostrar roles básicos
                            $rolesWithShelfPermission = \BookStack\Auth\Role::query()
                                ->whereIn('system_name', ['admin', 'public'])
                                ->get();
                        }
                    @endphp
                    
                    @if($rolesWithShelfPermission->count() > 0)
                        <div class="grid gap-xs">
                            @foreach($rolesWithShelfPermission as $role)
                                <div class="entity-meta-item">
                                    @icon('user')
                                    <div>
                                        <strong>{{ $role->display_name }}</strong>
                                        @if($role->description)
                                            <div class="text-muted text-small">{{ $role->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">
                            @icon('lock')
                            <span>Solo administradores tienen acceso</span>
                        </div>
                    @endif
                </div>
            </div> --}}

            @include('entities.view-toggle', ['view' => $view, 'type' => 'bookshelves'])
            <a href="{{ url('/tags') }}" class="icon-list-item">
                <span>@icon('tag')</span>
                <span>{{ trans('entities.tags_view_tags') }}</span>
            </a>
            {{-- @include('home.parts.expand-toggle', ['classes' => 'text-link', 'target' => '.entity-list.compact .entity-item-snippet', 'key' => 'home-details']) --}}
            @include('common.dark-mode-toggle', ['classes' => 'icon-list-item text-link'])
        </div>
    </div>
@stop
