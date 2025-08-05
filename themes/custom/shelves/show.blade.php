@extends('layouts.tri')

@push('social-meta')
    <meta property="og:description" content="{{ Str::limit($shelf->description, 100, '...') }}">
    @if($shelf->cover)
        <meta property="og:image" content="{{ $shelf->getBookCover() }}">
    @endif
@endpush

@include('entities.body-tag-classes', ['entity' => $shelf])

@section('body')

    <div class="mb-s print-hidden">
        @include('entities.breadcrumbs', ['crumbs' => [
            $shelf,
        ]])
    </div>

    <main class="card content-wrap">

        <div class="flex-container-row wrap v-center">
            <h1 class="flex fit-content break-text">{{ $shelf->name }}</h1>
            <div class="flex"></div>
            <div class="flex fit-content text-m-right my-m ml-m">
                @include('common.sort', $listOptions->getSortControlData())
            </div>
        </div>

        <div class="book-content">
            <div class="text-muted break-text">{!! $shelf->descriptionHtml() !!}</div>
            @if(count($sortedVisibleShelfBooks) > 0)
                @if($view === 'list')
                    <div class="entity-list">
                        @foreach($sortedVisibleShelfBooks as $book)
                            @include('books.parts.list-item', ['book' => $book])
                        @endforeach
                    </div>
                @else
                    <div class="grid third">
                        @foreach($sortedVisibleShelfBooks as $book)
                            @include('entities.grid-item', ['entity' => $book])
                        @endforeach
                    </div>
                @endif
            @else
                <div class="mt-xl">
                    <hr>
                    <p class="text-muted italic mt-xl mb-m">{{ trans('entities.shelves_empty_contents') }}</p>
                    <div class="icon-list inline block">
                        @if(userCan('book-create-all') && userCan('bookshelf-update', $shelf))
                            <a href="{{ $shelf->getUrl('/create-book') }}" class="icon-list-item text-book">
                                <span class="icon">@icon('add')</span>
                                <span>{{ trans('entities.books_create') }}</span>
                            </a>
                        @endif
                        @if(userCan('bookshelf-update', $shelf))
                            <a href="{{ $shelf->getUrl('/edit') }}" class="icon-list-item text-bookshelf">
                                <span class="icon">@icon('edit')</span>
                                <span>{{ trans('entities.shelves_edit_and_assign') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </main>

@stop

@section('left')

    @if($shelf->tags->count() > 0)
        <div id="tags" class="mb-xl">
            @include('entities.tag-list', ['entity' => $shelf])
        </div>
    @endif

    {{-- <div id="details" class="mb-xl">
        <h5>{{ trans('common.details') }}</h5>
        <div class="blended-links">
            @include('entities.meta', ['entity' => $shelf, 'watchOptions' => null])
            @if($shelf->hasPermissions())
                <div class="active-restriction">
                    @if(userCan('restrictions-manage', $shelf))
                        <a href="{{ $shelf->getUrl('/permissions') }}" class="entity-meta-item">
                            @icon('lock')
                            <div>{{ trans('entities.shelves_permissions_active') }}</div>
                        </a>
                    @else
                        <div class="entity-meta-item">
                            @icon('lock')
                            <div>{{ trans('entities.shelves_permissions_active') }}</div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div> --}}

    {{-- @if(count($activity) > 0)
        <div id="recent-activity" class="mb-xl">
            <h5>{{ trans('entities.recent_activity') }}</h5>
            @include('common.activity-list', ['activity' => $activity])
        </div>
    @endif --}}
@stop

@section('right')
<div class="mb-xl">
    <h5>{{ trans('common.roles') }} y Permisos</h5>
    <div class="text-muted text-small mb-s">Roles con acceso a este sistema:</div>

        @php
            // Simplificar para evitar errores de clase
            $rolesWithPermissions = collect();
            try {
                // Mostrar información básica de acceso sin consultar directamente los roles
                $currentUser = user();
                $userPermissions = [];
                
                // Verificar permisos del usuario actual
                if (userCan('bookshelf-view-all') || userCan('bookshelf-view-own') || $currentUser->hasSystemRole('admin')) {
                    $userPermissions[] = 'Ver';
                }
                if (userCan('bookshelf-create-all') || $currentUser->hasSystemRole('admin')) {
                    $userPermissions[] = 'Crear manuales';
                }
                if (userCan('bookshelf-update-all') || userCan('bookshelf-update-own') || $currentUser->hasSystemRole('admin')) {
                    $userPermissions[] = 'Editar';
                }
                if (userCan('bookshelf-delete-all') || userCan('bookshelf-delete-own') || $currentUser->hasSystemRole('admin')) {
                    $userPermissions[] = 'Eliminar';
                }
                
                // Crear información del usuario actual
                if (!empty($userPermissions)) {
                    $userInfo = (object)[
                        'display_name' => $currentUser->name,
                        'system_name' => $currentUser->hasSystemRole('admin') ? 'admin' : 'user',
                        'permissions' => $userPermissions,
                        'is_admin' => $currentUser->hasSystemRole('admin'),
                        'is_current' => true
                    ];
                    $rolesWithPermissions->push($userInfo);
                }
                
                // Agregar información sobre acceso público si no hay restricciones
                if (!$shelf->hasPermissions()) {
                    $publicInfo = (object)[
                        'display_name' => 'Público',
                        'system_name' => 'public',
                        'permissions' => ['Ver'],
                        'is_admin' => false,
                        'is_current' => false
                    ];
                    $rolesWithPermissions->push($publicInfo);
                }
                
            } catch (\Exception $e) {
                // En caso de error, mostrar solo usuario actual
                $rolesWithPermissions = collect();
            }
        @endphp
        
        @if($rolesWithPermissions->count() > 0)
            <div class="text-small">
                @foreach($rolesWithPermissions as $role)
                    <div class="entity-meta-item mb-s">
                        <div class="flex-container-row">
                            <div class="flex fit-content">
                                @if($role->is_admin)
                                    @icon('user-star')
                                @elseif($role->system_name === 'public')
                                    @icon('globe')
                                @else
                                    @icon('user')
                                @endif
                            </div>
                            <div class="flex">
                                <div>
                                    <strong>{{ $role->display_name }}</strong>
                                    @if($role->is_admin)
                                        <span class="text-muted text-small">(Administrador)</span>
                                    @elseif($role->system_name === 'public')
                                        <span class="text-muted text-small">(Acceso público)</span>
                                    @elseif($role->is_current)
                                        <span class="text-muted text-small">(Tu acceso actual)</span>
                                    @endif
                                </div>
                                <div class="text-small text-muted mt-xs">
                                    @foreach($role->permissions as $permission)
                                        <span class="mr-xs">
                                            @if($permission === 'Ver')
                                                @icon('eye') {{ $permission }}
                                            @elseif($permission === 'Crear manuales')
                                                @icon('add') {{ $permission }}
                                            @elseif($permission === 'Editar')
                                                @icon('edit') {{ $permission }}
                                            @elseif($permission === 'Eliminar')
                                                @icon('delete') {{ $permission }}
                                            @else
                                                @icon('check') {{ $permission }}
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted text-small">
                @icon('lock')
                <span>Acceso restringido</span>
            </div>
        @endif
    </div> 

    <div class="actions mb-xl">
        <h5>{{ trans('common.actions') }}</h5>
        <div class="icon-list text-link">

            @if(userCan('book-create-all') && userCan('bookshelf-update', $shelf))
                <a href="{{ $shelf->getUrl('/create-book') }}" data-shortcut="new" class="icon-list-item">
                    <span class="icon">@icon('add')</span>
                    <span>{{ trans('entities.books_new_action') }}</span>
                </a>
            @endif

            @include('entities.view-toggle', ['view' => $view, 'type' => 'bookshelf'])
            
            @include('common.dark-mode-toggle', ['classes' => 'icon-list-item text-link'])

            <hr class="primary-background">

            @if(userCan('bookshelf-update', $shelf))
                <a href="{{ $shelf->getUrl('/edit') }}" data-shortcut="edit" class="icon-list-item">
                    <span>@icon('edit')</span>
                    <span>{{ trans('common.edit') }}</span>
                </a>
            @endif

            @if(userCan('restrictions-manage', $shelf))
                <a href="{{ $shelf->getUrl('/permissions') }}" data-shortcut="permissions" class="icon-list-item">
                    <span>@icon('lock')</span>
                    <span>{{ trans('entities.permissions') }}</span>
                </a>
            @endif

            @if(userCan('bookshelf-delete', $shelf))
                <a href="{{ $shelf->getUrl('/delete') }}" data-shortcut="delete" class="icon-list-item">
                    <span>@icon('delete')</span>
                    <span>{{ trans('common.delete') }}</span>
                </a>
            @endif

            @if(!user()->isGuest())
                <hr class="primary-background">
                @include('entities.favourite-action', ['entity' => $shelf])
            @endif

        </div>
    </div>
@stop
