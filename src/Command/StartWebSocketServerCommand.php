<?php

namespace App\Command;

use App\Services\WebSocketMessaging;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

#[AsCommand(name: 'app:start-web-socket-server')]
class StartWebSocketServerCommand extends Command
{
    private $webSocketMessaging;

    public function __construct(WebSocketMessaging $webSocketMessaging)
    {
        $this->webSocketMessaging = $webSocketMessaging;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Start the web socket server')
            ->setHelp('This command starts the web socket server on port 8081')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $loop = \React\EventLoop\Loop::get();
        $this->webSocketMessaging->setLoop($loop);
        $socket = new \React\Socket\SocketServer('0.0.0.0:8081', [], $loop);
        $server = new IoServer(
            new HttpServer(
                new WsServer(
                    $this->webSocketMessaging
                )
            ),
            $socket,
            $loop
        );

        $output->writeln('Web socket server started');
        $server->run();
        $output->writeln('Web socket server closed');

        return self::SUCCESS;
    }
}
