<?php

declare(strict_types=1);

namespace Matcher\Controller;

use Logger\Controller\LoggerController;
use Services\Helper\AddressHelper;
use Services\Helper\AreaHelper;
use Services\Helper\CategoryHelper;
use Services\Helper\CountryFinderHelper;
use Services\Helper\CurrencyHelper;
use Services\Helper\SlugifyHelper;
use Services\Helper\TypeHelper;
use \DateTime;
use \Exception;

trait AbstractMatcherTraitClass {
    use AbstractMatcherTraitMethods;

    private AddressHelper $address;
    private AreaHelper $area;
    private CategoryHelper $category;
    private $country = '';
    private CountryFinderHelper $country_finder;
    private CurrencyHelper $currency;
    private $data = [];
    private LoggerController $logger;
    private SlugifyHelper $slug;
    private TypeHelper $type;

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getAddress(): AddressHelper { return $this->address; }
    private function setAddress(AddressHelper $input): void { $this->address = $input; }

    private function getArea(): AreaHelper { return $this->area; }
    private function setArea(AreaHelper $input): void { $this->area = $input; }

    private function getCategory(): CategoryHelper { return $this->category; }
    private function setCategory(CategoryHelper $input): void { $this->category = $input; }

    private function getCountry(): string { return $this->country; }
    public function setCountry(string $input): self { $this->country = filter_var(strtolower(trim($input)), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH); return $this; }

    private function getCountryFinder(): CountryFinderHelper { return $this->country_finder; }
    private function setCountryFinder(CountryFinderHelper $input): void { $this->country_finder = $input; }

    private function getCurrency(): CurrencyHelper { return $this->currency; }
    private function setCurrency(CurrencyHelper $input): void { $this->currency = $input; }

    private function getData(): array { return $this->data; }
    public function setData(array $input): self { $this->data = $input; return $this; }

    private function getLogger(): LoggerController { return $this->logger; }
    private function setLogger(LoggerController $input): void { $this->logger = $input; }

    private function getSlug(): SlugifyHelper { return $this->slug; }
    private function setSlug(SlugifyHelper $input): void { $this->slug = $input; }

    private function getType(): TypeHelper { return $this->type; }
    private function setType(TypeHelper $input): void { $this->type = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        // Validate country code
        if(empty($this->getCountry()) || !preg_match("/^[a-zA-Z]{2}$/", $this->getCountry())) {
            $data = [
                'message' => [
                    'description' => 'Silent log',
                    'details' => "Invalid country code: `{$this->getCountry()}`"
                ],
                'logger' => get_called_class().'::_init'
            ];
            $this->getLogger()->setData($data);
            $this->setData([]);
            return;
        }
        $this->getAddress()->setCountry($this->getCountry());
        $this->getCategory()->setCountry($this->getCountry());
        $this->getCountryFinder()->setCountry($this->getCountry());
        $this->getType()->setCountry($this->getCountry());
    }
}
