<?php

declare(strict_types=1);

namespace App\Handler;

trait AbstractAppTraitMethods {

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function escQuotes(string $input): string {
        return htmlspecialchars($input, ENT_QUOTES, null, false);
    }

    private function filenameDate(string $filename, string $pattern_start = '', string $pattern_end = ''): string {
        $pattern = "/".$pattern_start."([\d]{4})([\d]{2})([\d]{2})_([\d]{2})([\d]{2})([\d]{2})".$pattern_end."/";
        preg_match($pattern, $filename, $match);
        $mktime = mktime(
            (int) $match[4],
            (int) $match[5],
            (int) $match[6],
            (int) $match[2],
            (int) $match[3],
            (int) $match[1]
        );
        return date('Y-m-d H:i:s', $mktime);
    }

    private function formatFloat(string $input): string {
        $return = '';
        if(!is_null($input)) {
            $temp = preg_replace("/\s/", '', $input);
            if(substr_count($temp, ',') > 1) {
                $temp = preg_replace("/,/", '', $temp);
            }
            if(substr_count($temp, '.') > 1 || substr_count($temp, ',') == 1) {
                $temp = preg_replace(["/\./","/,/"], ['','.'], $temp);
            }
            $return = $temp;
        }
        return $return;
    }

    private function isTimeout(string $input, string $timeout): bool {
        $limit = date('Y-m-d H:i:s', strtotime($input.' +'.$timeout));
        $now = date('Y-m-d H:i:s', strtotime('now'));
        if($limit <= $now) { return true; }
        return false;
    }

    private function sanitize(string $input): string {
        $input = preg_replace(["/\x{00a0}/miu", "/\s+/miu"], [' ', ' '], $input);
        return trim($input);
    }

    // depends on LoggerController
    private function save(array $array, string $file): void {
        if(!is_dir(pathinfo($file)['dirname'])) { mkdir(pathinfo($file)['dirname'], octdec($this->getLogger()->getPermissions()['directory']), true); }
        chmod(pathinfo($file)['dirname'], octdec($this->getLogger()->getPermissions()['directory']));
        $input = json_encode($array);
        file_put_contents($file, $input, LOCK_EX);
        chmod($file, octdec($this->getLogger()->getPermissions()['file']));
    }

    // depends on SlugifyHelper
    private function slugify(?string $string): string {
        if(empty($string)) return '';
        return trim($this->getSlug()->get($string));
    }

    private function toArray(string $json_file): ?array {
        if(file_exists($json_file)) {
            $string = file_get_contents($json_file);
            $data = json_decode($string, true);
            return $data;
        }
        return null;
    }

    /** slovak word ending remover */
    private function wordCoreSK(string $input): string {
        $input = preg_replace("/\b(?:ves|vsi)\b/ui", '', $input);
        if(mb_strlen($input) < 5) { return $input; }
        $input = preg_replace("/\b(nov\w|cestice)\b/ui", '$1'.'xxxxxx', $input);
        $input = preg_replace("/\b(\w*)\b\s*\b(cest\w*[^x\s])\b\s*\b(\w*)\b/ui", '$1 $3', $input);
        $input = preg_replace("/\b(\w+)(?:ch|mi|m|v)\b/ui", '$1', $input);
        $input = preg_replace("/\b(\w+)(?:a|á|e|é|i|í|o|ó|u|ú|y|ý)\b/ui", '$1', $input);
        $input = preg_replace("/\b(\w+)(?:ov|i|o)\b/ui", '$1', $input);
        $input = preg_replace("/\b(\w+)(?:ovc)\b/ui", '$1'.'ov', $input);
        $input = preg_replace("/\bnames\w*\b/ui", 'nam', $input);
        $input = preg_replace("/\bulic\w*\b/ui", 'ul', $input);
        $input = preg_replace("/\btrie\w*\b/ui", 'tr', $input);
        $input = preg_replace("/\bcest\w*[^x\s]\b/ui", 'ces', $input);
        $input = preg_replace("/xxxxxx/ui", '', $input);
        return $this->sanitize($input);
    }
}
