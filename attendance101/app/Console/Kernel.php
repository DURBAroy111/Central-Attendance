<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ZKListen::class,
        \App\Console\Commands\GetZKLogs::class,      
        \App\Console\Commands\GetZKUsers::class,     
        \App\Console\Commands\ClearZKLogs::class,    
        \App\Console\Commands\DeviceInfo::class,     
    ];

   
    protected function schedule(Schedule $schedule)
    {
  
    }

    
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
