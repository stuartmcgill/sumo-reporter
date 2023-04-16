<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\CliCommand;

use DateTime;
use StuartMcGill\SumoReporter\DomainService\StreakDownloader;
use StuartMcGill\SumoReporter\Model\BashoDate;
use StuartMcGill\SumoReporter\Model\Streak;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'report:streaks',
    description: 'Displays winning and losing streaks.',
)]
class DownloadStreaks extends Command
{
    public function __construct(private readonly StreakDownloader $streakDownloader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $defaultDate = BashoDate::fromDateTime(new DateTime());

        $this
            ->addArgument(
                name: 'date',
                mode: InputArgument::OPTIONAL,
                description: 'Basho date in YYYY-MM format e.g. 2023-03',
                default: $defaultDate->format('Y-m'),
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Downloading wrestler streaks...');

        $date = $input->getArgument('date');

        [$winning, $losing] = $this->streakDownloader->download(
            year: (int)substr(string: $date, offset: 0, length: 4),
            month: (int)substr(string: $date, offset: 5, length: 2)
        );

        $io->section('Winning');
        $this->printStreaks($output, $winning);
        $io->newLine();

        $io->section('Losing');
        $this->printStreaks($output, $losing);
        $io->newLine();

        $io->success('Successfully completed');

        return Command::SUCCESS;
    }

    /** @param list<Streak> $streaks */
    private function printStreaks(OutputInterface $output, array $streaks): void
    {
        $table = new Table($output);
        $table
            ->setHeaders(['Name', 'Rank', 'Type', 'Streak size', 'Still running?'])
            ->setRows(array_map(
                callback: static fn (Streak $streak) => [
                    $streak->wrestler->name,
                    $streak->wrestler->rank,
                    $streak->type()->name,
                    $streak->length(),
                    $streak->isOpen() ? 'Yes' : '',
                ],
                array: $streaks
            ))
            ->render();
    }
}
