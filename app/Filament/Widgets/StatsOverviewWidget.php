<?php

namespace App\Filament\Widgets;

use App\Models\Club;
use App\Models\Event;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count()),
            Stat::make('Total Clubs', Club::count()),
            Stat::make('Total Events', Event::count()),
            Stat::make('Events This Month', Event::where('starts_at', '>=', now()->startOfMonth())->count()),
        ];
    }
}
