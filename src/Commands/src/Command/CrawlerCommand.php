<?php

declare(strict_types=1);

namespace Commands\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \DateTime;

class CrawlerCommand extends Command {
    use AbstractCommandsTraitClass;

    protected static $defaultName = 'app:crawler';

    public function __construct($crawler) {
        $this->setCrawler($crawler);
        parent::__construct();
    }

    ///////////////////////////////////////
    ////////// Protected Methods //////////
    ///////////////////////////////////////

    protected function configure() {
        $this->addArgument('target', InputArgument::OPTIONAL, 'Target Class.');
        $this->addArgument('url', InputArgument::OPTIONAL, 'Target URL.');
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit targets.', 0);
        $this->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Offset targets.', 0);
        $this->addOption('process', 'p', InputOption::VALUE_OPTIONAL, 'Process targets.', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setTarget($input->getArgument('target'));
        $this->setUrl($input->getArgument('url'));
        $this->setLimit($input->getOption('limit'));
        $this->setOffset($input->getOption('offset'));
        $this->setProcess($input->getOption('process'));

        $start = new DateTime('now');
        $output->writeln("<fg=yellow>> ".$start->format('Y-m-d H:i:s')."</> <fg=green;options=bold>Crawler Started</>  ".$this->getTarget());

        $this->getCrawler()->setClass($this->getTarget())
                           ->setUrl($this->getUrl())
                           ->setLimit($this->getLimit())
                           ->setOffset($this->getOffset())
                           ->setProcess($this->getProcess())
                           ->run();

        $stop = new DateTime('now');
        $time = $start->diff($stop);
        $output->writeln("<fg=yellow>> ".$stop->format('Y-m-d H:i:s')."</> <fg=red;options=bold>Crawler Finished</> ".$this->getTarget()." <fg=yellow>>>> Runtime:</> <fg=magenta>".$this->measuredTime($time)."</>");

        return Command::SUCCESS;
    }
}
