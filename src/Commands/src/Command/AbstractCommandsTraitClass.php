<?php

declare(strict_types=1);

namespace Commands\Command;

use Crawler\Controller\CrawlerController;
use Scanner\Controller\ScannerController;

trait AbstractCommandsTraitClass {
    use AbstractCommandsTraitMethods;

    private CrawlerController $crawler;
    private $function = '';
    private $limit = 0;
    private $offset = 0;
    private $order = 'desc';
    private $process = false;
    private ScannerController $scanner;
    private $target = '';
    private $updater = false;
    private $url = '';

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getCrawler(): CrawlerController { return $this->crawler; }
    private function setCrawler(CrawlerController $input): void { $this->crawler = $input; }

    private function getFunction(): string { return $this->function; }
    private function setFunction(?string $input): void {
        if(!empty($input)) {
            $input = strtolower(trim(filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)));
            $pattern = "/^(?:description|name|origin_url|contact_name|force)$/";
            if(preg_match($pattern, $input)) {
                $this->function = $input;
            }
        }
    }

    private function getLimit(): int { return $this->limit; }
    private function setLimit($input): void {
        if(!empty($input)) {
            $input = (int) filter_var(trim($input), FILTER_SANITIZE_NUMBER_INT);
            if($input > 0) { $this->limit = $input; }
            else { $this->limit = 0; }
        }
    }

    private function getOffset(): int { return $this->offset; }
    private function setOffset($input): void {
        if(!empty($input)) {
            $input = (int) filter_var(trim($input), FILTER_SANITIZE_NUMBER_INT);
            if($input > 0) { $this->offset = $input; }
            else { $this->limit = 0; }
        }
    }

    private function getOrder(): string { return $this->order; }
    private function setOrder(?string $input): void {
        if(!empty($input)) {
            $input = strtolower(trim(filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)));
            $pattern = "/^(?:asc|desc)$/";
            if(preg_match($pattern, $input)) {
                $this->order = $input;
            } else {
                $this->order = 'desc';
            }
        }
    }

    private function getProcess(): bool { return $this->process; }
    private function setProcess($input): void {
        if(!empty($input)) {
            $input = strtolower(trim(filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)));
            $pattern = "/^(?:on|true|1)$/";
            if(preg_match($pattern, $input)) {
                $this->process = true;
            } else {
                $this->process = false;
            }
        }
    }

    private function getScanner(): ScannerController { return $this->scanner; }
    private function setScanner(ScannerController $input): void { $this->scanner = $input; }

    private function getTarget(): string { return $this->target; }
    private function setTarget(?string $input): void {
        if(!empty($input)) {
            $input = filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
            if(strlen($input) < 3) { return; }
            $pattern = "/^(?:topreality|nehnutelnosti|reality|bazos)$/i";
            if(preg_match($pattern, $input)) {
                $input = ucfirst(str_replace('target', '', strtolower($input)));
                $input = "Target{$input}\Controller\Crawler{$input}Controller";
                $this->target = $input;
            }
        }
    }

    private function getUpdater(): bool { return $this->updater; }
    private function setUpdater($input): void {
        if(!empty($input)) {
            $input = strtolower(trim(filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)));
            $pattern = "/^(?:on|true|1)$/";
            if(preg_match($pattern, $input)) {
                $this->updater = true;
            } else {
                $this->updater = false;
            }
        }
    }

    private function getUrl(): string { return $this->url; }
    private function setUrl(?string $input): void {
        if(!empty($input)) {
            $input = filter_var(trim($input), FILTER_SANITIZE_URL, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
            if(filter_var($input, FILTER_VALIDATE_URL) === false) { return; }
            $this->url = $input;
        }
    }
}
