<?php

declare(strict_types=1);

namespace Commands\Command;

use Duplicates\Controller\DuplicatesController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \DateTime;

class DuplicatesCommand extends Command {
    use AbstractCommandsTraitMethods;

    private DuplicatesController $duplicates;

    protected static $defaultName = 'app:duplicates';

    public function __construct(DuplicatesController $duplicates) {
        $this->setDuplicates($duplicates);
        parent::__construct();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getDuplicates(): DuplicatesController { return $this->duplicates; }
    private function setDuplicates(DuplicatesController $input): void { $this->duplicates = $input; }

    ///////////////////////////////////////
    ////////// Protected Methods //////////
    ///////////////////////////////////////

    protected function configure() {}

    protected function execute(InputInterface $input, OutputInterface $output) {
        $start = new DateTime('now');
        $output->writeln("<fg=yellow>> ".$start->format('Y-m-d H:i:s')."</> <fg=green;options=bold>Duplicates Started</>");
        $this->getDuplicates()->run();
        $stop = new DateTime('now');
        $time = $start->diff($stop);
        $output->writeln("<fg=yellow>> ".$stop->format('Y-m-d H:i:s')."</> <fg=red;options=bold>Duplicates Finished</> <fg=yellow>>>> Runtime:</> <fg=magenta>".$this->measuredTime($time)."</>");
        return Command::SUCCESS;
    }
}
