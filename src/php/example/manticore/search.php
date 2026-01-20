<?php

declare(strict_types=1);

use Manticoresearch\Client;
use Manticoresearch\Search;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require dirname(__DIR__) . '/vendor/autoload.php';

$client = new Client(
    [
        'host' => 'manticore',
        'port' => 9308,
    ]
);
$search = new Search($client);

$console = new Application();
$console
    ->register('search:json')
    ->setDescription('Поиск по индексу JSON')
    ->addArgument('query')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($search): int {
        $query = $input->getArgument('query');
        $results = $search
            ->setTable('books_json')
            ->search($query)
            ->get();

        $output->writeln("search: " . $query);
        foreach ($results as $doc) {
            $output->writeln('Document:' . $doc->getId());
            foreach ($doc->getData() as $field => $value) {
                $output->writeln($field . ": " . $value);
            }
        }

        return Command::SUCCESS;
    });

$console
    ->register('search:pg')
    ->setDescription('Поиск по индексу PgSQL')
    ->addArgument('query')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($search): int {
        $query = $input->getArgument('query');
        $results = $search
            ->setTable('books_pg_plain')
            ->match($query)
            ->get();

        $output->writeln("search: " . $query);
        foreach ($results as $doc) {
            $output->writeln('Document:' . $doc->getId());
            foreach ($doc->getData() as $field => $value) {
                $output->writeln($field . ": " . $value);
            }
        }

        return Command::SUCCESS;
    });

$console
    ->register('search:my')
    ->setDescription('Поиск по индексу MySQL')
    ->addArgument('query')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($search): int {
        $query = $input->getArgument('query');
        $results = $search
            ->setTable('books_my_plain')
            ->search($query)
            ->get();

        $output->writeln("search: " . $query);
        foreach ($results as $doc) {
            $output->writeln('Document:' . $doc->getId());
            foreach ($doc->getData() as $field => $value) {
                $output->writeln($field . ": " . $value);
            }
        }

        return Command::SUCCESS;
    });

try {
    exit($console->run());
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(Command::FAILURE);
}
