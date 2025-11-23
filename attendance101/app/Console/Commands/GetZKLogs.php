<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Rats\Zkteco\Lib\ZKTeco;

class GetZKLogs extends Command
{
    protected $signature = 'zk:getlogs';
    protected $description = 'Pull all attendance logs from ZKTeco device';

    public function handle()
    {
        $ip = '192.168.1.237'; 
        $zk = new ZKTeco($ip);

        $this->info("Connecting to ZKTeco device at $ip ...");

        if ($zk->connect()) {
            $logs = $zk->getAttendance();
            $this->info("Total logs: " . count($logs));
            print_r($logs);
        } else {
            $this->error("Cannot connect to device.");
        }
    }
}
