@if(!user()->isGuest())
    <div class="mb-xl">
        <h5>{{ trans('common.roles') }} y Permisos</h5>
            <div class="text-muted text-small mb-s">Roles con acceso a este manual:</div>

            @php
                // Simplificar para evitar errores de clase
                $rolesWithPermissions = collect();
                try {
                    // Mostrar información básica de acceso sin consultar directamente los roles
                    $currentUser = user();
                    $userPermissions = [];
                    
                    // Verificar permisos del usuario actual para books
                    if (userCan('book-view-all') || userCan('book-view-own') || $currentUser->hasSystemRole('admin')) {
                        $userPermissions[] = 'Ver';
                    }
                    if (userCan('book-create-all') || $currentUser->hasSystemRole('admin')) {
                        $userPermissions[] = 'Crear libros';
                    }
                    if (userCan('book-update-all') || userCan('book-update-own') || $currentUser->hasSystemRole('admin')) {
                        $userPermissions[] = 'Editar';
                    }
                    if (userCan('book-delete-all') || userCan('book-delete-own') || $currentUser->hasSystemRole('admin')) {
                        $userPermissions[] = 'Eliminar';
                    }
                    if (userCan('page-create', $book) || $currentUser->hasSystemRole('admin')) {
                        $userPermissions[] = 'Crear páginas';
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
                    if (!$book->hasPermissions()) {
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
                                                @elseif($permission === 'Crear libros')
                                                    @icon('add') {{ $permission }}
                                                @elseif($permission === 'Crear páginas')
                                                    @icon('page') {{ $permission }}
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

<div class="mb-xl">
    <h5>{{ trans('common.details') }}</h5>
    <div class="blended-links">
        @include('entities.meta', ['entity' => $book, 'watchOptions' => $watchOptions, 'referenceCount' => $referenceCount])
        @if($book->hasPermissions())
            <div class="active-restriction">
                @if(userCan(\BookStack\Permissions\Permission::RestrictionsManage, $book))
                    <a href="{{ $book->getUrl('/permissions') }}" class="entity-meta-item">
                        @icon('lock')
                        <div>{{ trans('entities.books_permissions_active') }}</div>
                    </a>
                @else
                    <div class="entity-meta-item">
                        @icon('lock')
                        <div>{{ trans('entities.books_permissions_active') }}</div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endif
