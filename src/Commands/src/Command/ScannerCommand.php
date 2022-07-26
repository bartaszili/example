<?php

declare(strict_types=1);

namespace Commands\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \DateTime;

class ScannerCommand extends Command {
    use AbstractCommandsTraitClass;

    protected static $defaultName = 'app:scanner';

    public function __construct($scanner) {
        $this->setScanner($scanner);
        parent::__construct();
    }

    ///////////////////////////////////////
    ////////// Protected Methods //////////
    ///////////////////////////////////////

    protected function configure() {
        $this->addArgument('target', InputArgument::OPTIONAL, 'The target class.');
        $this->addOption('function', 'f', InputOption::VALUE_OPTIONAL, 'Function trigger.', '');
        $this->addOption('updater', 'u', InputOption::VALUE_OPTIONAL, 'Updater mode trigger (updater|crawler => true|false).', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setTarget($input->getArgument('target'));
        $this->setFunction($input->getOption('function'));
        $this->setUpdater($input->getOption('updater'));

        $start = new DateTime('now');
        $output->writeln("<fg=yellow>> ".$start->format('Y-m-d H:i:s')."</> <fg=green;options=bold>Scanner Started</>");
        $this->getScanner()->setFunction($this->getFunction())
                           ->setUpdater($this->getUpdater())
                           ->run($this->getTarget());
        $stop = new DateTime('now');
        $time = $start->diff($stop);
        $output->writeln("<fg=yellow>> ".$stop->format('Y-m-d H:i:s')."</> <fg=red;options=bold>Scanner Finished</> <fg=yellow>>>> Runtime:</> <fg=magenta>".$this->measuredTime($time)."</>");
        return Command::SUCCESS;
    }
}
