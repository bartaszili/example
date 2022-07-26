<?php

declare(strict_types=1);

namespace Parser\Controller;

use App\Handler\AbstractAppTraitMethods;
use Laminas\Dom\Query;
use Logger\Controller\LoggerController;
use Services\Helper\SlugifyHelper;
use \DOMElement;
use \Exception;

class ParserController {
    use AbstractAppTraitMethods;

    private $data = [];
    private Query $dom;
    private DOMElement $dom_item;
    private $dom_name = '';
    private $dom_query = '';
    private $dom_result = '';
    private $exit = false;
    private LoggerController $logger;
    private $parsing_map = [];
    private SlugifyHelper $slug;
    private $target = '';

    public function __construct(LoggerController $logger, SlugifyHelper $slug) {
        $this->setLogger($logger);
        $this->setSlug($slug);
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    public function getData(): array { return $this->data; }
    private function setData(array $input): void { $this->data = $input; }

    private function getDom(): Query { return $this->dom; }
    private function setDom(Query $input): void { $this->dom = $input; }

    private function getDomItem(): DOMElement { return $this->dom_item; }
    private function setDomItem(DOMElement $input): void { $this->dom_item = $input; }

    private function getDomName(): string { return $this->dom_name; }
    private function setDomName(string $input): void { $this->dom_name = $input; }

    private function getDomQuery(): string { return $this->dom_query; }
    private function setDomQuery(string $input): void { $this->dom_query = $input; }

    private function getDomResult(): string { return $this->dom_result; }
    private function setDomResult(string $input): void { $this->dom_result = $input; }

    private function getExit(): bool { return $this->exit; }
    private function setExit(bool $input): void { $this->exit = $input; }

    private function getLogger(): LoggerController { return $this->logger; }
    private function setLogger(LoggerController $input): void { $this->logger = $input; }

    private function getParsingMap(): array { return $this->parsing_map; }
    public function setParsingMap(array $input): self { $this->parsing_map = $input; return $this; }

    private function getSlug(): SlugifyHelper { return $this->slug; }
    private function setSlug(SlugifyHelper $input): void { $this->slug = $input; }

    private function getTarget(): string { return $this->target; }
    public function setTarget(string $input): self { $this->target = $input; return $this; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        $this->setExit(false);
        $this->configCheck();
        $this->setData([]);
        if($this->getExit() === true) { return; }
        $this->setDom(new Query(file_get_contents($this->getTarget())));
        $this->run();
    }

    private function configCheck(): void {
        if(empty($this->getTarget())) {
            $data = [
                'message' => [
                    'description' => 'Missing property',
                    'details' => "Property `target` not set for Parser"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            $this->setExit(true);
        } else {
            if(!file_exists($this->getTarget())) {
                $data = [
                    'message' => [
                        'description' => 'Missing crawled file',
                        'details' => $this->getTarget()
                    ],
                    'logger' => get_called_class().'::configCheck'
                ];
                $this->getLogger()->setData($data);
                $this->setExit(true);
            }
        }
        if(empty($this->getParsingMap())) {
            $data = [
                'message' => [
                    'description' => 'Missing property',
                    'details' => "Property `parsing_map` not set for Parser"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            $this->setExit(true);
        }
    }

    /** Serial search in node */
    private function findResult(): string {
        $pattern1 = "/(\w+)/";
        $pattern2 = "/\(\"(.+)\"\)/";
        preg_match($pattern1, $this->getDomResult(), $match1);
        preg_match($pattern2, $this->getDomResult(), $match2);
        $str1 = '';
        $str2 = '';
        if(!empty($match1)) { $str1 = $match1[1]; }
        if(!empty($match2)) { $str2 = $match2[1]; }
        if($str2) {
            if    ($str1 == 'getAttribute')        { return (!is_null($this->getDomItem()->getAttribute($str2))) ? $this->sanitize($this->getDomItem()->getAttribute($str2)) : ''; }
        } else {
            if    ($str1 == 'firstChild')          { return (isset($this->getDomItem()->firstChild->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->textContent) : ''; }
            elseif($str1 == 'firstChildSibling1')  { return (isset($this->getDomItem()->firstChild->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling2')  { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling3')  { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling4')  { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling5')  { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling6')  { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling7')  { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling8')  { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling9')  { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling10') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling11') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling12') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling13') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling14') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'firstChildSibling15') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'allBreadcrumbs')      {
                $lines = explode(PHP_EOL, $this->getDomItem()->textContent);
                $return = [];
                if(!empty($lines)) {
                    foreach($lines as $line) {
                        $line = trim($this->sanitize($line));
                        $slug = $this->slugify($line);
                        if(!empty($line) && $slug != 'reality') {
                            $return[] = preg_replace("/,/", '|||', $line);
                        }
                    }
                }
                return implode(', ', $return);
            }
            elseif($str1 == 'step1s1') { return (isset($this->getDomItem()->firstChild->nextSibling->firstChild->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->firstChild->nextSibling->textContent) : ''; }
            elseif($str1 == 'step1s3') { return (isset($this->getDomItem()->firstChild->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'step1s5') { return (isset($this->getDomItem()->firstChild->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'step3s1') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->textContent) : ''; }
            elseif($str1 == 'step3s3') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'step3s5') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'step5s1') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->textContent) : ''; }
            elseif($str1 == 'step5s3') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'step5s5') { return (isset($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->firstChild->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'nextSibling')  { return (isset($this->getDomItem()->nextSibling->textContent))                                                                                                         ? $this->sanitize($this->getDomItem()->nextSibling->textContent) : ''; }
            elseif($str1 == 'nextSibling2') { return (isset($this->getDomItem()->nextSibling->nextSibling->textContent))                                                                                            ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'nextSibling3') { return (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->textContent))                                                                               ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'nextSibling4') { return (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->textContent))                                                                  ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'nextSibling5') { return (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent))                                                     ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'nextSibling6') { return (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent))                                        ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'nextSibling7') { return (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent))                           ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'nextSibling8') { return (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent))              ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'nextSibling9') { return (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : ''; }
            elseif($str1 == 'array') { return $this->sanitize($this->getDomItem()->textContent); }
            elseif($str1 == 'lastChild') { return (isset($this->getDomItem()->lastChild->textContent)) ? $this->sanitize($this->getDomItem()->lastChild->textContent) : ''; }
            else { return (isset($this->getDomItem()->{$str1})) ? $this->sanitize($this->getDomItem()->{$str1}) : ''; }
        }
    }

    /** Paralel search in node siblings */
    private function findSibling(): string {
        $lookup1 = (isset($this->getDomItem()->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->nextSibling->textContent) : '';
        if(!empty($lookup1)) { return(string) $lookup1; }
        $lookup2 = (isset($this->getDomItem()->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->textContent) : '';
        if(!empty($lookup2)) { return(string) $lookup2; }
        $lookup3 = (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->textContent) : '';
        if(!empty($lookup3)) { return(string) $lookup3; }
        $lookup4 = (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : '';
        if(!empty($lookup4)) { return(string) $lookup4; }
        $lookup5 = (isset($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent)) ? $this->sanitize($this->getDomItem()->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->textContent) : '';
        if(!empty($lookup5)) { return(string) $lookup5; }
        // returned none ==== found nothing ??? TODO STOPPED HERE
        return '';
    }

    /** Auto insert, add or update data property */
    private function push(array $input): void {
        $data = $this->getData();
        if(empty($data)) {
            $this->setData($input);
        } else {
            foreach($input as $key => $value) {
                $data[$key] = $value;
            }
            $this->setData($data);
        }
    }

    private function run(): void {
        foreach($this->getParsingMap() as $key => $value) {
            $this->setDomQueryProperties($value);
            $items = $this->getDom()->execute($this->getDomQuery());
            if($this->getDomResult() == 'nehnutelnosti_list') {
                foreach($items as $item) {
                    if($item->hasAttribute("data-action-counter-item-id")) {
                        $this->setDomItem($item);
                        $this->push([$key => $this->getDomItem()->getAttribute('data-action-counter-item-id')]);
                    }
                }
            } else {
                $i = count($items);
                $j = 0;
                foreach($items as $item) {
                    $j++;
                    $this->setDomItem($item);
                    if(!empty($this->getDomName())) {
                        if($this->getDomResult() == 'reality_list1') {
                            $count = 0;
                            foreach($this->getDomItem()->getElementsByTagName('div') as $item) {
                                if($item->hasAttribute($this->getDomName())) { $count++; }
                            }
                            $this->push([$key => $count]);
                            break;
                        }
                        if($this->getDomResult() == 'reality_list2') {
                            $attr = explode(',', $this->getDomName());
                            $result = [];
                            foreach($this->getDomItem()->getElementsByTagName('div') as $item) {
                                if($item->hasAttribute($attr[0])) {
                                    foreach($item->getElementsByTagName('a') as $sub_item) {
                                        if($sub_item->getAttributeNode("class")->value == $attr[1]) {
                                            $result[] = $this->sanitize($sub_item->getAttribute("href"));
                                        }
                                    }
                                }
                            }
                            $this->push($result);
                            break;
                        }
                        $slug = $this->slugify($item->textContent);
                        if(!empty($slug) && true == stristr($slug, $this->getDomName())) {
                            $this->push([$key => $this->findSibling()]);
                            break;
                        } elseif(empty($slug) && $this->getDomName() == 'url') {
                            $this->push([$key => $this->findSibling()]);
                            break;
                        }
                    } else {
                        if($this->getDomResult() == 'topreality_list') {
                            $result = [];
                            foreach($this->getDomItem()->getElementsByTagName('h2') as $item) {
                                $result[] = $this->sanitize($item->firstChild->getAttribute("href"));
                            }
                            $this->push($result);
                        } elseif($this->getDomResult() == 'bazos_list' || $this->getDomResult() == 'bazos_offset') {
                            $result = [];
                            foreach($this->getDomItem()->getElementsByTagName('div') as $item) {
                                if(preg_match("/inzeraty\s+/", $item->getAttribute('class'))) {
                                    if($this->getDomResult() == 'bazos_list') {
                                        $result[] = $this->sanitize($item->childNodes[1]->childNodes[1]->childNodes[0]->getAttribute("href"));
                                    }
                                    elseif($this->getDomResult() == 'bazos_offset') {
                                        if(count($item->childNodes[1]->childNodes[2]->childNodes) == 1) {
                                            $result[] = $this->sanitize($item->childNodes[1]->childNodes[2]->childNodes[0]->textContent);
                                        } elseif(count($item->childNodes[1]->childNodes[2]->childNodes) > 1) {
                                            $result[] = $this->sanitize($item->childNodes[1]->childNodes[2]->childNodes[1]->textContent);
                                        }

                                    }
                                }
                            }
                            $this->push($result);
                        } else {
                            if($key == 'data') {
                                $array = isset($this->getData()[$key]) ? $this->getData()[$key] : [];
                                $string = $this->sanitize($this->findResult());
                                if(!empty($string)) {
                                    $json = json_decode($string, true);
                                    // fix invalid JSON reality.sk
                                    if(empty($json)) {
                                        $string = $this->sanitize(preg_replace(['/\n/m', '/\\\\/m', '/$/m'], [' ', ' ', ' '], $string));
                                        $json = json_decode($string, true);
                                    }
                                    // fix invalid JSON topreality.sk
                                    if(empty($json)) {
                                        if(preg_match("/realestatelisting/i", $string) && preg_match("/\"description\"\s*\:/i", $string) && preg_match("/\"name\"\s*\:/i", $string)) {
                                            $x = preg_split("/\"\s*:\s*\"/", $string);
                                            $c = count($x);
                                            foreach($x as $d_key => $d_value) {
                                                if(preg_match("/\"description$/i", $d_value)) {
                                                    if($c > ($d_key + 1)) {
                                                        $y = preg_split("/\"\s*,\s*\"/", $x[$d_key + 1]);
                                                        if(!empty($y) && count($y) == 2) {
                                                            $z = $this->escQuotes($y[0]);
                                                            $x[$d_key + 1] = $z.'", "'.$y[1];
                                                        }
                                                    }
                                                }
                                                if(preg_match("/\"name$/i", $d_value)) {
                                                    if($c > ($d_key + 1)) {
                                                        $y = preg_split("/\"\s*,\s*\"/", $x[$d_key + 1]);
                                                        if(!empty($y) && count($y) == 2) {
                                                            $z = $this->escQuotes($y[0]);
                                                            $x[$d_key + 1] = $z.'", "'.$y[1];
                                                        }
                                                    }
                                                }
                                            }
                                            $string2 = implode('": "', $x);
                                            $json = json_decode($string2, true);
                                        }
                                    }
                                    if(isset($json['@type']) && !empty($json['@type']) && is_string($json['@type'])) {
                                        if($this->slugify($json['@type']) == 'product') {
                                            if(isset($json['offers']) && !empty($json['offers'])) {
                                                if(isset($json['offers']['url']) && !empty($json['offers']['url'])) {
                                                    $this->push(['origin_url' => $json['offers']['url']]);
                                                }
                                            }
                                        }
                                        if($this->slugify($json['@type']) == 'realestatelisting' && isset($json['url']) && !empty($json['url'])) {
                                            $this->push(['origin_url' => $json['url']]);
                                        }
                                    }
                                    $data = array_merge($array, [$json]);
                                    if(!empty($data)) { $this->push([$key => $data]); }
                                }
                                if($i == $j) { break; }
                            } else {
                                $this->push([$key => $this->findResult()]);
                            }
                        }
                        if($key != 'data') { break; }
                    }
                }
            }
        }
    }

    private function setDomQueryProperties(array $input): void {
        if(!is_null($input['name'])) { $this->setDomName($input['name']); }
        if(!is_null($input['query'])) { $this->setDomQuery($input['query']); }
        if(!is_null($input['result'])) { $this->setDomResult($input['result']); }
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function get(): array {
        $this->_init();
        return $this->getData();
    }
}
