<?php

declare(strict_types=1);

namespace Services\Helper;

/*
 * 2021-02-10T21:15:00+01:00
 *
 * Usage:
 *
 *     Functions:
 *       convertDistance()
 *       from('string')
 *       to('string')
 *       value('string')
 *
 *     View some results:
 *       $distance = $distance
 *                  ->to('m')
 *                  ->from('km')
 *                  ->value('220')
 *                  ->convertDistance();
 *       var_dump($distance);
 */
class DistanceHelper {
    use AbstractServicesTraitClass;

    private $dsf = 'distance_conversion.json';
    private $from = '';
    private $return = '';
    private $to = 'm';
    private $value = '';

    public function __construct(array $datasets, $logger, $slug) {
        $this->setDatasets($datasets);
        $this->setLogger($logger);
        $this->setSlug($slug);
        $this->configCheck();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getFrom(): string { return $this->from; }
    private function setFrom(string $input): void { $this->from = filter_var(strtolower(trim($input)), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH); }

    private function getReturn(): string { return $this->return; }
    private function setReturn(string $input): void { $this->return = $input; }

    private function getTo(): string { return $this->to; }
    private function setTo(string $input): void { $this->to = filter_var(strtolower(trim($input)), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH); }

    private function getValue(): string { return $this->value; }
    private function setValue(string $input): void { $this->value = filter_var(strtolower(trim($input)), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH); }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        $this->loadDataset();
    }

    private function distance(): void {
        $this->run();
        $x = $this->getValue();
        $y = 1 / $this->getData()['units'][$this->getFrom()]['rate'];
        $z = $this->getData()['units'][$this->getTo()]['rate'];
        $this->setReturn((string) ($x*$y*$z));
    }

    private function run(): void {
        $from = $this->slugify($this->getFrom());
        if($from == 'm') {
            $this->setFrom($from);
        } else {
            foreach($this->getData()['units'] as $key => $value) {
                foreach($value['name'] as $name) {
                    if(true == stristr($from, $name)) {
                        $this->setFrom($key);
                        break 2;
                    }
                }
            }
        }
        $to = $this->slugify($this->getTo());
        if($to == 'm') {
            $this->setTo($to);
        } else {
            foreach($this->getData()['units'] as $key => $value) {
                foreach($value['name'] as $name) {
                    if(true == stristr($to, $name)) {
                        $this->setTo($key);
                        break 2;
                    }
                }
            }
        }
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function convertDistance(): string {
        $this->_init();
        $this->distance();
        return $this->getReturn();
    }

    public function from(string $input): self {
        $this->setFrom($input);
        return $this;
    }

    public function to(string $input): self {
        $this->setTo($input);
        return $this;
    }

    public function value(string $input): self {
        $this->setValue($input);
        return $this;
    }
}
