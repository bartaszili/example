<?php

declare(strict_types=1);

namespace Services\Helper;

use \Exception;

/*
 * Acts as cross-reference sorter to determine category based on keywords.
 *
 * 2021-02-10T15:10:00+01:00
 *
 * Usage:
 *
 *     Functions:
 *       customDataset('file_path')
 *       customKeywords('file_path')
 *       getAll()
 *       getOneResult()
 *       getResults()
 *       keywordSearch('Kancelárie, administratívne priestory') // search words, smart keywords search
 *       search('priestor') // search word, matches i18n language slug
 *       setCountry('sk')
 *
 *     View some results:
 *       $category = $category->setCountry('sk')->keywordSearch('Kancelárie, administratívne priestory')->getOneResult();
 *       var_dump($category);
 */
class CategoryHelper {
    use AbstractServicesTraitClass;

    private $dsf = 'category.json';
    private $ignore = [];
    private $keywords = [];
    private $keywords_file = '';
    private $main_categories = [];
    private $map = [];
    private $sub_categories = [];

    public function __construct(array $datasets, $logger, $slug) {
        $this->setDatasets($datasets);
        $this->setLogger($logger);
        $this->setSlug($slug);
        $this->configCheck();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getIgnore(): array { return $this->ignore; }
    private function setIgnore(array $input): void { $this->ignore = $input; }

    private function getKeywords(): array { return $this->keywords; }
    private function setKeywords(array $input): void { $this->keywords = $input; }

    private function getKeywordsFile(): string { return $this->keywords_file; }
    private function setKeywordsFile(string $input): void { $this->keywords_file = $input; }

    private function getMainCategories(): array { return $this->main_categories; }
    private function setMainCategories(array $input): void { $this->main_categories = $input; }

    private function getMap(): array { return $this->map; }
    private function setMap(array $input): void { $this->map = $input; }

    private function getSubCategories(): array { return $this->sub_categories; }
    private function setSubCategories(array $input): void { $this->sub_categories = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        if(!$this->countryCodeCheck()) { return; }
        $this->loadDataset();

        $this->kwf = 'category_keywords_'.$this->getCountry().'.json';
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
            if(!isset($array['dataset']['_map'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `_map` attribute from dataset file `{$this->getKeywordsFile()}`"
                    ],
                    'logger' => get_called_class().'::_init'
                ];
                $this->getLogger()->setData($data);
                throw new Exception("Missing `_map` attribute from dataset file `{$this->getKeywordsFile()}`");
            }
            $this->setMap($array['dataset']['_map']);
            $this->setMainCategories(array_keys($array['dataset']['_map']));
            if(!isset($array['dataset']['_ignore'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `_ignore` attribute from dataset file `{$this->getKeywordsFile()}`"
                    ],
                    'logger' => get_called_class().'::_init'
                ];
                $this->getLogger()->setData($data);
                throw new Exception("Missing `_ignore` attribute from dataset file `{$this->getKeywordsFile()}`");
            }
            $this->setIgnore($array['dataset']['_ignore']);
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
    }

    private function findMainCategory(string $category) {
        foreach($this->getMap() as $main_cat => $sub_cat) {
            foreach($sub_cat as $sub_key => $sub_item) {
                if(is_string($sub_item)) {
                    if($category == $sub_item) {
                        return $main_cat;
                    }
                } elseif(is_array($sub_item)) {
                    foreach($sub_item as $sub_value) {
                        if($category == $sub_value) {
                            return $main_cat;
                        }
                    }
                }
            }
        }
    }

    private function getByI18n($input) {
        if(!is_string($input)) { return; }
        $input = $this->slugify($input);
        if(strlen($input) < 3) { return; }
        $slug = $this->getUniqueSearchString($input);
        $return = [];
        foreach($this->getData() as $item) {
            foreach($slug as $search) {
                if(true == stristr($item[$this->getCountry().'_slug'], $search)) {
                    $return[] = $item;
                    continue 2;
                }
            }
        }
        if(!empty($return)) $this->setData($return);
    }

    private function getByKeyword(string $input, string $main_input = '') {
        if(!is_string($input)) { return; }
        $input = $this->slugify($input);
        if(strlen($input) < 3) { return; }
        $slug = $this->getUniqueSearchString($input);

        $main_keyword = $this->slugify($main_input);
        $main_keyword_slug = $this->getUniqueSearchString($main_keyword);

        $main_slug = [];
        if(!empty($main_keyword_slug)) {
            $main_slug[] = $main_keyword_slug;
        }
        if(!empty($slug)) {
            $main_slug[] = $slug;
        }

        foreach($main_slug as $main_item) {
            if(count($this->getMainCategories()) != 1) {
                // Try to determine main_category
                foreach($this->getMainCategories() as $cat) {
                    foreach($main_item as $key => $search) {
                        $tmp = $this->searchLoop($search, $cat);
                        if(!empty($tmp)) {
                            $this->setMainCategories($tmp);
                            break 2;
                        }
                    }
                }

                // Try again to determine main_category if no previous success, loop all
                if(count($this->getMainCategories()) != 1) {
                    $hits = [];
                    foreach(array_column($this->getData(), 'name') as $item) {
                        foreach($main_item as $search) {
                            $tmp = $this->searchLoop($search, $item);
                            if(!empty($tmp)) {
                                if(empty($hits[$item])) { $hits[$item] = 1; }
                                else { $hits[$item] += 1; }
                            }
                        }
                    }
                    arsort($hits);
                    reset($hits);
                    $main_hits = [];
                    foreach($hits as $key => $value) {
                        $tmp = $this->findMainCategory($key);
                        if(!empty($tmp)) {
                            if(empty($main_hits[$tmp])) { $main_hits[$tmp] = $value; }
                            else { $main_hits[$tmp] += $value; }
                        }
                    }
                    arsort($main_hits);
                    reset($main_hits);
                    // In case we have several matches with same hits count,
                    // we can't decide, therefore just returning the first hit.
                    // It is not ideal, but good enough, it's still valid.
                    $this->setMainCategories([key($main_hits)]);
                }
            }
        }

        // set others if still nothing found
        if(empty($this->getMainCategories()) || empty($this->getMainCategories()[0])) {
            $this->setMainCategories([
                array_keys($this->getMap())[count($this->getMap())-1]
            ]);
        }

        // set sub_categories with filtered data
        $this->setSubCategories($this->getMap()[$this->getMainCategories()[0]]);

        $hits_arr = [];
        $hits_ign = [];
        $hits_str = [];

        // filter more on sub_category
        foreach($this->getSubCategories() as $key => $sub_cat) {
            if(is_string($sub_cat)) {
                foreach($slug as $search) {
                    $tmp = $this->searchLoop($search, $sub_cat);
                    if(!empty($tmp)) { $hits_str[] = $tmp; }
                }
            } elseif(is_array($sub_cat)) {
                foreach($this->getIgnore() as $ignored) {
                    if($key == $ignored) {
                        foreach($slug as $search) {
                            $tmp = $this->searchLoop($search, $key);
                            if(!empty($tmp)) { $hits_ign[] = $tmp; }
                        }
                        foreach($sub_cat as $sub_item) {
                            foreach($slug as $search) {
                                $tmp = $this->searchLoop($search, $sub_item);
                                if(!empty($tmp)) { $hits_ign[] = $tmp; }
                            }
                        }
                    } else {
                        foreach($sub_cat as $sub_item) {
                            foreach($slug as $search) {
                                $tmp = $this->searchLoop($search, $sub_item);
                                if(!empty($tmp)) { $hits_arr[] = $tmp; }
                            }
                        }
                    }
                }
            }
        }

        $order_hits_arr = [];
        if(!empty($hits_arr)) {
            foreach($hits_arr as $item) {
                if(empty($order_hits_arr[$item[0]])) { $order_hits_arr[$item[0]] = count($item); }
                else { $order_hits_arr[$item[0]] += count($item); }
            }
            arsort($order_hits_arr);
            reset($order_hits_arr);
        }

        $order_hits_ign = [];
        if(!empty($hits_ign)) {
            if($hits_ign[0][0] == $this->getIgnore()[0]) {
                unset($hits_ign[0]);
                arsort($hits_ign);
                reset($hits_ign);
            }
            foreach($hits_ign as $item) {
                if(empty($order_hits_ign[$item[0]])) { $order_hits_ign[$item[0]] = count($item); }
                else { $order_hits_ign[$item[0]] += count($item); }
            }
            arsort($order_hits_ign);
            reset($order_hits_ign);
        }

        $order_hits_str = [];
        if(!empty($hits_str)) {
            foreach($hits_str as $item) {
                if(empty($order_hits_str[$item[0]])) { $order_hits_str[$item[0]] = count($item); }
                else { $order_hits_str[$item[0]] += count($item); }
            }
            arsort($order_hits_str);
            reset($order_hits_str);
        }

        $order_hits_map = [
            'order_hits_arr' => $order_hits_arr,
            'order_hits_ign' => $order_hits_ign,
            'order_hits_str' => $order_hits_str
        ];
        $order = [
            'order_hits_arr' => count($order_hits_arr),
            'order_hits_ign' => count($order_hits_ign),
            'order_hits_str' => count($order_hits_str)
        ];
        arsort($order);
        reset($order);

        if($order['order_hits_arr'] + $order['order_hits_ign'] + $order['order_hits_str'] == 0) {
            // if no sub-category found, then return main-category
            $this->setSubCategories($this->getMainCategories());
        } else {
            if(!empty($order_hits_ign)) {
                $x = false;
                foreach($this->getIgnore() as $ignored) {
                    if(key($order_hits_ign) == $ignored) {
                        $x = true;
                    }
                }
                if($x == true) {
                    // if no sub-sub-category found, then return main-category
                    $this->setSubCategories($this->getMainCategories());
                } else {
                    $this->setSubCategories([key($order_hits_ign)]);
                }
            } else {
                $this->setSubCategories([key($order_hits_map[key($order)])]);
            }
        }

        $return = [];
        foreach($this->getData() as $item) {
            if($item['name'] == $this->getSubCategories()[0]) {
                $return[] = $item;
                break;
            }
        }
        if(!empty($return)) $this->setData($return);
    }

    private function searchLoop(string $slug, string $cat): array {
        if(empty($slug) || empty($cat)) return [];
        $return = [];
        foreach($this->getKeywords() as $key => $value) {
            if($key == $cat) {
                foreach($value as $keyword) {
                    // compare from beginning of the words
                    if(0 == substr_compare($slug, $keyword, 0, strlen($keyword))) {
                    // if(true == stristr($slug, $keyword)) {
                        $return[] = $cat;
                        break 2;
                    }
                }
            }
        }
        return $return;
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function customKeywords(string $file): self {
        $this->setKeywordsFile($file);
        $this->_init();
        return $this;
    }

    public function keywordSearch(string $search, string $main = ''): self {
        $this->_init();
        $this->getByKeyword($search, $main);
        return $this;
    }

    public function search(string $search): self {
        $this->_init();
        $this->getByI18n($search);
        return $this;
    }
}
