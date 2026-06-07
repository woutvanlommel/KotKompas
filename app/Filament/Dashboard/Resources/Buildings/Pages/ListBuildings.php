<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingResource::class;

    protected ?string $heading = '';

    protected string $view = 'filament.dashboard.pages.buildings.list';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nieuw gebouw'),
        ];
    }

    protected function getQuery(): Builder
    {
        return parent::getQuery()->where('landlord_id', auth()->id());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make()
                    ->schema([
                        Stack::make([
                            TextColumn::make('name')
                                ->label('Naam')
                                ->weight('bold')
                                ->size('lg')
                                ->url(fn($record) => static::$resource::getUrl('view', ['record' => $record])),
                            TextColumn::make('description')
                                ->label('Beschrijving')
                                ->size('sm')
                                ->color('gray'),
                            TextColumn::make('fullAddress')
                                ->label('Adres')
                                ->size('sm')
                                ->color('gray'),
                        ])
                            ->space(),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),
            ])
            ->paginated([12, 24])
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
            ]);
    }
}
