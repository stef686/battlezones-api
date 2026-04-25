<?php

namespace App\Filament\Resources\GameSystems;

use App\Filament\Resources\GameSystems\Pages\CreateGameSystem;
use App\Filament\Resources\GameSystems\Pages\EditGameSystem;
use App\Filament\Resources\GameSystems\Pages\ListGameSystems;
use App\Filament\Resources\GameSystems\RelationManagers\FactionsRelationManager;
use App\Filament\Resources\GameSystems\Schemas\GameSystemForm;
use App\Filament\Resources\GameSystems\Tables\GameSystemsTable;
use App\Models\GameSystem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GameSystemResource extends Resource
{
    protected static ?string $model = GameSystem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    public static function form(Schema $schema): Schema
    {
        return GameSystemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GameSystemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGameSystems::route('/'),
            'create' => CreateGameSystem::route('/create'),
            'edit' => EditGameSystem::route('/{record}/edit'),
        ];
    }
}
