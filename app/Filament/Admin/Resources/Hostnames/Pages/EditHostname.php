<?php

namespace app\Filament\Admin\Resources\Hostnames\Pages;

use app\Filament\Admin\Resources\Hostnames\HostnameResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHostname extends EditRecord
{
    protected static string $resource = HostnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
