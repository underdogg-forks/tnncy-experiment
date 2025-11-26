<?php

namespace app\Filament\Admin\Resources\Hostnames\Pages;

use app\Filament\Admin\Resources\Hostnames\HostnameResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHostname extends CreateRecord
{
    protected static string $resource = HostnameResource::class;
}
