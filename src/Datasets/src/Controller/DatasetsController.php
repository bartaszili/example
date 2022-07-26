<?php

declare(strict_types=1);

namespace Datasets\Controller;

use App\Handler\AbstractAppTraitMethods;
use Commands\Command\AbstractCommandsTraitMethods;
use Logger\Controller\LoggerController;
use Services\Helper\AddressHelper;
use Services\Helper\SlugifyHelper;
use \DateTime;
use \Exception;

class DatasetsController {
    use AbstractAppTraitMethods;
    use AbstractCommandsTraitMethods;

    private $action = '';
    private AddressHelper $address;
    private $config = [];
    private $country = 'sk';
    private $data = [];
    private $data_2 = [];
    private LoggerController $logger;
    private SlugifyHelper $slug;
    private $step = 0;
    private $sub_step = 0;

    public function __construct(
        AddressHelper $address,
        LoggerController $logger,
        SlugifyHelper $slug,
        array $config
    ) {
        $this->setAddress($address);
        $this->setConfig($config);
        $this->setLogger($logger);
        $this->setSlug($slug);
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getAction(): string { return $this->action; }
    public function setAction(string $input): self { $this->action = $input; return $this; }

    private function getAddress(): AddressHelper { return $this->address; }
    private function setAddress(AddressHelper $input): void { $this->address = $input; }

    private function getConfig(): array { return $this->config; }
    private function setConfig(array $input): void { $this->config = $input; }

    private function getCountry(): string { return $this->country; }
    public function setCountry(string $input): self { $this->country = $input; return $this; }

    private function getData(): array { return $this->data; }
    private function setData(array $input): void { $this->data = $input; }

    private function getData2(): array { return $this->data_2; }
    private function setData2(array $input): void { $this->data_2 = $input; }

    private function getLogger(): LoggerController { return $this->logger; }
    private function setLogger(LoggerController $input): void { $this->logger = $input; }

    private function getSlug(): SlugifyHelper { return $this->slug; }
    private function setSlug(SlugifyHelper $input): void { $this->slug = $input; }

    private function getStep(): int { return $this->step; }
    public function setStep(int $input): self { $this->step = $input; return $this; }

    private function getSubStep(): int { return $this->sub_step; }
    public function setSubStep(int $input): self { $this->sub_step = $input; return $this; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        $this->configCheck();
        $this->getAddress()->setCountry($this->getCountry());
    }

     ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function run(): void {
        $this->_init();
        if(empty($this->getAction()))  {
            $this->action_shared();
            $this->action_address();
            $this->action_reset();
        }
        if($this->getAction() == 'address') { $this->action_address(); }
        if($this->getAction() == 'reset')   { $this->action_reset(); }
        if($this->getAction() == 'shared')  { $this->action_shared(); }
    }
}
