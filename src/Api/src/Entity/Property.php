<?php

declare(strict_types=1);

namespace Api\Entity;

use Api\Entity\Pricelog;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use \DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="properties")
 **/
class Property {
    /**
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /** @ORM\Column(nullable=true) */
    private $name;

    /** @ORM\Column(type="text", nullable=true) */
    private $description;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $archived;

    /** @ORM\Column(type="float", nullable=true) */
    private $area_total;

    /** @ORM\Column(nullable=true) */
    private $area_unit;

    /** @ORM\Column(type="float", nullable=true) */
    private $area_usable;

    /** @ORM\Column(type="float", nullable=true) */
    private $area_used;

    /** @ORM\Column(nullable=true) */
    private $category;

    /** @ORM\Column(nullable=true) */
    private $contact_email;

    /** @ORM\Column(nullable=true) */
    private $contact_name;

    /** @ORM\Column(nullable=true) */
    private $contact_phone;

    /** @ORM\Column(nullable=true) */
    private $country;

    /** @ORM\Column(nullable=true) */
    private $county;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $created;

    /** @ORM\Column(nullable=true) */
    private $currency;

    /** @ORM\Column(nullable=true) */
    private $district;

    /** @ORM\Column(type="json_array", nullable=true) */
    private $duplicates;

    /** @ORM\Column(type="boolean", nullable=true) */
    private $is_active;

    /** @ORM\Column(type="boolean", nullable=true) */
    private $is_main;

    /** @ORM\Column(type="boolean", nullable=true) */
    private $is_private;

    /** @ORM\Column(type="float", nullable=true) */
    private $latitude;

    /** @ORM\Column(type="float", nullable=true) */
    private $longitude;

    /** @ORM\Column(type="json_array", nullable=true) */
    private $meta;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $modified;

    /** @ORM\Column(nullable=true) */
    private $origin_condition;

    /** @ORM\Column(nullable=true) */
    private $origin_host;

    /** @ORM\Column(nullable=true) */
    private $origin_id;

    /** @ORM\Column(type="text", nullable=true) */
    private $origin_image_path;

    /** @ORM\Column(nullable=true) */
    private $origin_status;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $origin_updated;

    /** @ORM\Column(type="text", nullable=true) */
    private $origin_url;

    /** @ORM\Column(type="json_array", nullable=true) */
    private $other_json;

    /** @ORM\Column(type="text", nullable=true) */
    private $other_text;

    /** @ORM\Column(nullable=true) */
    private $postcode;

    /** @ORM\Column(type="float", nullable=true) */
    private $price;

    /** @ORM\Column(nullable=true) */
    private $price_type;

    /** @ORM\Column(type="integer", nullable=true) */
    private $score;

    /** @ORM\Column(type="json_array", nullable=true) */
    private $score_details;

    /** @ORM\Column(nullable=true) */
    private $street;

    /** @ORM\Column(nullable=true) */
    private $town;

    /** @ORM\Column(nullable=true) */
    private $town_part;

    /** @ORM\Column(nullable=true) */
    private $type;

    /** @ORM\Column(type="float", nullable=true) */
    private $unit_price;

    /** @ORM\Column(nullable=true) */
    private $unit_price_unit;

    /** @ORM\OneToMany(targetEntity="Api\Entity\Pricelog", mappedBy="property", cascade={"persist"}, orphanRemoval=true) */
    private $price_log;

    private $distance;

