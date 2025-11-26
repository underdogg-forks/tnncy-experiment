<?php

namespace app\Filament\Tenant\Resources\Permissions\Pages;

use app\Filament\Tenant\Resources\Permissions\PermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;
}
