<?php

declare(strict_types=1);

namespace TargetReality\Controller;

use Matcher\Controller\AbstractMatcherTraitClass;

class MatcherRealityController {
    use AbstractMatcherTraitClass;

    public function __construct(
        $address,
        $area,
        $category,
        $country_finder,
        $currency,
        $logger,
        $slug,
        $type
    ) {
        $this->setAddress($address);
        $this->setArea($area);
        $this->setCategory($category);
        $this->setCountryFinder($country_finder);
        $this->setCurrency($currency);
        $this->setLogger($logger);
        $this->setSlug($slug);
        $this->setType($type);
    }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function matchArea(): void {
        $regex = "/^(\d{1,3}[\s\.,](\d{3}[\s\.,])*\d{3}|\d+)([\.,]\d+)?\s+(.+)$/";
        if(!empty($this->getData()['area_total'])) {
            $numbers = $this->findFormattedPriceNumber($this->getData()['area_total']);
            preg_match_all($regex, trim($this->getData()['area_total']), $units);
            $this->push([
                'area_total' => $this->getArea()
                                    ->value((string) $numbers[0])
                                    ->from((string) $units[count($units)-1][0])
                                    ->convertArea(),
                'area_unit' => 'm2'
            ]);
        }
        if(!empty($this->getData()['area_usable'])) {
            $numbers = $this->findFormattedPriceNumber($this->getData()['area_usable']);
            preg_match_all($regex, trim($this->getData()['area_usable']), $units);
            $this->push([
                'area_usable' => $this->getArea()
                                    ->value((string) $numbers[0])
                                    ->from((string) $units[count($units)-1][0])
                                    ->convertArea(),
                'area_unit' => 'm2'
            ]);
        }
        if(!empty($this->getData()['area_used'])) {
            $numbers = $this->findFormattedPriceNumber($this->getData()['area_used']);
            preg_match_all($regex, trim($this->getData()['area_used']), $units);
            $this->push([
                'area_used' => $this->getArea()
                                    ->value((string) $numbers[0])
                                    ->from((string) $units[count($units)-1][0])
                                    ->convertArea(),
                'area_unit' => 'm2'
            ]);
        }
    }

    // dependency
    private function matchBreadcrumbs(): void {
        if(!empty($this->getData()['breadcrumbs'])) {
            $count = count($this->getData()['breadcrumbs']);
            $url = $this->getData()['breadcrumbs'][$count - 2]['item'];
            $path = parse_url($url, PHP_URL_PATH);
            $path = preg_replace(["/^\//", "/\/$/"], ['', ''], $path);
            $array = explode('/', $path);
            $count = count($array);

            // log unexpected breadcrumbs
            if($count < 3 || $count > 5) {
                $data = [
                    'message' => [
                        'description' => 'Matcher warning',
                        'details' => [
                            "`breadcrumbs` unexpected size",
                            $this->getData()['breadcrumbs'],
                            'count' => $count
                        ],
                    ],
                    'url' => $this->getData()['origin_url'],
                    'logger' => get_called_class().'::matchBreadcrumbs'
                ];
                $this->getLogger()->setData($data);
                $this->push(['errors' => true]);
            }

            $is_main_category = -1;
            $is_sub_category = -1;
            $is_main_location = -1;
            $is_type = -1;
            $is_condition = -1;

            $return = [];
            if($count == 3) {
                $is_main_category = 0;
                $is_main_location = 1;
                $is_type = 2;
            }
            if($count >= 4) {
                $is_main_category = 0;
                $is_sub_category = 1;
                $is_main_location = 2;
                $is_type = 3;
                if($count > 4) {
                    $is_condition = 4;
                }
            }


            if($is_main_category > -1) { $return['main_category'] = $this->sanitize($array[$is_main_category]); }
            if($is_sub_category > -1) { $return['sub_category'] = $this->sanitize($array[$is_sub_category]); }
            if($is_main_location > -1) { $return['main_location'] = $this->sanitize($array[$is_main_location]); }
            if($is_type > -1) { $return['type'] = $this->sanitize($array[$is_type]); }
            if($is_condition > -1) { $return['condition'] = $this->sanitize($array[$is_condition]); }

            if(!empty($return)) { $this->push($return); }
        }
    }

