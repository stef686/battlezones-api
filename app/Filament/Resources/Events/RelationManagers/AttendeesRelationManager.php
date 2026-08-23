<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Enums\Allegiance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendees';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('name')
                    ->label('Attendee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('allegiance')
                    ->badge()
                    ->sortable(),
                TextColumn::make('members.name')
                    ->label('Players')
                    ->badge(),
                TextColumn::make('checked_in_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Attendee name')
                            ->helperText('Leave blank for a single player, who competes under their own name.')
                            ->maxLength(255),
                        Select::make('allegiance')
                            ->options(Allegiance::class),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Attendee name')
                            ->maxLength(255),
                        // The API freezes allegiance once a Round is Live,
                        // organisers included; this is the repair path.
                        Select::make('allegiance')
                            ->options(Allegiance::class),
                    ]),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
