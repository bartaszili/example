<?php

declare(strict_types=1);

namespace Commands\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \DateTime;

class UpdaterCommand extends Command {
    use AbstractCommandsTraitClass;

    protected static $defaultName = 'app:updater';

    public function __construct($crawler) {
        $this->setCrawler($crawler);
        parent::__construct();
    }

    ///////////////////////////////////////
    ////////// Protected Methods //////////
    ///////////////////////////////////////

    protected function configure() {
        $this->addArgument('target', InputArgument::OPTIONAL, 'Target Class.');
        $this->addOption('function', 'f', InputOption::VALUE_OPTIONAL, 'Function trigger.', '');
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit processing.', 0);
        $this->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Offset processing.', 0);
        $this->addOption('process', 'p', InputOption::VALUE_OPTIONAL, 'Process targets.', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setTarget($input->getArgument('target'));
        $this->setFunction($input->getOption('function'));
        $this->setLimit($input->getOption('limit'));
        $this->setOffset($input->getOption('offset'));
        $this->setProcess($input->getOption('process'));

        $start = new DateTime('now');
        $output->writeln("<fg=yellow>> ".$start->format('Y-m-d H:i:s')."</> <fg=green;options=bold>Updater Started</>  ".$this->getTarget());

        $this->getCrawler()->setClass($this->getTarget())
                           ->setLimit($this->getLimit())
                           ->setOffset($this->getOffset())
                           ->setFunction($this->getFunction())
                           ->setProcess($this->getProcess())
                           ->updater();

        $stop = new DateTime('now');
        $time = $start->diff($stop);
        $output->writeln("<fg=yellow>> ".$stop->format('Y-m-d H:i:s')."</> <fg=red;options=bold>Updater Finished</> ".$this->getTarget()." <fg=yellow>>>> Runtime:</> <fg=magenta>".$this->measuredTime($time)."</>");

        return Command::SUCCESS;
    }
}
