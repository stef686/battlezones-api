<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Actions\Events\DeleteAttendee;
use App\Enums\Allegiance;
use App\Models\EventAttendee;
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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendees';

    /**
     * Deletes, single and bulk, go through DeleteAttendee as the API's do, so
     * a paired Attendee is refused and its Avatar file cleaned up.
     */
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
                DeleteAction::make()
                    ->using(function (DeleteAction $action, EventAttendee $record, DeleteAttendee $deleteAttendee): bool {
                        try {
                            $deleteAttendee->execute($record);
                        } catch (ValidationException $refusal) {
                            $action->failureNotificationTitle($refusal->errors()['attendee'][0]);

                            return false;
                        }

                        return true;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(function (DeleteBulkAction $action, Collection $records, DeleteAttendee $deleteAttendee): void {
                            $refused = new EloquentCollection();

                            foreach ($records->ensure(EventAttendee::class) as $attendee) {
                                try {
                                    $deleteAttendee->execute($attendee);
                                } catch (ValidationException) {
                                    $refused->push($attendee);
                                    $action->reportBulkProcessingFailure(
                                        'paired',
                                        function () use (&$refused): string {
                                            return 'Already paired, so not deleted: '.$refused->map(fn (EventAttendee $refusedAttendee): string => $refusedAttendee->displayName())->implode(', ').'. Drop them instead.';
                                        },
                                    );
                                }
                            }

                            $refused->loadMissing('memberships.user');
                        }),
                ]),
            ]);
    }
}
