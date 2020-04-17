<?php

namespace App\Command;

use App\Services\WebSocketMessaging;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class StartWebSocketServerCommand extends Command
{
    protected static $defaultName = 'app:start-web-socket-server';

    protected function configure()
    {
        $this
            ->setDescription('Start the web socket server')
            ->setHelp('This command starts the web socket server on port 8081')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketMessaging()
                )
            ),
            8081
        );

        $output->writeln('Web socket server started');
        $server->run();
        $output->writeln('Web socket server closed');
    }
}
