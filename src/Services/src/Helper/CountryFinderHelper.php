<?php

declare(strict_types=1);

namespace Services\Helper;

/*
 * 2021-03-03T14:00:00+01:00
 *
 * Usage:
 *
 *     Functions:
 *       customKeywords('file_path')
 *       keywordSearch('string')
 *       locate(['longitude' => '12.34', 'latitude' => '56.78'])
 *
 *     View some results:
 *       $cf = $cf->setCountry('sk')->locate(['longitude' => '12.34', 'latitude' => '56.78']);
 *       var_dump($cf);
 */
class CountryFinderHelper {
    use AbstractServicesTraitClass;

    private $country_data = [];
    private $dsf = 'geo.json';
    private $keywords = [];
    private $keywords_file = '';
    private $point = [];
    private $polygon = [];
    private $repeted = [];
    private $return = '';
    private $special = [];

    public function __construct(array $datasets, $logger, $slug) {
        $this->setDatasets($datasets);
        $this->setLogger($logger);
        $this->setSlug($slug);
        $this->configCheck();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getCountryData(): array { return $this->country_data; }
    private function setCountryData(array $input): void { $this->country_data = $input; }

    private function getKeywords(): array { return $this->keywords; }
    private function setKeywords(array $input): void { $this->keywords = $input; }

    private function getKeywordsFile(): string { return $this->keywords_file; }
    private function setKeywordsFile(string $input): void { $this->keywords_file = $input; }

    private function getPoint(): array { return $this->point; }
    private function setPoint(array $input): void { $this->point = $input; }

    private function getPolygon(): array { return $this->polygon; }
    private function setPolygon(array $input): void { $this->polygon = $input; }

    private function getReturn(): string { return $this->return; }
    private function setReturn(string $input): void { $this->return = $input; }

    private function getRepeted(): array { return $this->repeted; }
    private function setRepeted(array $input): void { $this->repeted = $input; }

    private function getSpecial(): array { return $this->special; }
    private function setSpecial(array $input): void { $this->special = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(array $point = []): void {
        if(!$this->countryCodeCheck()) { return; }
        $this->loadDataset();

        $this->kwf = 'country_keywords_'.$this->getCountry().'.json';
        $this->dir = $this->getDatasets()['shared']['i18n_path'].$this->getCountry().DIRECTORY_SEPARATOR;

        if(empty($this->getKeywordsFile())) {
            $this->setKeywordsFile($this->dir.$this->kwf);
        }

        if(file_exists($this->getKeywordsFile())) {
            $string = file_get_contents($this->getKeywordsFile());
            $array = json_decode($string, true);
            if(!isset($array['dataset'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `dataset` attribute from dataset file `{$this->getKeywordsFile()}`"
                    ],
                    'logger' => get_called_class().'::_init'
                ];
                $this->getLogger()->setData($data);
                throw new Exception("Missing `dataset` attribute from dataset file `{$this->getKeywordsFile()}`");
            }
            if(!isset($array['dataset']['_keywords'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `_keywords` attribute from dataset file `{$this->getKeywordsFile()}`"
                    ],
                    'logger' => get_called_class().'::_init'
                ];
                $this->getLogger()->setData($data);
                throw new Exception("Missing `_keywords` attribute from dataset file `{$this->getKeywordsFile()}`");
            }
            $this->setKeywords($array['dataset']['_keywords']);
            if(!isset($array['dataset']['_repeted'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `_repeted` attribute from dataset file `{$this->getKeywordsFile()}`"
                    ],
                    'logger' => get_called_class().'::_init'
                ];
                $this->getLogger()->setData($data);
                throw new Exception("Missing `_repeted` attribute from dataset file `{$this->getKeywordsFile()}`");
            }
            $this->setRepeted($array['dataset']['_repeted']);
            if(!isset($array['dataset']['_special'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `_special` attribute from dataset file `{$this->getKeywordsFile()}`"
                    ],
                    'logger' => get_called_class().'::_init'
                ];
                $this->getLogger()->setData($data);
                throw new Exception("Missing `_special` attribute from dataset file `{$this->getKeywordsFile()}`");
            }
            $this->setSpecial($array['dataset']['_special']);
        } else {
            $data = [
                'message' => [
                    'description' => 'Missing dataset file',
                    'details' => $this->getKeywordsFile()
                ],
                'logger' => get_called_class().'::_init'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing dataset file: `{$this->getKeywordsFile()}`");
        }

        if(!empty($this->getData()[strtoupper($this->getCountry())])) {
            $this->setCountryData($this->getData()[strtoupper($this->getCountry())]);
        }
        if(!empty($point)) {
            $p[0] = $point['longitude'];
            $p[1] = $point['latitude'];
            $this->setPoint($p);
        }
    }

    private function getByKeyword($input) {
        if(!is_string($input)) { return; }
        $input = $this->slugify($input);
        if(strlen($input) < 3) { return; }
        $slug = $this->getUniqueSearchString($input, true, false);
        $return = [];

        $bypass = false;
        if(!empty($this->getSpecial())) {
            foreach($this->getSpecial() as $special) {
                foreach($slug as $search) {
                    if(0 == substr_compare($search, $special, 0, strlen($special))) {
                        $ret_ = [];
                        foreach($this->getKeywords() as $country => $keywords) {
                            foreach($keywords as $word) {
                                if($word == $special) {
                                    $ret_[] = [
                                        $country => [
                                            'search' => $search,
                                            'word' => $word,
                                            'keywords' => $keywords
                                        ]
                                    ];
                                    continue 2;
                                }
                            }
                        }
                        $bypass = true;
                        continue 2;
                    }
                }
            }
        }

        if($bypass) {
            $this->setData($ret_);
        }

        else {
            // ret_1 logic OK
            $ret_1 = [];
            $rank = [];
            foreach($this->getKeywords() as $country => $keywords) {
                foreach($keywords as $word) {
                    foreach($slug as $search) {
                        // compare from beginning of the words
                        if(0 == substr_compare($search, $word, 0, strlen($word))) {
                            $ret_1[] = [
                                $country => [
                                    'search' => $search,
                                    'word' => $word,
                                    'keywords' => $keywords
                                ]
                            ];
                            if(isset($rank[$country])) {
                                $rank[$country] = $rank[$country] + 1;
                            } else {
                                $rank[$country] = 1;
                            }
                        }
                    }
                }
            }

            $ret_2 = [];
            $ret_22 = [];
            $ret_3 = [];
            $ret_33 = [];
            if(!empty($ret_1)) {
                foreach($ret_1 as $item) {
                    $lookup = array_search(array_column($item, 'word')[0], $this->getRepeted());
                    $keywords = $this->getUniqueSearchString(implode(' ', array_column($item, 'keywords')[0]), true);
                    $word = array_column($item, 'word')[0];
                    $country = key($item);

                    // repeted match
                    if($lookup !== false) {
                        // multiple keywords
                        if(count($keywords) > 1) {
                            $i = $this->matchAllKeywords($keywords, $slug);
                            if($i == count($keywords)) {
                                if(!$this->isKeyInArray($ret_2, key($item))) {
                                    $ret_2[] = $item;
                                }
                            }
                        }
                        // single keyword
                        else {
                            if(!$this->isKeyInArray($ret_22, key($item))) {
                                $ret_22[] = $item;
                            }
                        }
                    }

                    // non-repeted match
                    else {
                        // multiple keywords
                        if(count($keywords) > 1) {
                            $j = $this->matchAllKeywords($keywords, $slug);
                            if($j == count($keywords)) {
                                if(!$this->isKeyInArray($ret_3, key($item))) {
                                    $ret_3[] = $item;
                                }
                            }
                        }
                        // single keyword
                        else {
                            if(!$this->isKeyInArray($ret_33, key($item))) {
                                $ret_33[] = $item;
                            }
                        }
                    }
                }
            }

            // multi
            $ret_m = null;

            if(count($ret_2) == 0 && count($ret_3) == 1) { $ret_m = $ret_3[0]; }
            if(count($ret_2) == 1 && count($ret_3) == 0) { $ret_m = $ret_2[0]; }
            if(count($ret_2) == 1 && count($ret_3) == 1) {
                if(key($ret_2[0]) == key($ret_3[0])) { $ret_m = $ret_2[0]; }
                else {
                    if($rank[key($ret_2[0])] > $rank[key($ret_3[0])]) { $ret_m = $ret_2[0]; }
                    elseif($rank[key($ret_2[0])] < $rank[key($ret_3[0])]) { $ret_m = $ret_3[0]; }
                    else { $ret_m = false; }
                }
            }

            // single
            $ret_s = null;
            if(count($ret_22) == 0 && count($ret_33) == 1) { $ret_s = $ret_33[0]; }
            if(count($ret_22) == 1 && count($ret_33) == 0) { $ret_s = $ret_22[0]; }
            if(count($ret_22) == 1 && count($ret_33) == 1) {
                if(key($ret_22[0]) == key($ret_33[0])) { $ret_s = $ret_22[0]; }
                else {
                    if($rank[key($ret_22[0])] > $rank[key($ret_33[0])]) { $ret_s = $ret_22[0]; }
                    elseif($rank[key($ret_22[0])] < $rank[key($ret_33[0])]) { $ret_s = $ret_33[0]; }
                    else { $ret_s = false; }
                }
            }

            // main
            $ret_main = [];
            if($ret_m == null && !empty($ret_s)) { $ret_main = [$ret_s]; }
            elseif(!empty($ret_m) && $ret_s == null) { $ret_main = [$ret_m]; }
            $this->setData($ret_main);
        }
    }

    private function isKeyInArray(array $array, string $string): ?bool {
        if(empty($array) || empty($string)) { return null; }
        $return = false;
        foreach($array as $key => $value) {
            if($string == $key) {
                $return = true;
                continue;
            }
        }
        return $return;
    }

    private function loopAll(): void {
        if(empty($this->getPoint())) {
            $this->setReturn('no-point');
            return;
        }
        foreach($this->getData() as $key => $value) {
            if($key != strtoupper($this->getCountry())) {
                if($value['geometry']['type'] == 'Polygon') {
                    $this->setPolygon($value['geometry']['coordinates'][0]);
                    $answer = $this->run();
                    if($answer == 'inside') {
                        $this->setReturn($key);
                        return;
                    }
                    if($answer == 'boundary') {
                        $this->setReturn($answer);
                        return;
                    }
                }
                if($value['geometry']['type'] == 'MultiPolygon') {
                    foreach($value['geometry']['coordinates'] as $item) {
                        $this->setPolygon($item[0]);
                        $answer = $this->run();
                        if($answer == 'inside') {
                            $this->setReturn($key);
                            return;
                        }
                        if($answer == 'boundary') {
                            $this->setReturn($answer);
                            return;
                        }
                    }
                }
            }
        }
        $this->setReturn('not-found');
    }

    private function loopOne(): void {
        if(empty($this->getPoint())) {
            $this->setReturn('no-point');
            return;
        }
        if($this->getCountryData()['geometry']['type'] == 'Polygon') {
            $this->setPolygon($this->getCountryData()['geometry']['coordinates'][0]);
            $answer = $this->run();
            if($answer == 'inside') {
                $this->setReturn(strtoupper($this->getCountry()));
                return;
            } else {
                $this->setReturn($answer);
                return;
            }
        }
        if($this->getCountryData()['geometry']['type'] == 'MultiPolygon') {
            foreach($this->getCountryData()['geometry']['coordinates'] as $item) {
                $this->setPolygon($item[0]);
                $answer = $this->run();
                if($answer == 'on-boundary') {
                    $this->setReturn($answer);
                    return;
                }
                if($answer == 'inside') {
                    $this->setReturn(strtoupper($this->getCountry()));
                    return;
                }
            }
            $this->setReturn('outside');
        }
    }

    private function matchAllKeywords(array $keywords, array $slug): ?int {
        if(empty($keywords) || empty($slug)) { return null; }
        $hits = [];
        foreach($keywords as $keyword) {
            $i = 0;
            foreach($slug as $search) {
                // compare from beginning of the words
                if(0 == substr_compare($search, $keyword, 0, strlen($keyword))) {
                    $i++;
                }
            }
            $hits[] = $i;
        }
        $j = 0;
        foreach($hits as $hit) { if($hit > 0) { $j++; } }
        return $j;
    }

    private function run(): string {
        $intersections = 0;
        for($i=1; $i < count($this->getPolygon()); $i++) {
            $vertex1 = $this->getPolygon()[$i-1];
            $vertex2 = $this->getPolygon()[$i];
            if($vertex1[1] == $vertex2[1] && $vertex1[1] == $this->getPoint()[1] && $this->getPoint()[0] > min($vertex1[0], $vertex2[0]) && $this->getPoint()[0] < max($vertex1[0], $vertex2[0])) { return "on-boundary"; }
            if($this->getPoint()[1] > min($vertex1[1], $vertex2[1]) && $this->getPoint()[1] <= max($vertex1[1], $vertex2[1]) && $this->getPoint()[0] <= max($vertex1[0], $vertex2[0]) && $vertex1[1] != $vertex2[1]) {
                $xinters = ($this->getPoint()[1] - $vertex1[1]) * ($vertex2[0] - $vertex1[0]) / ($vertex2[1] - $vertex1[1]) + $vertex1[0];
                if($xinters == $this->getPoint()[0]) { return 'on-boundary'; }
                if($vertex1[0] == $vertex2[0] || $this->getPoint()[0] <= $xinters) { $intersections++; }
            }
        }
        if($intersections % 2 != 0) { return 'inside'; }
        else { return 'outside'; }
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function customKeywords(string $file): self {
        $this->setKeywordsFile($file);
        $this->_init();
        return $this;
    }

    public function keywordSearch(string $search): self {
        $this->_init();
        $this->getByKeyword($search);
        return $this;
    }

    public function locate(array $point = []): string {
        $this->_init($point);
        if(!empty($this->getCountryData())) { $this->loopOne(); }
        if($this->getReturn() == 'outside') { $this->loopAll(); }
        return $this->getReturn();
    }
}
