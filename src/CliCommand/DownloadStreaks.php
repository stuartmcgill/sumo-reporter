<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\CliCommand;

use StuartMcGill\SumoScraper\DomainService\StreakDownloader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'report:streaks')]
class DownloadStreaks extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $wrestlers = (new StreakDownloader())->download();

        foreach ($wrestlers as $wrestler) {
            $output->writeln($wrestler->name);
        }

        return Command::SUCCESS;
    }
}
