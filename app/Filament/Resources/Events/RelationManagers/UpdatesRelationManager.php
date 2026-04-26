<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'updates';

    public function table(Table $table): Table
    {
        $schema = [
            TextInput::make('title')
                ->maxLength(255),
            RichEditor::make('body')
                ->required(),
            Select::make('user_id')
                ->relationship('author', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->label('Author'),
            DateTimePicker::make('published_at')
                ->required()
                ->default(now()),
            DateTimePicker::make('pinned_at'),
        ];

        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->sortable(),
                TextColumn::make('author.name')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('pinned_at')
                    ->dateTime()
                    ->sortable(),
            ])
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
