<?php

namespace App\Filament\Resources\Events\Tables;

use App\Enums\EventStatus;
use App\Models\GameSystem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (EventStatus $state): string => match ($state) {
                        EventStatus::Draft => 'gray',
                        EventStatus::Published => 'info',
                        EventStatus::Active => 'success',
                        EventStatus::Completed => 'warning',
                        EventStatus::Cancelled => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('gameSystem.name')
                    ->sortable(),
                TextColumn::make('club.name')
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('attendees_count')
                    ->counts('attendees')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(EventStatus::class),
                SelectFilter::make('game_system_id')
                    ->label('Game System')
                    ->options(fn () => GameSystem::pluck('name', 'id')->all()),
                Filter::make('starts_at')
                    ->schema([
                        DatePicker::make('starts_from'),
                        DatePicker::make('starts_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['starts_from'], fn (Builder $query, $date) => $query->whereDate('starts_at', '>=', $date))
                            ->when($data['starts_until'], fn (Builder $query, $date) => $query->whereDate('starts_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
