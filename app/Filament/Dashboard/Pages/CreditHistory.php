<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\CreditTransaction;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class CreditHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.dashboard.pages.credit-history';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $title = 'Transactiegeschiedenis';

    // Verschijnt niet apart in de navigatie — bereikbaar via "Bekijk alles" op de creditpagina.
    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('huurder') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CreditTransaction::query()->where('user_id', auth()->id())
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d M Y · H:i')
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Omschrijving')
                    ->formatStateUsing(fn ($state, CreditTransaction $record): string => $record->label()),

                TextColumn::make('amount_paid')
                    ->label('Betaald')
                    ->alignEnd()
                    ->placeholder('—')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? '€ '.number_format($state / 100, 2, ',', '.') : '—'),

                TextColumn::make('amount')
                    ->label('Aantal')
                    ->badge()
                    ->alignEnd()
                    ->formatStateUsing(fn (int $state): string => ($state > 0 ? '+' : '').number_format($state, 0, ',', '.'))
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable(),
            ])
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Nog geen transacties')
            ->emptyStateDescription('Zodra je credits koopt of gebruikt, verschijnen ze hier.')
            ->emptyStateIcon(Heroicon::OutlinedWallet);
    }
}
