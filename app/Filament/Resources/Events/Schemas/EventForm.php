<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\Country;
use App\Enums\EventStatus;
use App\Enums\PairingFormat;
use App\Models\Club;
use App\Models\GameSystem;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description'),
                Select::make('status')
                    ->options(EventStatus::class)
                    ->required(),
                Select::make('pairing_format')
                    ->options(PairingFormat::class)
                    ->required(),
                Select::make('game_system_id')
                    ->label('Game System')
                    ->options(fn () => GameSystem::pluck('name', 'id')->all())
                    ->required()
                    ->searchable(),
                Select::make('club_id')
                    ->label('Club')
                    ->options(fn () => Club::pluck('name', 'id')->all())
                    ->searchable(),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->required(),
                TextInput::make('venue_name')
                    ->maxLength(255),
                TextInput::make('venue_address')
                    ->maxLength(255),
                TextInput::make('venue_city')
                    ->maxLength(255),
                Select::make('venue_country')
                    ->options(Country::class),
                TextInput::make('max_attendees')
                    ->numeric()
                    ->minValue(1),
                Toggle::make('standings_visible')
                    ->default(false),
            ]);
    }
}
