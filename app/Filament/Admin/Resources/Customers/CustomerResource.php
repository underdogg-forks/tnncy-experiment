<?php

namespace app\Filament\Admin\Resources\Customers;

use app\Filament\Admin\Resources\Customers\Pages\CreateCustomer;
use app\Filament\Admin\Resources\Customers\Pages\EditCustomer;
use app\Filament\Admin\Resources\Customers\Pages\ListCustomers;
use app\Filament\Admin\Resources\Customers\Schemas\CustomerForm;
use app\Filament\Admin\Resources\Customers\Tables\CustomersTable;
use App\Models\System\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
