@if(!user()->isGuest())
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
@endif

<div id="actions" class="actions mb-xl">
    <h5>{{ trans('common.actions') }}</h5>
    <div class="icon-list text-link">

        @if(userCan(\BookStack\Permissions\Permission::BookCreateAll) && userCan(\BookStack\Permissions\Permission::BookshelfUpdate, $shelf))
            <a href="{{ $shelf->getUrl('/create-book') }}" data-shortcut="new" class="icon-list-item">
                <span class="icon">@icon('add')</span>
                <span>{{ trans('entities.books_new_action') }}</span>
            </a>
        @endif

        @include('entities.view-toggle', ['view' => $view, 'type' => 'bookshelf'])
        @include('common.dark-mode-toggle', ['classes' => 'icon-list-item text-link'])

        <hr class="primary-background">

        @if(userCan(\BookStack\Permissions\Permission::BookshelfUpdate, $shelf))
            <a href="{{ $shelf->getUrl('/edit') }}" data-shortcut="edit" class="icon-list-item">
                <span>@icon('edit')</span>
                <span>{{ trans('common.edit') }}</span>
            </a>
        @endif

        @if(userCan(\BookStack\Permissions\Permission::RestrictionsManage, $shelf))
            <a href="{{ $shelf->getUrl('/permissions') }}" data-shortcut="permissions" class="icon-list-item">
                <span>@icon('lock')</span>
                <span>{{ trans('entities.permissions') }}</span>
            </a>
        @endif

        @if(userCan(\BookStack\Permissions\Permission::BookshelfDelete, $shelf))
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