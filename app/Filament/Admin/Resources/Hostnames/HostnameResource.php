<?php

namespace app\Filament\Admin\Resources\Hostnames;

use app\Filament\Admin\Resources\Hostnames\Pages\CreateHostname;
use app\Filament\Admin\Resources\Hostnames\Pages\EditHostname;
use app\Filament\Admin\Resources\Hostnames\Pages\ListHostnames;
use app\Filament\Admin\Resources\Hostnames\Schemas\HostnameForm;
use app\Filament\Admin\Resources\Hostnames\Tables\HostnamesTable;
use App\Models\System\Hostname;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HostnameResource extends Resource
{
    protected static ?string $model = Hostname::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return HostnameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HostnamesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHostnames::route('/'),
            'create' => CreateHostname::route('/create'),
            'edit' => EditHostname::route('/{record}/edit'),
        ];
    }
}
