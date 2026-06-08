<?php

namespace App\Filament\Dashboard\Resources\Buildings\RelationManagers;

use App\Filament\Dashboard\Resources\Rooms\Schemas\RoomForm;
use App\Filament\Dashboard\Resources\Rooms\Tables\RoomsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'rooms';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return RoomForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return RoomsTable::configure($table);
    }
}