    private function matchCategoryType(): void {
        $string = '';
        if(!empty($this->getData()['main_category'])) {
            $string .= $this->getData()['main_category'].' ';
        }
        if(!empty($this->getData()['sub_category'])) {
            $string .= $this->getData()['sub_category'];
        }
        $string = $this->sanitize($string);

        $main = '';
        if(!empty($this->getData()['main_category'])) {
            $main .= $this->getData()['main_category'];
        }
        $main = $this->sanitize($main);

        if(!empty($string)) {
            $this->push([
                'category' => $this->getCategory()
                                   ->keywordSearch($string, $main)
                                   ->getOneResult()['name']
            ]);
        } else { $this->push(['errors' => true]); }

        $string = '';
        if(!empty($this->getData()['type'])) {
            $string .= $this->getData()['type'];
        }
        $string = $this->sanitize($string);

        if(!empty($string)) {
            $this->push([
                'type' => $this
                            ->getType()
                            ->search(trim($string))
                            ->getOneResult()['name']
            ]);
        } else { $this->push(['errors' => true]); }
    }

    private function matchCountryByGps(): ?string {
        $country = '';
        $return = null;
        if(!empty($this->getData()['origin_latitude'])) {
            $country = $this->getCountryFinder()->locate([
                'latitude' => $this->formatFloat($this->getData()['origin_latitude']),
                'longitude' => $this->formatFloat($this->getData()['origin_longitude']),
            ]);

            if(!empty($country)) {
                if(strlen($country) > 2) {
                    $data = [
                        'message' => [
                            'description' => 'geoJSON response',
                            'details' => $country
                        ],
                        'url' => $this->getData()['origin_url'],
                        'logger' => get_called_class().'::matchCountryByGps'
                    ];
                    $this->getLogger()->setData($data);
                } else {
                    $return = $country;
                }
            }
        }
        return $return;
    }

    // dependency
    private function matchData(): void {
        if(!empty($this->getData()['data'])) {
            $return = [];
            $map = [
                'product' => [
                    'description' => 'description',
                    'offers|price' => 'price',
                    'offers|priceCurrency' => 'currency',
                    'offers|seller|name' => 'contact_name',
                ],
                'singlefamilyresidence' => [
                    'telephone' => 'contact_phone',
                    'image' => 'origin_image_path',
                    'floorSize|value' => 'area',
                    'floorSize|unitText' => 'area_unit',
                ],
                'residence' => [
                    'address|streetAddress' => 'origin_street',
                    'address|addressLocality' => 'origin_location',
                    'address|addressRegion' => 'origin_country',
                ],
                'breadcrumblist' => [
                    'itemListElement' => 'breadcrumbs',
                ],
            ];
            foreach($this->getData()['data'] as $item) {
                foreach($map as $key => $value) {
                    if($this->slugify($item['@type']) == $key) {
                        foreach($value as $key1 => $value1) {
                            $path = explode('|', $key1);
                            if(count($path) == 1) { if(isset($item[$path[0]])) { $return[$value1] = $item[$path[0]]; } }
                            if(count($path) == 2) { if(isset($item[$path[0]][$path[1]])) { $return[$value1] = $item[$path[0]][$path[1]]; } }
                            if(count($path) == 3) { if(isset($item[$path[0]][$path[1]][$path[2]])) { $return[$value1] = $item[$path[0]][$path[1]][$path[2]]; } }
                        }
                    }
                }
            }
            if(!empty($return)) { $this->push($return); }
        }
    }

