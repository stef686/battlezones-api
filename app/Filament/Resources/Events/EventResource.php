<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\RelationManagers\AttendeesRelationManager;
use App\Filament\Resources\Events\RelationManagers\CustomFieldsRelationManager;
use App\Filament\Resources\Events\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Events\RelationManagers\OrganisersRelationManager;
use App\Filament\Resources\Events\RelationManagers\RoundsRelationManager;
use App\Filament\Resources\Events\RelationManagers\ScoreTypesRelationManager;
use App\Filament\Resources\Events\RelationManagers\UpdatesRelationManager;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OrganisersRelationManager::class,
            AttendeesRelationManager::class,
            RoundsRelationManager::class,
            CustomFieldsRelationManager::class,
            DocumentsRelationManager::class,
            ScoreTypesRelationManager::class,
            UpdatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }
}
