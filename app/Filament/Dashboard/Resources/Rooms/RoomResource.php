<?php

namespace App\Filament\Dashboard\Resources\Rooms;

use App\Filament\Dashboard\Resources\Rooms\Pages\CreateRoom;
use App\Filament\Dashboard\Resources\Rooms\Pages\EditRoom;
use App\Filament\Dashboard\Resources\Rooms\Pages\ListRooms;
use App\Filament\Dashboard\Resources\Rooms\Schemas\RoomForm;
use App\Filament\Dashboard\Resources\Rooms\Tables\RoomsTable;
use App\Models\Room;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('verhuurder') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return RoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomsTable::configure($table);
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
            'index' => ListRooms::route('/'),
            'create' => CreateRoom::route('/create'),
            'edit' => EditRoom::route('/{record}/edit'),
        ];
    }
}