    // dependency
    private function matchGps(): void {
        $lat = null;
        $lon = null;
        if(isset($this->getData()['latitude']) && !is_null($this->getData()['latitude'])) { $lat = $this->formatFloat($this->getData()['latitude']); }
        if(isset($this->getData()['longitude']) && !is_null($this->getData()['longitude'])) { $lon = $this->formatFloat($this->getData()['longitude']); }
        if(!is_null($lat) && !is_null($lon)) {
            $this->push([
                'origin_latitude' => $lat,
                'origin_longitude' => $lon
            ]);
        }
    }

    private function matchHostId(): void {
        preg_match("/\/([A-Z]{2}\w{7}\-\w{2}\-\w{6})\//", $this->getData()['origin_url'], $id);
        $this->push([
            'origin_id' => $id[1],
            'origin_host' => parse_url($this->getData()['origin_url'], PHP_URL_HOST)
        ]);
    }

    private function matchIsPrivate(): void {
        $is_private = true;
        if(true == strstr($this->getData()['origin_id'], 'RE')) {
            $is_private = !$is_private;
        }
        $this->push(['is_private' => $is_private]);
    }

    private function matchLocation(): void {
        $this->push([
            'country' => null,
            'county' => null,
            'district' => null,
            'town' => null,
            'town_part' => null,
            'street' => null,
            'postcode' => null,
            'latitude' => null,
            'longitude' => null,
            'score' => 0,
            'score_details' => [
                'no-location-found'
            ]
        ]);

        $m_c = '';

        // match country based on origin_country
        if(empty($m_c) && !empty($this->getData()['origin_country'])) {
            $ret = $this->getCountryFinder()
                        ->keywordSearch($this->sanitize($this->getData()['origin_country']))
                        ->getOneResult();
            if(!empty($ret)) { $m_c = key($ret); }
        }

        // match country based on unique currency
        if(empty($m_c) && !empty($this->getData()['currency'])) {
            $countries = $this->getCurrency()->search($this->getData()['currency'])->getOneResult()['countries'];
            if(count($countries) == 1) {
                $m_c = $countries[0];
            }
        }

        // skip match address if country was detected successfully based on used currency
        if(!empty($m_c) && strtolower($m_c) != $this->getCountry()) {
            goto m_c_continue;
        }

        // match address
        if($this->matchLocationProbe()) {
            $location_street = '';
            if(!empty($this->getData()['origin_street'])) { $location_street = $this->getData()['origin_street']; }
            $answer = $this->matchAddress($location_street);
            if(!empty($answer)) {
                $score = 100;
                $answer['score'] = $score - $answer['score_reduce_by'];
            } else {
                $data = [
                    'message' => [
                        'description' => 'address-not-found',
                        'details' => 'matchAddress()'
                    ],
                    'url' => $this->getData()['origin_url'],
                    'logger' => get_called_class().'::matchLocation'
                ];
                $this->getLogger()->setData($data);
            }
            $answer['country'] = strtoupper($this->getCountry());
        }

        // match country
        else {
            m_c_continue:

            // i18n match country in title, etc.
            if(empty($m_c)) {
                $string = '';
                if(!empty($this->getData()['main_location'])) {
                    $string .= $this->getData()['main_location'].' ';
                }
                if(!empty($this->getData()['location'])) {
                    $string .= $this->getData()['location'].' ';
                }
                if(!empty($this->getData()['name'])) {
                    $string .= $this->getData()['name'];
                }
                $string = $this->sanitize($string);

                $ret = $this->getCountryFinder()
                            ->keywordSearch($string)
                            ->getOneResult();
                if(!empty($ret)) { $m_c = key($ret); }
            }

            // match country using GPS coordinates
            if(empty($m_c)) {
                $ret = $this->matchCountryByGps();
                if(!empty($ret)) { $m_c = $ret; }
            }

            if(!empty($m_c)) {
                $answer['country'] = $m_c;
                $answer['score_details'] = [];
            } else {
                $answer['score_details'] = ['no-country-found'];
                $answer['errors'] = true;
            }
        }

        // Twisted GPS data correction
        if(isset($answer['country']) && strtolower($answer['country']) == strtolower($this->getCountry())) {
            $lat = null;
            $lon = null;
            if(isset($this->getData()['origin_latitude']) && $this->getData()['origin_latitude'] < 30) {
                $lon = $this->getData()['origin_latitude'];
            }
            if(isset($this->getData()['origin_longitude']) && $this->getData()['origin_longitude'] > 30) {
                $lat = $this->getData()['origin_longitude'];
            }
            if(!is_null($lat) && !is_null($lon)) {
                $this->push([
                    'origin_latitude' => $lat,
                    'origin_longitude' => $lon
                ]);
            }
        }
        if(isset($this->getData()['origin_latitude']) && !empty($this->getData()['origin_latitude'])) {
            $answer['latitude']  = $this->getData()['origin_latitude'];
        }
        if(isset($this->getData()['origin_longitude']) && !empty($this->getData()['origin_longitude'])) {
            $answer['longitude'] = $this->getData()['origin_longitude'];
        }
        $this->push($answer);
    }

