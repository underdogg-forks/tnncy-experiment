<?php

namespace app\Filament\Admin\Resources\Hostnames\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HostnameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('website_id')
                    ->relationship('website', 'id')
                    ->default(null),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->default(null),
                TextInput::make('fqdn')
                    ->required(),
                TextInput::make('redirect_to')
                    ->default(null),
                Toggle::make('force_https')
                    ->required(),
                DateTimePicker::make('under_maintenance_since'),
            ]);
    }
}