    public function __construct() {
        $this->setPriceLog(new ArrayCollection());
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getId(): string { return $this->id; }
    // setId by Doctrine ORM

    private function getName(): ?string { return $this->name; }
    private function setName(?string $input): void { $this->name = $input; }

    private function getDescription(): ?string { return $this->description; }
    private function setDescription(?string $input): void { $this->description = $input; }

    private function getArchived(): ?DateTime { return $this->archived; }
    public function setArchived(?DateTime $input = null): void { if(empty($input)) { $this->archived = new DateTime("now"); } else { $this->archived = $input; } }

    private function getAreaTotal(): ?float { return $this->area_total; }
    private function setAreaTotal(?float $input): void { $this->area_total = $input; }

    private function getAreaUnit(): ?string { return $this->area_unit; }
    private function setAreaUnit(?string $input): void { $this->area_unit = $input; }

    private function getAreaUsable(): ?float { return $this->area_usable; }
    private function setAreaUsable(?float $input): void { $this->area_usable = $input; }

    private function getAreaUsed(): ?float { return $this->area_used; }
    private function setAreaUsed(?float $input): void { $this->area_used = $input; }

    private function getCategory(): ?string { return $this->category; }
    private function setCategory(?string $input): void { $this->category = $input; }

    private function getContactEmail(): ?string { return $this->contact_email; }
    private function setContactEmail(?string $input): void { $this->contact_email = $input; }

    private function getContactName(): ?string { return $this->contact_name; }
    private function setContactName(?string $input): void { $this->contact_name = $input; }

    private function getContactPhone(): ?string { return $this->contact_phone; }
    private function setContactPhone(?string $input): void { $this->contact_phone = $input; }

    private function getCountry(): ?string { return $this->country; }
    private function setCountry(?string $input): void { $this->country = $input; }

    private function getCounty(): ?string { return $this->county; }
    private function setCounty(?string $input): void { $this->county = $input; }

    private function getCreated(): ?DateTime { return $this->created; }
    private function setCreated(?DateTime $input = null): void { if(empty($input) && empty($this->getId())) { $this->created = new DateTime("now"); } else { $this->created = $input; } }

    private function getCurrency(): ?string { return $this->currency; }
    private function setCurrency(?string $input): void { $this->currency = $input; }

    private function getDistrict(): ?string { return $this->district; }
    private function setDistrict(?string $input): void { $this->district = $input; }

    private function getDuplicates(): ?array { return $this->duplicates; }
    public function setDuplicates(?array $input): void { $this->duplicates = $input; }

    private function getIsActive(): bool { return $this->is_active; }
    public function setIsActive(?bool $input): void { $this->is_active = $input; }

    private function getIsMain(): ?bool { return $this->is_main; }
    public function setIsMain(?bool $input): void { $this->is_main = $input; }

    private function getIsPrivate(): bool { return $this->is_private; }
    private function setIsPrivate(?bool $input): void { $this->is_private = $input; }

    private function getLatitude(): ?float { return $this->latitude; }
    private function setLatitude(?float $input): void { $this->latitude = $input; }

    private function getLongitude(): ?float { return $this->longitude; }
    private function setLongitude(?float $input): void { $this->longitude = $input; }

    private function getMeta(): ?array { return $this->meta; }
    private function setMeta(?array $input): void { $this->meta = $input; }

    private function getModified(): ?DateTime { return $this->modified; }
    private function setModified(?DateTime $input = null): void { if(empty($input)) { $this->modified = new DateTime("now"); } else { $this->modified = $input; } }

    private function getOriginCondition(): ?string { return $this->origin_condition; }
    private function setOriginCondition(?string $input): void { $this->origin_condition = $input; }

    private function getOriginHost(): ?string { return $this->origin_host; }
    private function setOriginHost(?string $input): void { $this->origin_host = $input; }

    private function getOriginId(): ?string { return $this->origin_id; }
    private function setOriginId(?string $input): void { $this->origin_id = $input; }

    private function getOriginImagePath(): ?string { return $this->origin_image_path; }
    private function setOriginImagePath(?string $input): void { $this->origin_image_path = $input; }

    private function getOriginStatus(): ?string { return $this->origin_status; }
    private function setOriginStatus(?string $input): void { $this->origin_status = $input; }

    private function getOriginUpdated(): ?DateTime { return $this->origin_updated; }
    private function setOriginUpdated(?DateTime $input): void { $this->origin_updated = $input; }

    private function getOriginUrl(): ?string { return $this->origin_url; }
    private function setOriginUrl(?string $input): void { $this->origin_url = $input; }

    private function getOtherJson(): ?array { return $this->other_json; }
    public function setOtherJson(?array $input): void { $this->other_json = $input; }

    private function getOtherText(): ?string { return $this->other_text; }
    public function setOtherText(?string $input): void { $this->other_text = $input; }

    private function getPostcode(): ?string { return $this->postcode; }
    private function setPostcode(?string $input): void { $this->postcode = $input; }

    private function getPrice(): ?float { return $this->price; }
    private function setPrice(?float $input): void { $this->price = $input; }

    private function getPriceType(): ?string { return $this->price_type; }
    private function setPriceType(?string $input): void { $this->price_type = $input; }

    private function getScore(): ?int { return $this->score; }
    private function setScore(?int $input): void { $this->score = $input; }

    private function getScoreDetails(): ?array { return $this->score_details; }
    private function setScoreDetails(?array $input): void { $this->score_details = $input; }

    private function getStreet(): ?string { return $this->street; }
    private function setStreet(?string $input): void { $this->street = $input; }

    private function getTown(): ?string { return $this->town; }
    private function setTown(?string $input): void { $this->town = $input; }

    private function getTownPart(): ?string { return $this->town_part; }
    private function setTownPart(?string $input): void { $this->town_part = $input; }

    private function getType(): ?string { return $this->type; }
    private function setType(?string $input): void { $this->type = $input; }

    private function getUnitPrice(): ?float { return $this->unit_price; }
    private function setUnitPrice(?float $input): void { $this->unit_price = $input; }

    private function getUnitPriceUnit(): ?string { return $this->unit_price_unit; }
    private function setUnitPriceUnit(?string $input): void { $this->unit_price_unit = $input; }

    private function getPriceLog() { return $this->price_log; }
    private function setPriceLog($input): void { $this->price_log = $input; }

    public function getDistance() { return $this->distance; }
    public function setDistance($input): void { $this->distance = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function writePricelog(array $input): void {
        $u_p = 0;
        if(isset($input['unit_price'])) { $u_p = $input['unit_price']; }
        $pricelogObj = new Pricelog();
        $pricelogObj->setPrice((float) $input['price']);
        $pricelogObj->setUnitPrice((float) $u_p);
        $date = null;
        if(isset($input['created']) && !empty($input['created'])) { $date = $input['created']; }
        if(isset($input['modified']) && !empty($input['modified'])) { $date = $input['modified']; }
        $pricelogObj->setCreated($date);
        $pricelogObj->setProperty($this);
        $this->getPriceLog()->add($pricelogObj);
    }

    private function withPricelog(bool $input): array {
        $return = [];
        if($input) {
            $return = $this->getPriceLog()
                           ->map(function(Pricelog $var) {
                               $var->resetProperty();
                               return $var->getPricelog(false); })
                           ->toArray();
        }
        return $return;
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function getProperty(bool $input = false): array {
        $return = [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'description'       => $this->getDescription(),
            'archived'          => $this->getArchived(),
            'area_total'        => $this->getAreaTotal(),
            'area_unit'         => $this->getAreaUnit(),
            'area_usable'       => $this->getAreaUsable(),
            'area_used'         => $this->getAreaUsed(),
            'category'          => $this->getCategory(),
            'contact_email'     => $this->getContactEmail(),
            'contact_name'      => $this->getContactName(),
            'contact_phone'     => $this->getContactPhone(),
            'country'           => $this->getCountry(),
            'county'            => $this->getCounty(),
            'created'           => $this->getCreated(),
            'currency'          => $this->getCurrency(),
            'district'          => $this->getDistrict(),
            'duplicates'        => $this->getDuplicates(),
            'is_active'         => $this->getIsActive(),
            'is_main'           => $this->getIsMain(),
            'is_private'        => $this->getIsPrivate(),
            'latitude'          => $this->getLatitude(),
            'longitude'         => $this->getLongitude(),
            'meta'              => $this->getMeta(),
            'modified'          => $this->getModified(),
            'origin_condition'  => $this->getOriginCondition(),
            'origin_host'       => $this->getOriginHost(),
            'origin_id'         => $this->getOriginId(),
            'origin_image_path' => $this->getOriginImagePath(),
            'origin_status'     => $this->getOriginStatus(),
            'origin_updated'    => $this->getOriginUpdated(),
            'origin_url'        => $this->getOriginUrl(),
            'other_json'        => $this->getOtherJson(),
            'other_text'        => $this->getOtherText(),
            'postcode'          => $this->getPostcode(),
            'price'             => $this->getPrice(),
            'price_type'        => $this->getPriceType(),
            'score'             => $this->getScore(),
            'score_details'     => $this->getScoreDetails(),
            'street'            => $this->getStreet(),
            'town'              => $this->getTown(),
            'town_part'         => $this->getTownPart(),
            'type'              => $this->getType(),
            'unit_price'        => $this->getUnitPrice(),
            'unit_price_unit'   => $this->getUnitPriceUnit()
        ];
        if($input && (count($this->getPriceLog()) > 0)) {
            $return['price_log'] = $this->withPricelog($input);
        }
        return $return;
    }

    public function setProperty(array $input): void {
        if(isset($input['name'])              && !empty($input['name']))              { $this->setName($input['name']); }
        if(isset($input['description'])       && !empty($input['description']))       { $this->setDescription($input['description']); }           else { $this->setDescription(null); }
        if(isset($input['area_total'])        && !is_null($input['area_total']))      { $this->setAreaTotal((float) $input['area_total']); }      else { $this->setAreaTotal(null); }
        if(isset($input['area_unit'])         && !empty($input['area_unit']))         { $this->setAreaUnit($input['area_unit']); }                else { $this->setAreaUnit(null); }
        if(isset($input['area_usable'])       && !is_null($input['area_usable']))     { $this->setAreaUsable((float) $input['area_usable']); }    else { $this->setAreaUsable(null); }
        if(isset($input['area_used'])         && !is_null($input['area_used']))       { $this->setAreaUsed((float) $input['area_used']); }        else { $this->setAreaUsed(null); }
        if(isset($input['category'])          && !empty($input['category']))          { $this->setCategory($input['category']); }                 else { $this->setCategory(null); }
        if(isset($input['contact_email'])     && !empty($input['contact_email']))     { $this->setContactEmail($input['contact_email']); }        else { $this->setContactEmail(null); }
        if(isset($input['contact_name'])      && !empty($input['contact_name']))      { $this->setContactName($input['contact_name']); }          else { $this->setContactName(null); }
        if(isset($input['contact_phone'])     && !empty($input['contact_phone']))     { $this->setContactPhone($input['contact_phone']); }        else { $this->setContactPhone(null); }
        if(isset($input['country'])           && !empty($input['country']))           { $this->setCountry($input['country']); }                   else { $this->setCountry(null); }
        if(isset($input['county'])            && !empty($input['county']))            { $this->setCounty($input['county']); }                     else { $this->setCounty(null); }
        if(isset($input['created'])           && !empty($input['created']))           { $this->setCreated($input['created']); }
        if(isset($input['currency'])          && !empty($input['currency']))          { $this->setCurrency($input['currency']); }                 else { $this->setCurrency(null); }
        if(isset($input['district'])          && !empty($input['district']))          { $this->setDistrict($input['district']); }                 else { $this->setDistrict(null); }
        if(isset($input['is_active']))                                                { $this->setIsActive($input['is_active']); }                else { $this->setIsActive(true); }
        if(isset($input['is_main']))                                                  { $this->setIsMain($input['is_main']); }                    else { if(is_null($this->getIsMain())) { $this->setIsMain(true); } }
        if(isset($input['is_private']))                                               { $this->setIsPrivate($input['is_private']); }              else { $this->setIsPrivate(false); }
        if(isset($input['latitude'])          && !is_null($input['latitude']))        { $this->setLatitude((float) $input['latitude']); }         else { $this->setLatitude(null); }
        if(isset($input['longitude'])         && !is_null($input['longitude']))       { $this->setLongitude((float) $input['longitude']); }       else { $this->setLongitude(null); }
        if(isset($input['meta'])              && !empty($input['meta']))              { $this->setMeta($input['meta']); }                         else { $this->setMeta(null); }
        if(isset($input['modified'])          && !empty($input['modified']))          { $this->setModified($input['modified']); }
        if(isset($input['origin_condition'])  && !empty($input['origin_condition']))  { $this->setOriginCondition($input['origin_condition']); }  else { $this->setOriginCondition(null); }
        if(isset($input['origin_host'])       && !empty($input['origin_host']))       { $this->setOriginHost($input['origin_host']); }
        if(isset($input['origin_id'])         && !empty($input['origin_id']))         { $this->setOriginId($input['origin_id']); }
        if(isset($input['origin_image_path']) && !empty($input['origin_image_path'])) { $this->setOriginImagePath($input['origin_image_path']); } else { $this->setOriginImagePath(null); }
        if(isset($input['origin_status'])     && !empty($input['origin_status']))     { $this->setOriginStatus($input['origin_status']); }        else { $this->setOriginStatus(null); }
        if(isset($input['origin_updated'])    && !empty($input['origin_updated']))    { $this->setOriginUpdated($input['origin_updated']); }
        if(isset($input['origin_url'])        && !empty($input['origin_url']))        { $this->setOriginUrl($input['origin_url']); }
        if(isset($input['postcode'])          && !empty($input['postcode']))          { $this->setPostcode($input['postcode']); }                 else { $this->setPostcode(null); }
        if(isset($input['price'])             && !empty($input['price']))             {
            // write pricelog if price is different
            if($input['price'] != (float) $this->getPrice() && $input['price'] != 1) { $this->writePricelog($input); }
            $this->setPrice((float) $input['price']);
        }                                                                                                                                         else { $this->setPrice(null); }
        if(isset($input['price_type'])        && !empty($input['price_type']))        { $this->setPriceType($input['price_type']); }              else { $this->setPriceType(null); }
        if(isset($input['score'])             && !is_null($input['score']))           { $this->setScore((int) $input['score']); }                 else { $this->setScore(null); }
        if(isset($input['score_details'])     && !empty($input['score_details']))     { $this->setScoreDetails($input['score_details']); }        else { $this->setScoreDetails(null); }
        if(isset($input['street'])            && !empty($input['street']))            { $this->setStreet($input['street']); }                     else { $this->setStreet(null); }
        if(isset($input['town'])              && !empty($input['town']))              { $this->setTown($input['town']); }                         else { $this->setTown(null); }
        if(isset($input['town_part'])         && !empty($input['town_part']))         { $this->setTownPart($input['town_part']); }                else { $this->setTownPart(null); }
        if(isset($input['type'])              && !empty($input['type']))              { $this->setType($input['type']); }                         else { $this->setType(null); }
        if(isset($input['unit_price'])        && !is_null($input['unit_price']))      { $this->setUnitPrice((float) $input['unit_price']); }      else { $this->setUnitPrice(null); }
        if(isset($input['unit_price_unit'])   && !empty($input['unit_price_unit']))   { $this->setUnitPriceUnit($input['unit_price_unit']); }     else { $this->setUnitPriceUnit(null); }
        // copy updated aside
        if(
            empty($this->getOtherText())
            && isset($input['origin_updated']) && !empty($input['origin_updated']) && empty(preg_match("/\s+(?:00\:?){3}/uim", $input['origin_updated']->format('Y-m-d H:i:s')))
            && isset($input['origin_host']) && !empty($input['origin_host']) && $input['origin_host'] == 'reality.bazos.sk'
        ) {
            $this->setOtherText(json_encode($input['origin_updated']));
        }
        // load saved updated
        if(
            !empty($this->getOtherText())
            && isset($input['origin_updated']) && !empty($input['origin_updated']) && !empty(preg_match("/\s+(?:00\:?){3}/uim", $input['origin_updated']->format('Y-m-d H:i:s')))
            && isset($input['origin_host']) && !empty($input['origin_host']) && $input['origin_host'] == 'reality.bazos.sk'
        ) {
            $date = json_decode($this->getOtherText());
            if($date->format('Y-m-d') == $input['origin_updated']->format('Y-m-d')) {
                $this->setOriginUpdated($date);
            }
        }
    }
}
