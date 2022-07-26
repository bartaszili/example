<?php

declare(strict_types=1);

namespace Commands\Command;

use Datasets\Controller\DatasetsController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \DateTime;

class DatasetsCommand extends Command {
    use AbstractCommandsTraitMethods;

    protected static $defaultName = 'app:datasets';
    private $action = '';
    private $country = 'sk';
    private DatasetsController $datasets;
    private $step = 0;
    private $sub_step = 0;

    public function __construct(DatasetsController $datasets) {
        $this->setDatasets($datasets);
        parent::__construct();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getAction(): string { return $this->action; }
    private function setAction(?string $input): void {
        if(!empty($input)) {
            $input = strtolower(trim(filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)));
            $pattern = "/^(?:address|reset|shared)$/";
            if(preg_match($pattern, $input)) {
                $this->action = $input;
            }
        }
    }

    private function getCountry(): string { return $this->country; }
    private function setCountry(?string $input): void {
        if(!empty($input)) {
            $input = strtolower(trim(filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)));
            $pattern = "/^(?:sk)$/";
            if(preg_match($pattern, $input)) {
                $this->country = $input;
            }
        }
    }

    private function getDatasets(): DatasetsController { return $this->datasets; }
    private function setDatasets(DatasetsController $input): void { $this->datasets = $input; }

    private function getStep(): int { return $this->step; }
    private function setStep($input): void {
        if(!empty($input)) {
            $input = (int) filter_var(trim($input), FILTER_SANITIZE_NUMBER_INT);
            if($input > 0) { $this->step = $input; }
        }
    }

    private function getSubStep(): int { return $this->sub_step; }
    private function setSubStep($input): void {
        if(!empty($input)) {
            $input = (int) filter_var(trim($input), FILTER_SANITIZE_NUMBER_INT);
            if($input > 0) { $this->sub_step = $input; }
        }
    }

    ///////////////////////////////////////
    ////////// Protected Methods //////////
    ///////////////////////////////////////

    protected function configure() {
        $this->addArgument('action', InputArgument::OPTIONAL, 'Action: address|shared|reset');
        $this->addOption('country', 'c', InputOption::VALUE_OPTIONAL, 'Country: sk.', 'sk');
        $this->addOption('step', 's', InputOption::VALUE_OPTIONAL, 'Step: 0|1|2|3...', 0);
        $this->addOption('substep', 'z', InputOption::VALUE_OPTIONAL, 'Sub-step: 0|1|2|3...', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setAction($input->getArgument('action'));
        $this->setCountry($input->getOption('country'));
        $this->setStep($input->getOption('step'));
        $this->setSubStep($input->getOption('substep'));

        $start = new DateTime('now');
        $output->writeln("<fg=yellow>> ".$start->format('Y-m-d H:i:s')."</> <fg=green;options=bold>Datasets Started</>");
        if(!empty($this->getAction())) { $this->getDatasets()->setAction($this->getAction()); }
        if(!empty($this->getCountry())) { $this->getDatasets()->setCountry($this->getCountry()); }
        if(!empty($this->getStep())) { $this->getDatasets()->setStep($this->getStep()); }
        if(!empty($this->getSubStep())) { $this->getDatasets()->setSubStep($this->getSubStep()); }
        $this->getDatasets()->run();
        $stop = new DateTime('now');
        $time = $start->diff($stop);
        $output->writeln("<fg=yellow>> ".$stop->format('Y-m-d H:i:s')."</> <fg=red;options=bold>Datasets Finished</> <fg=yellow>>>> Runtime:</> <fg=magenta>".$this->measuredTime($time)."</>");
        return Command::SUCCESS;
    }
}
