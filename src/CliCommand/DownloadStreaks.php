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

        $this->addArgument(
            name: 'date',
            mode: InputArgument::OPTIONAL,
            description: 'Basho date in YYYY-MM format e.g. 2023-03',
            default: $defaultDate->format('Y-m'),
        );

        $this->addArgument(
            name: 'filename',
            mode: InputArgument::OPTIONAL,
            description: 'File to download results to',
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

        $io->success('Successfully displayed');

        $filename = $input->getArgument('filename');
        if (empty($filename)) {
            return Command::SUCCESS;
        }

        $fullPath = __DIR__ . '/../../data/' . $filename;

        $this->saveStreaks(
            winning: $winning,
            losing: $losing,
            filename: $fullPath,
        );

        $io->success("Successfully saved to $fullPath\n");

        return Command::SUCCESS;
    }

    /** @param list<Streak> $streaks */
    private function printStreaks(OutputInterface $output, array $streaks): void
    {
        $table = new Table($output);
        $table
            ->setHeaders(['Name', 'Rank', 'Type', 'Streak size', 'Streak still active?'])
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

    /**
     * @param list<Streak> $winning
     * @param list<Streak> $losing
     */
    private function saveStreaks(array $winning, array $losing, string $filename): void
    {
        $data = $this->appendStreaksToCsvData('Winning', $winning);
        $data .= "\n";
        $data .= $this->appendStreaksToCsvData('Losing', $losing);

        file_put_contents(filename: $filename, data: $data);
    }

    /** @param list<Streak> $streaks */
    private function appendStreaksToCsvData(string $title, array $streaks): string
    {
        $data = "$title streaks\n";
        $data .= "Name,Rank,Type,Streak size, Streak still active?\n";

        foreach ($streaks as $streak) {
            $name = $streak->wrestler->name;
            $rank = $streak->wrestler->rank;
            $type = $streak->type()->name;
            $length = $streak->length();
            $isActive = $streak->isOpen() ? 'Yes' : '';

            $data .= "$name,$rank,$type,$length,$isActive\n";
        }

        return $data;
    }
}
