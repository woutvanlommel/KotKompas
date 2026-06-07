<?php

namespace App\Filament\Dashboard\Resources\Buildings;

use App\Filament\Dashboard\Resources\Buildings\Pages\CreateBuilding;
use App\Filament\Dashboard\Resources\Buildings\Pages\EditBuilding;
use App\Filament\Dashboard\Resources\Buildings\Pages\ListBuildings;
use App\Filament\Dashboard\Resources\Buildings\Schemas\BuildingForm;
use App\Filament\Dashboard\Resources\Buildings\Tables\BuildingsTable;
use App\Models\Building;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|\UnitEnum|null $navigationGroup = 'Beheer';

    protected static ?string $navigationLabel = 'Gebouwen';

    protected static ?string $modelLabel = 'Gebouw';

    protected static ?string $pluralModelLabel = 'Gebouwen';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return BuildingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BuildingsTable::configure($table);
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
            'index' => ListBuildings::route('/'),
            'create' => CreateBuilding::route('/create'),
            'edit' => EditBuilding::route('/{record}/edit'),
        ];
    }
}
