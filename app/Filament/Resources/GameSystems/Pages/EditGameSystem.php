<?php

namespace App\Filament\Resources\GameSystems\Pages;

use App\Filament\Resources\GameSystems\GameSystemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGameSystem extends EditRecord
{
    protected static string $resource = GameSystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
