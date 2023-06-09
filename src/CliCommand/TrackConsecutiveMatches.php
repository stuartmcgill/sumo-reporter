<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\CliCommand;

use DateTime;
use StuartMcGill\SumoReporter\DomainService\MatchTracker\ConsecutiveMatchTracker;
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
    private string $dataDir;

    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly ConsecutiveMatchTracker $consecutiveMatchTracker,
        array $config,
    ) {
        $this->dataDir =  $config['dataDir'];

        parent::__construct();
    }

    protected function configure(): void
    {
        $defaultDate = BashoDate::fromDateTime(new DateTime());

        $this->addArgument(
            name: 'date',
            mode: InputArgument::OPTIONAL,
            description: 'Basho date in YYYYMM format e.g. 2023-03',
            default: $defaultDate->format('Y-m'),
        );

        $this->addArgument(
            name: 'filename',
            mode: InputArgument::OPTIONAL,
            description: 'File to download results to',
        );

        $this->addOption(
            name: 'covid-breaks-run',
            description: 'Whether or not to stop the run in case of a COVID kyujo',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Calculating consecutive matches...');

        $date = $input->getArgument('date');
        if (!$this->validateDate($date)) {
            $io->error('If a date is specified it should be in YYYY-MM format e.g. 2020-03');

            return Command::INVALID;
        }

        $bashoDate = new BashoDate(
            year: (int)substr(string: $date, offset: 0, length: 4),
            month: (int)substr(string: $date, offset: 5, length: 2)
        );

        $covidBreaksRun = $input->getOption('covid-breaks-run');

        $consecutiveMatches = $this->consecutiveMatchTracker->calculate(
            bashoDate: $bashoDate,
            allowCovidExemptions: !$covidBreaksRun,
        );

        $io->section('Consecutive matches in Makuuchi');
        $this->printConsecutiveMatches($output, $consecutiveMatches);
        $io->newLine();

        $io->success('Successfully displayed');

        $filename = $input->getArgument('filename');
        if (empty($filename)) {
            return Command::SUCCESS;
        }

        $fullPath = "$this->dataDir/$filename";

        $this->saveConsecutiveMatches(
            runs: $consecutiveMatches,
            filename: $fullPath,
        );

        $io->success("Successfully saved to $fullPath\n");

        return Command::SUCCESS;
    }

    private function validateDate(string $date): bool
    {
        // YYYY-MM
        return preg_match(pattern: '/^[0-9]{4}-[0-9]{2}$/', subject: $date) > 0;
    }

    /** @param list<ConsecutiveMatchRun> $runs */
    private function printConsecutiveMatches(
        OutputInterface $output,
        array $runs,
    ): void {
        $table = new Table($output);
        $table
            ->setHeaders(['Name', 'Number of matches', 'Since', 'Current rank'])
            ->setRows(array_map(
                callback: static fn (ConsecutiveMatchRun $run) => [
                    $run->rikishi->shikonaEn,
                    $run->size(),
                    $run->startDate(),
                    $run->rikishi->currentRank,
                ],
                array: $runs
            ))
            ->render();
    }

    /** @param list<ConsecutiveMatchRun> $runs */
    private function saveConsecutiveMatches(array $runs, string $filename): void
    {
        $data = "Name,Matches,Since,Current rank\n";

        foreach ($runs as $run) {
            $name = $run->rikishi->shikonaEn;
            $sumoDbId = $run->rikishi->sumoDbId;
            $link = "<a href=\"https://sumodb.sumogames.de/Rikishi.aspx?r=$sumoDbId\">$name</a>";
            $currentRank = $run->rikishi->currentRank;
            $size = $run->size();
            $startDate = $run->startDate();

            $data .= "$link,$size,$startDate,$currentRank\n";
        }

        file_put_contents(filename: $filename, data: $data);
    }
}
