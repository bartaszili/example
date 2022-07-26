<?php

declare(strict_types=1);

namespace Services\Helper;

use FuzzyWuzzy\Fuzz;
use FuzzyWuzzy\Process;

class FuzzyHelper {
    use AbstractServicesTraitClass;

    private $first = '';
    private Fuzz $fuzz;
    private Process $process;
    private $second = '';

    public function __construct($logger, $slug) {
        $this->setLogger($logger);
        $this->setSlug($slug);

        $this->setFuzz(new Fuzz());
        $this->setProcess(new Process());
    }

    // STOPPED HERE TODO FINISH ME

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getFirst(): string { return $this->first; }
    private function setFirst(string $input): void { $this->first = strtolower(trim(filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH))); }

    public function getFuzz(): Fuzz { return $this->fuzz; }
    private function setFuzz(Fuzz $input): void { $this->fuzz = $input; }

    public function getProcess(): Process { return $this->process; }
    private function setProcess(Process $input): void { $this->process = $input; }

    private function getSecond(): string { return $this->second; }
    private function setSecond(string $input): void { $this->second = strtolower(trim(filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH))); }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function run(): void {
        $return = [];

        if(empty($this->getFirst()) || empty($this->getSecond())) { return; }

        $a = $this->getFuzz()->ratio($this->getFirst(), $this->getSecond());
        if(!empty($a)) { $return['ratio'] = $a; }

        $b = $this->getFuzz()->partialRatio($this->getFirst(), $this->getSecond());
        if(!empty($b)) { $return['partialRatio'] = $b; }

        $c = $this->getFuzz()->tokenSortRatio($this->getFirst(), $this->getSecond());
        if(!empty($c)) { $return['tokenSortRatio'] = $c; }

        $d = $this->getFuzz()->tokenSetRatio($this->getFirst(), $this->getSecond());
        if(!empty($d)) { $return['tokenSetRatio'] = $d; }

        if(!empty($return)) {
            $x = 0;
            foreach($return as $key => $value) { $x += $value; }
            if($x > 0) { $return['score'] = $x / count($return); }
            else { $return['score'] = 0; }
            $this->setData($return);
        }
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function get(?string $first, ?string $second): array {
        $this->setData([]);
        if(empty($first) || empty($second)) { return $this->getData(); }
        $this->setFirst($first);
        $this->setSecond($second);
        $this->run();
        return $this->getData();
    }
}
