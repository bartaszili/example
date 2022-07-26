<?php

declare(strict_types=1);

namespace Api\Entity;

use Api\Entity\Property;
use Doctrine\ORM\Mapping as ORM;
use \DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="pricelog")
 **/
class Pricelog {
    /**
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /** @ORM\Column(type="guid") */
    private $property_id;

    /** @ORM\Column(type="float") */
    private $price;

    /** @ORM\Column(type="float") */
    private $unit_price;

    /** @ORM\Column(type="datetime") */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="Api\Entity\Property", inversedBy="price_log", cascade={"persist"})
     * @ORM\JoinColumn(name="property_id", referencedColumnName="id")
     */
    private $property;

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getId(): string { return $this->id; }
    // setId by Doctrine ORM

    private function getPropertyId(): string { return $this->property_id; }
    public  function setPropertyId(string $input): void { $this->property_id = $input; }

    private function getPrice(): float { return $this->price; }
    public  function setPrice(float $input): void { $this->price = $input; }

    private function getUnitPrice(): float { return $this->unit_price; }
    public  function setUnitPrice(float $input): void { $this->unit_price = $input; }

    private function getCreated(): DateTime { return $this->created; }
    public  function setCreated(?DateTime $input = null): void { if(empty($input) && empty($this->getId())) { $this->created = new DateTime("now"); } else { $this->created = $input; } }

    private function getProperty() { return $this->property->getProperty(); }
    public  function setProperty($input): void { $this->property = $input; }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    /** Removes Property ManyToOne relationship to prevent infinite recursion */
    public function resetProperty(): void { $this->property = [];}

    public function getPricelog(bool $input = false): array {
        $return = [
            'id'          => $this->getId(),
            'property_id' => $this->getPropertyId(),
            'price'       => $this->getPrice(),
            'unit_price'  => $this->getUnitPrice(),
            'created'     => $this->getCreated()
        ];
        if($input) {
            $return['property'] = $this->getProperty();
        }
        return $return;
    }

    public function setPricelog(array $input): void {
        if(isset($input['property_id']) && !empty($input['property_id']))  { $this->setPropertyId((string) $input['property_id']); }
        if(isset($input['price'])       && !is_null($input['price']))      { $this->setPrice((float) $input['price']); }
        if(isset($input['unit_price'])  && !is_null($input['unit_price'])) { $this->setUnitPrice((float) $input['unit_price']); }
        if(isset($input['created'])     && !empty($input['created']))      { $this->setCreated($input['created']); }
    }
}
