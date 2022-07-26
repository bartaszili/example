<?php

declare(strict_types=1);

namespace Commands\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \DateTime;

class CheckerCommand extends Command {
    use AbstractCommandsTraitClass;

    protected static $defaultName = 'app:checker';

    public function __construct($crawler) {
        $this->setCrawler($crawler);
        parent::__construct();
    }

    ///////////////////////////////////////
    ////////// Protected Methods //////////
    ///////////////////////////////////////

    protected function configure() {
        $this->addArgument('target', InputArgument::OPTIONAL, 'Target Class.');
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit processing.', 0);
        $this->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Offset processing.', 0);
        $this->addOption('order', 'a', InputOption::VALUE_OPTIONAL, 'Order of processing.', 'desc');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setTarget($input->getArgument('target'));
        $this->setLimit($input->getOption('limit'));
        $this->setOffset($input->getOption('offset'));
        $this->setOrder($input->getOption('order'));

        $start = new DateTime('now');
        $output->writeln("<fg=yellow>> ".$start->format('Y-m-d H:i:s')."</> <fg=green;options=bold>Checker Started</>  ".$this->getTarget());

        $this->getCrawler()->setClass($this->getTarget())
                           ->setLimit($this->getLimit())
                           ->setOffset($this->getOffset())
                           ->setOrder($this->getOrder())
                           ->checker();

        $stop = new DateTime('now');
        $time = $start->diff($stop);
        $output->writeln("<fg=yellow>> ".$stop->format('Y-m-d H:i:s')."</> <fg=red;options=bold>Checker Finished</> ".$this->getTarget()." <fg=yellow>>>> Runtime:</> <fg=magenta>".$this->measuredTime($time)."</>");

        return Command::SUCCESS;
    }
}
