<?php

namespace App\Filament\Dashboard\Resources\Buildings;

use App\Filament\Dashboard\Resources\Buildings\Pages\ListBuildings;
use App\Filament\Dashboard\Resources\Buildings\Pages\ViewBuilding;
use App\Filament\Dashboard\Resources\Buildings\RelationManagers\RoomsRelationManager;
use App\Filament\Dashboard\Resources\Buildings\Schemas\BuildingForm;
use App\Filament\Dashboard\Resources\Buildings\Tables\BuildingsTable;
use App\Models\Building;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Beheer';

    protected static ?string $navigationLabel = 'Gebouwen';

    protected static ?string $modelLabel = 'Gebouw';

    protected static ?string $pluralModelLabel = 'Gebouwen';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('verhuurder') ?? false;
    }

    /**
     * Owner scope at resource level: applies to the list, record binding
     * and — critically — global search, so a landlord can never enumerate
     * another landlord's building name/city via the topbar search.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('landlord_id', auth()->id());
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'city'];
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Stad' => $record->city,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = auth()->id();
        $count = static::getModel()::where('landlord_id', $userId)->count();

        return (string) $count ?: null;
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
            RoomsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBuildings::route('/'),
            // 'create' => CreateBuilding::route('/create'),
            'view' => ViewBuilding::route('/{record}'),
            // 'edit' => EditBuilding::route('/{record}/edit'),
        ];
    }
}
