<?php

declare(strict_types=1);

namespace Drupalens\Command;

use Drupalens\Audit\Auditor;
use Drupalens\Check\HygieneCheck;
use Drupalens\Check\SecurityCheck;
use Drupalens\Check\SupportCheck;
use Drupalens\Data\HttpReleaseHistory;
use Drupalens\Finding\Severity;
use Drupalens\Project\Loader;
use Drupalens\Report\JsonReport;
use Drupalens\Report\TextReport;
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
    /** Exit code when findings meet or exceed the --fail-on threshold. */
    private const EXIT_FINDINGS = 1;

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
        try {
            $project = (new Loader())->load((string) $input->getArgument('path'));
        } catch (\Throwable $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::INVALID;
        }

        $auditor = new Auditor(new SecurityCheck(), new SupportCheck(), new HygieneCheck());

        $only = $input->getOption('only');
        if ($only !== null && !in_array($only, $auditor->ids(), true)) {
            $output->writeln(sprintf('<error>unknown check "%s" (expected: %s)</error>', $only, implode(', ', $auditor->ids())));
            return Command::INVALID;
        }

        $cache = $input->getOption('cache');
        $releases = new HttpReleaseHistory(is_string($cache) ? $cache : null);

        $findings = $auditor->run($project, $releases, is_string($only) ? $only : null);

        $rendered = $input->getOption('json')
            ? (new JsonReport())->render($findings)
            : (new TextReport())->render($findings);
        $output->write($rendered);

        $gate = Severity::threshold((string) $input->getOption('fail-on'));
        if ($gate !== null) {
            foreach ($findings as $f) {
                if ($f->severity->rank() >= $gate->rank()) {
                    return self::EXIT_FINDINGS;
                }
            }
        }
        return Command::SUCCESS;
    }
}