    private function matchLocationProbe(): bool {
        if(isset($this->getAddress()->getDatasets()['address'][$this->getCountry()])) {
            $counter = 0;

            $string_district = '';
            $string_town = '';

            // 0-0
            if(empty($string_district) && empty($string_town)) {
                $columns = ['district', 'town'];
                if(!empty($this->getData()['origin_location'])) {
                    if(mb_strlen($this->getData()['origin_location']) > 2) {
                        foreach($columns as $item) {
                            if(is_int(array_search($this->slugify($this->getData()['origin_location']), array_unique(array_column($this->getAddress()->reset()->strictSearch($this->getData()['origin_location'], $item)->getResults(), $item.'_slug'))))) {
                                if($item == 'district') { $this->district = array_unique(array_column($this->getAddress()->getResults(), 'district'))[0]; }
                                if($item == 'town') { $this->town = array_unique(array_column($this->getAddress()->getResults(), 'town'))[0]; }
                                $counter++;
                            }
                        }
                    }
                } elseif(!empty($this->getData()['main_location'])) {
                    if(mb_strlen($this->getData()['main_location']) > 2) {
                        foreach($columns as $item) {
                            if(is_int(array_search($this->slugify($this->getData()['main_location']), array_unique(array_column($this->getAddress()->reset()->strictSearch($this->getData()['main_location'], $item, true)->getResults(), $item.'_slug'))))) {
                                if($item == 'district') { $this->district = array_unique(array_column($this->getAddress()->getResults(), 'district'))[0]; }
                                if($item == 'town') { $this->town = array_unique(array_column($this->getAddress()->getResults(), 'town'))[0]; }
                                $counter++;
                            }
                        }
                    }
                }
            }

            if($counter > 0) { return true; }
        }
        return false;
    }

    private function matchMeta(): void {
        $meta_map = [
            'breadcrumbs',
            'main_category',
            'main_location',
            'origin_country',
            'origin_latitude',
            'origin_longitude',
            'origin_street',
            'sub_category',
        ];
        $meta = [];
        foreach($meta_map as $item) {
            if(isset($this->getData()[$item])) { $meta[$item] = $this->getData()[$item]; }
        }
        $this->push(['meta' => $meta]);
    }

    private function matchPhone(): void {
        $push = null;
        if(!empty($this->getData()['contact_phone'])) {
            $push= $this->noWhiteSpace($this->getData()['contact_phone']);
        }
        $this->push(['contact_phone' => $push]);
    }

