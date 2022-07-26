<?php

declare(strict_types=1);

namespace Matcher\Controller;

use App\Handler\AbstractAppTraitMethods;
use \DateTime;

trait AbstractMatcherTraitMethods {
    use AbstractAppTraitMethods;

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    /** To retrieve currency from price string */
    private function findCurrency(string $input): string {
        if(!empty($input)) {
            preg_match_all("/\d+\s(\D{1,3})\/\D+|\d+\s(\D{1,3})\Z\s?/", trim($input), $match);
            $symbol = '';
            if(!empty($match[1][0]) && empty($match[2][0])) {
                $symbol = $match[1][0];
            }
            if(empty($match[1][0]) && !empty($match[2][0])) {
                $symbol = $match[2][0];
            }
            return $symbol;
        } else {
            return '';
        }
    }

    /** To handle non-standard number representation values found with parser */
    private function findFormattedPriceNumber(string $input): array {
        $return = [];
        if(!empty($input)) {
            preg_match_all("/(\d{1,3}[\s\.,](\d{3}[\s\.,])*\d{3}|\d+)([\.,]\d+)?/", trim($input), $match);
            foreach($match[0] as $item) {
                $return[] = $this->formatFloat($item);
            }
        }
        return $return;
    }

    private function findPriceType(string $input): string {
        $return = '';
        if(!empty($input)) {
            preg_match_all("/\/\b(\D+)\b\s?/", trim($input), $match);
            if(!empty($match[1][0])) {
                $return = $match[1][0];
            }
            return $return;
        } else {
            return $return;
        }
    }

    /** To handle non-standard DateTime representation values found with parser */
    private function makeDateTime(string $input): ?DateTime {
        if(!empty($input)) {
            if(!preg_match('/[0-9:\. ]{5,}/', $input)) { return null; }
            $pattern = "/(\d{1,2})\D+(\d{1,2})(?:\D+(\d{2,4}|))?(?:\D+(\d{1,2}|))?(?:\D+(\d{1,2}|))?(?:\D+(\d{1,2}|))?/";
            preg_match($pattern, $input, $matched);
            $mktime = mktime(
                (isset($matched[4]) && !empty($matched[4])) ? (int) trim($matched[4]) : 0,
                (isset($matched[5]) && !empty($matched[5])) ? (int) trim($matched[5]) : 0,
                (isset($matched[6]) && !empty($matched[6])) ? (int) trim($matched[6]) : 0,
                (isset($matched[2]) && !empty($matched[2])) ? (int) trim($matched[2]) : 1,
                (isset($matched[1]) && !empty($matched[1])) ? (int) trim($matched[1]) : 1,
                (isset($matched[3]) && !empty($matched[3])) ? (int) trim($matched[3]) : date('Y')
            );
            return new DateTime(date("Y-m-d H:i:s", $mktime));
        } else {
            return null;
        }
    }

    private function matchAddress(?string $input): array {
        if(empty($input)) { $input = ''; }
        $return = [];
        if(isset($this->town_part) && !empty($this->town_part)) {
            $this->getAddress()
                ->search($this->town_part, 'town')
                ->search($this->town_part, 'town_part');
        }
        if(!empty($input)) {
            $this->getAddress()->search($input, 'street');
            $this->getAddress()->strictSearch($input, 'street', true);
        }
        if(
            isset($this->town) && empty($this->town)
            && isset($this->town_part) && empty($this->town_part)
            && isset($this->street) && empty($this->street)
            && isset($this->postcode) && !empty($this->postcode)
        ) {
            $this->getAddress()->search($this->postcode, 'postcode');
        }
        $answer = $this->getAddress()->getResults();
        if(count($answer) == 1) {
            $return['county']    = $answer[0]['county'];
            $return['district']  = $answer[0]['district'];
            $return['town']      = $answer[0]['town'];
            $return['town_part'] = $answer[0]['town_part'];
            $return['street']    = $answer[0]['street'];
            $return['postcode']  = $answer[0]['postcode'];
            if(!empty($answer[0]['latitude']) || !empty($answer[0]['longitude'])) {
                $return['latitude']  = $answer[0]['latitude'];
                $return['longitude'] = $answer[0]['longitude'];
            }
            $return['score_reduce_by'] = 0;
            $return['score_details'] = [];
        } elseif(count($answer) > 1) {
            $key = 0;
            if(isset($this->district) && !empty($this->district)) {
                $towns = array_unique(array_column($this->getAddress()->getResults(), 'town'));
                if(in_array($this->district, $towns)) { $s_key = array_search($this->district, array_column($this->getAddress()->getResults(), 'town')); }
                if(isset($s_key) && $s_key > 0) { $key = $s_key; }
            }
            $return['county']    = $answer[$key]['county'];
            $return['district']  = $answer[$key]['district'];
            $return['town']      = $answer[$key]['town'];
            $return['town_part'] = $answer[$key]['town_part'];
            $return['street']    = $answer[$key]['street'];
            $return['postcode']  = $answer[$key]['postcode'];
            if(!empty($answer[$key]['latitude']) || !empty($answer[$key]['longitude'])) {
                $return['latitude']  = $answer[$key]['latitude'];
                $return['longitude'] = $answer[$key]['longitude'];
            }
            if(count($answer) >     1 && count($answer) <   10) { $return['score_reduce_by'] =  5; }
            if(count($answer) >=   10 && count($answer) <  100) { $return['score_reduce_by'] = 15; }
            if(count($answer) >=  100 && count($answer) < 1000) { $return['score_reduce_by'] = 25; }
            if(count($answer) >= 1000                         ) { $return['score_reduce_by'] = 35; }
            $return['score_details']   = [
                'to-many-locations',
                'count' => count($answer)
            ];
        }
        return $return;
    }

    private function matchDateTime(): void {
        $date = null;
        $date2 = null;
        if(isset($this->getData()['origin_updated']) && !empty($this->getData()['origin_updated'])) {
            $date = $this->makeDateTime($this->getData()['origin_updated']);
        }
        if(isset($this->getData()['origin_updated2']) && !empty($this->getData()['origin_updated2'])) {
            $date2 = $this->makeDateTime($this->getData()['origin_updated2']);
        }
        $this->push(['origin_updated' => !is_null($date) ? $date : $date2]);
    }

    private function noWhiteSpace(string $input): string {
        return preg_replace("/\s+/", '', trim($input));
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

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function get(): array {
        $this->_init();
        if($this->getLogger()->getDebug()['console_log']['matcher'] == true) {
            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s.u')." Matcher is processing: ".$this->getData()['origin_url'].PHP_EOL);
        }
        $this->matchDateTime();
        $this->getUrlId();
        $this->run();
        return $this->getData();
    }

    public function getUpdated(): array {
        $this->_init();
        $this->matchDateTime();
        $this->getUrlId();
        return $this->getData();
    }
}
