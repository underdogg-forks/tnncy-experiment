<?php

namespace app\Filament\Admin\Resources\Hostnames\Pages;

use app\Filament\Admin\Resources\Hostnames\HostnameResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHostnames extends ListRecords
{
    protected static string $resource = HostnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
