<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\CliCommand;

use DateTime;
use StuartMcGill\SumoReporter\DomainService\ConsecutiveMatchTracker;
use StuartMcGill\SumoReporter\Model\BashoDate;
use StuartMcGill\SumoReporter\Model\ConsecutiveMatchRun;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'report:consecutivematchtracker',
    description: 'Displays wrestlers with the most consecutive matches in Makuuchi.',
)]
class TrackConsecutiveMatches extends Command
{
    public function __construct(private readonly ConsecutiveMatchTracker $consecutiveMatchTracker)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $defaultDate = BashoDate::fromDateTime(new DateTime());

        $this->addArgument(
            name: 'date',
            mode: InputArgument::OPTIONAL,
            description: 'Basho date in YYYYMM format e.g. 202303',
            default: $defaultDate->format('Ym'),
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Calculating consecutive matches...');

        $date = $input->getArgument('date');
        $consecutiveMatches = $this->consecutiveMatchTracker->calculate($date);

        $io->section('Consecutive matches in Makuuchi');
        $this->printConsecutiveMatches($output, $consecutiveMatches);
        $io->newLine();

        $io->success('Successfully completed');

        return Command::SUCCESS;
    }

    /** @param list<ConsecutiveMatchRun> $runs */
    private function printConsecutiveMatches(
        OutputInterface $output,
        array $consecutiveMatches
    ): void {
        $table = new Table($output);
        $table
            ->setHeaders(['Name', 'Rank', 'Number of matches', 'Since'])
            ->setRows(array_map(
                callback: static fn (ConsecutiveMatchRun $run) => [
                    $run->rikishi->shikonaEn,
                    $run->rikishi->currentRank,
                    $run->size(),
                    $run->startDate(),
                ],
                array: $consecutiveMatches
            ))
            ->render();
    }
}
