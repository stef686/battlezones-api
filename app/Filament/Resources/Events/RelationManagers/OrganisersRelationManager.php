<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Enums\EventOrganiserRole;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganisersRelationManager extends RelationManager
{
    protected static string $relationship = 'organisers';

    protected static ?string $inverseRelationship = 'organisedEvents';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => EventOrganiserRole::from($state)->label()),
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role')
                            ->options(EventOrganiserRole::class)
                            ->default(EventOrganiserRole::Organiser)
                            ->required(),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
