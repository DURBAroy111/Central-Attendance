<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ZKListen extends Command
{
    protected $signature = 'zk:listen {port=4370}';
    protected $description = 'Listen for ZKTeco device connections on a TCP port';

    public function handle()
    {
        $port = (int)$this->argument('port');
        $address = '0.0.0.0';

        $this->info("Starting TCP listener on {$address}:{$port} ...");

        $errno = 0;
        $errstr = '';

        $server = @stream_socket_server("tcp://{$address}:{$port}", $errno, $errstr);

        if (!$server) {
            $this->error("Unable to start server: $errstr ($errno)");
            return Command::FAILURE;
        }

        stream_set_blocking($server, true);

        $this->info("Listening... (Ctrl + C to stop)");

        while (true) {
            $conn = @stream_socket_accept($server, -1);

            if ($conn === false) {
                continue;
            }

            $peer = stream_socket_get_name($conn, true);
            $this->info("Connection from {$peer}");

            $data = fread($conn, 8192);

            $this->info('Received bytes: ' . strlen($data));

            fwrite($conn, "OK\n");

            fclose($conn);
        }
    }
}
