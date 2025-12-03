<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Rats\Zkteco\Lib\ZKTeco;

class GetZKUsers extends Command
{
    protected $signature = 'zk:getusers';
    protected $description = 'Pull all users from ZKTeco device';

    public function handle()
    {
        $ip = '192.168.1.8'; 
        $zk = new ZKTeco($ip);

        $this->info("Connecting to ZKTeco device at $ip ...");

        if ($zk->connect()) {
            $users = $zk->getUser();
            $this->info("Total users: " . count($users));
            print_r($users);
        } else {
            $this->error("Cannot connect to device.");
        }
    }
}
