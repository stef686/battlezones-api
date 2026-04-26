<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StandingsRelationManager extends RelationManager
{
    protected static string $relationship = 'standings';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('position')
            ->columns([
                TextColumn::make('position')
                    ->sortable(),
                TextColumn::make('attendee.user.name')
                    ->label('Attendee')
                    ->sortable(),
            ]);
    }
}