    private function matchPrices(): void {
        if(empty($this->getData()['price']) || true == stristr(mb_strtolower($this->getData()['price']), 'cena')) {
            $this->push([
                'currency' => null,
                'price' => null,
                'unit_price' => null,
                'unit_price_unit' => null,
                'price_type' => null
            ]);
        } else {
            $currency = null;
            $price = null;
            $unit_price = null;
            $unit_price_unit = null;
            $price_type = null;
            if(!empty($this->getData()['currency'])) {
                $symbol = explode('/', $this->getData()['currency']);
                if(!empty($symbol)) {
                    $currency = $this->getCurrency()
                                     ->search($symbol[0])
                                     ->getOneResult()['alpha3'];
                    if(!empty($symbol[1])) {
                        $price_type_map = [
                            'den'    => 'daily',
                            'tyz' => 'weekly',
                            'tyzden' => 'weekly',
                            'mes' => 'monthly',
                            'mesiac' => 'monthly',
                            'rok'    => 'yearly',
                            ''       => '',
                        ];
                        $price_type = $price_type_map[$this->slugify($symbol[1])];
                        if(empty($price_type) && ($this->getData()['type'] == 'to-let' || $this->getData()['type'] == 'sublet')) {
                            $price_type = 'monthly';
                        }
                    }
                }
            }
            if(!empty($this->getData()['price'])) {
                $prices = $this->findFormattedPriceNumber($this->getData()['price']);
                $price = (!empty($prices) && isset($prices[0])) ? (string) $prices[0] : null;
                // if(count($prices) == 4) { $price = (string) (((!empty($prices) && isset($prices[0])) ? (string) $prices[0] : null) + ((!empty($prices) && isset($prices[1])) ? (string) $prices[1] : null)); }
            }
            if(!empty($this->getData()['unit_price'])) {
                $unit_prices = $this->findFormattedPriceNumber($this->getData()['unit_price']);
                $unit_price = (!empty($unit_prices) && isset($unit_prices[0])) ? (string) $unit_prices[0] : null;
                preg_match_all("/\s\D+\/\D+\/\b(\w+)\b|\s\D+\/\b(\w+)\b/", trim($this->getData()['unit_price']), $units);
                $unit_price_unit = (!empty($units) && isset($units[1][0])) ? (string) $units[1][0] : null;
                if(count($prices) > 0 && count($prices) < 3 && empty($unit_price_unit)) { $unit_price_unit = (!empty($units) && isset($units[2][0])) ? (string) $units[2][0] : null; }
                if($unit_price_unit == 'den' || $unit_price_unit == 'tyz' || $unit_price_unit == 'tyzden' || $unit_price_unit == 'mes' || $unit_price_unit == 'mesiac' || $unit_price_unit == 'rok') { $unit_price_unit = ''; }
                
                if(!empty($unit_price) && !empty($unit_price_unit)) {
                    $unit_price = $this->getArea()
                                       ->value((string) $unit_price)
                                       ->from((string) $unit_price_unit)
                                       ->convertUnitPrice();
                }
            }
            $this->push([
                'currency' => $currency,
                'price' => $price,
                'price_type' => $price_type,
                'unit_price' => $unit_price,
                'unit_price_unit' => (is_null($unit_price)) ? null : 'm2'
            ]);
        }
    }

    private function matchStatus(): void {
        if(!empty($this->getData()['name'])) {
            $slug = $this->slugify($this->getData()['name']);
            if (true == stristr($slug, 'rezervovane')) {
                $this->push(['origin_status' => 'reserved']);
            } else {
                $this->push(['origin_status' => '']);
            }
        }
    }

    private function run(): void {
        // Dependency, should be applied first!
        $this->matchData();
        $this->matchBreadcrumbs();
        $this->matchGps();

        // Order
        $this->matchArea();
        $this->matchCategoryType();
        $this->matchIsPrivate();
        $this->matchPhone();
        $this->matchPrices();
        $this->matchStatus();

        // Should be applied last!
        $this->matchLocation();
        $this->matchMeta();
    }

    private function getUrlId(): void {
        $this->matchHostId();
    }
}
