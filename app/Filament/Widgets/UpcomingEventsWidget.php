<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingEventsWidget extends TableWidget
{
    protected static ?string $heading = 'Upcoming Events';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Event::query()
                ->where('starts_at', '>', now())
                ->orderBy('starts_at')
                ->limit(5))
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('gameSystem.name')
                    ->label('Game System'),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
