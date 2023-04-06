<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\CliCommand;

use DateTime;
use StuartMcGill\SumoScraper\DomainService\StreakCompilation;
use StuartMcGill\SumoScraper\DomainService\StreakDownloader;
use StuartMcGill\SumoScraper\Model\BashoDate;
use StuartMcGill\SumoScraper\Model\Streak;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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

        $streaks = $this->streakDownloader->download(
            year: (int)substr(string: $date, offset: 0, length: 4),
            month: (int)substr(string: $date, offset: 5, length: 2)
        );

        $this->printStreaks($io, $streaks);
        $io->success('Successfully completed');

        return Command::SUCCESS;
    }

    /** @param list<Streak> $streaks */
    private function printStreaks(SymfonyStyle $io, array $streaks): void
    {
        foreach ($streaks as $streak) {
            $io->writeln($streak->wrestler->name);
        }
    }
}
