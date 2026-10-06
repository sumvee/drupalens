<?php

declare(strict_types=1);

namespace Drupalens\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'audit',
    description: 'Audit a Drupal codebase for security, support status, and hygiene',
)]
final class AuditCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to the Drupal project', '.')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Emit findings as JSON')
            ->addOption('fail-on', null, InputOption::VALUE_REQUIRED, 'Severity gate: critical|high|medium|low|none', 'high')
            ->addOption('only', null, InputOption::VALUE_REQUIRED, 'Run a single check: security|support|hygiene')
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, 'Directory to cache drupal.org responses');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>audit: not implemented yet (checks land in Wave 3, report in Wave 4). Scaffold is live.</comment>');

        return Command::SUCCESS;
    }
}
