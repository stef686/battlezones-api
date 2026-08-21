<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Enums\SortDirection;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScoreTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'scoreTypes';

    public function table(Table $table): Table
    {
        $schema = [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('slug')
                ->required()
                ->maxLength(255),
            Select::make('sort_direction')
                ->required()
                ->enum(SortDirection::class)
                ->options(SortDirection::class),
            Toggle::make('is_derived')
                ->default(false),
            TextInput::make('ranking_order')
                ->numeric()
                ->nullable(),
            TextInput::make('win_points')
                ->numeric()
                ->nullable(),
            TextInput::make('draw_points')
                ->numeric()
                ->nullable(),
            TextInput::make('loss_points')
                ->numeric()
                ->nullable(),
            TextInput::make('display_order')
                ->required()
                ->numeric()
                ->default(0),
        ];

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->sortable(),
                TextColumn::make('slug')
                    ->sortable(),
                TextColumn::make('sort_direction')
                    ->sortable(),
                IconColumn::make('is_derived')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('ranking_order')
                    ->sortable(),
                TextColumn::make('display_order')
                    ->sortable(),
            ])
            ->defaultSort('display_order')
            ->headerActions([
                CreateAction::make()
                    ->schema($schema),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema($schema),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
