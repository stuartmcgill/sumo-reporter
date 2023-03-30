<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\CliCommand;

use StuartMcGill\SumoScraper\DomainService\StreakDownloader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'report:streaks',
    description: 'Displays winning and losing streaks.'
)]
class DownloadStreaks extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Downloading wrestler streaks...');

        $wrestlers = (new StreakDownloader())->download();

        foreach ($wrestlers as $wrestler) {
            $output->writeln($wrestler->name);
        }

        $io->success('Successfully completed');

        return Command::SUCCESS;
    }
}
