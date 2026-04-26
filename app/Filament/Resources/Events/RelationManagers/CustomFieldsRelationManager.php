<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Enums\CustomFieldType;
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

class CustomFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'customFields';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->sortable(),
                TextColumn::make('type')
                    ->sortable(),
                TextColumn::make('display_order')
                    ->sortable(),
            ])
            ->defaultSort('display_order')
            ->headerActions([
                CreateAction::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->required()
                            ->enum(CustomFieldType::class)
                            ->options(CustomFieldType::class),
                        TextInput::make('display_order')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->required()
                            ->enum(CustomFieldType::class)
                            ->options(CustomFieldType::class),
                        TextInput::make('display_order')
                            ->required()
                            ->numeric(),
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
