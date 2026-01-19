<?php

declare(strict_types=1);

use DI\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use kuaukutsu\poc\migration\connection\PdoDriver;
use kuaukutsu\poc\migration\tools\PrettyConsoleOutput;
use kuaukutsu\poc\migration\Db;
use kuaukutsu\poc\migration\DbCollection;
use kuaukutsu\poc\migration\Migrator;
use kuaukutsu\poc\migration\MigratorInterface;
use kuaukutsu\poc\migration\example\presentation\DownCommand;
use kuaukutsu\poc\migration\example\presentation\FixtureCommand;
use kuaukutsu\poc\migration\example\presentation\InitCommand;
use kuaukutsu\poc\migration\example\presentation\RedoCommand;
use kuaukutsu\poc\migration\example\presentation\UpCommand;

use function DI\factory;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = new Container(
    [
        MigratorInterface::class => factory(
            fn(): MigratorInterface => new Migrator(
                dbCollection: new DbCollection(
                    new Db(
                        path: dirname(__DIR__, 3) . '/source/migrations/pgsql/main',
                        driver: new PdoDriver(
                            dsn: 'pgsql:host=postgres;port=5432;dbname=main',
                            username: 'postgres',
                            password: 'postgres',
                        )
                    ),
                    new Db(
                        path: dirname(__DIR__, 3) . '/source/migrations/mysql/main',
                        driver: new PdoDriver(
                            dsn: 'mysql:host=mysql;port=3306;dbname=main',
                            username: 'dbuser',
                            password: 'dbpassword',
                        )
                    )
                ),
                eventSubscribers: [
                    new PrettyConsoleOutput(),
                ],
            )
        ),
    ]
);

$console = new Application();
$console->setCommandLoader(
    new ContainerCommandLoader(
        $container,
        [
            'migrate:init' => InitCommand::class,
            'migrate:up' => UpCommand::class,
            'migrate:down' => DownCommand::class,
            'migrate:redo' => RedoCommand::class,
            'migrate:fixture' => FixtureCommand::class,
        ],
    )
);

try {
    exit($console->run());
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(Command::FAILURE);
}
