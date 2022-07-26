<?php

declare(strict_types=1);

namespace Services\Helper;

use Cocur\Slugify\Slugify;

/*
 * 2020-12-22T08:45:00+01:00
 *
 * Helper class to slugify strings.
 *
 *
 *  Usage:
 *
 *      Functions:
 *          get('string to slugify')
 *
 *      View some results:
 *          $slug = $slug->get('string to slugify');
 *          var_dump($slug);
 */
class SlugifyHelper {
    private Slugify $slug;

    public function __construct() {
        $this->setSlug(new Slugify([
            'rulesets' => [
                // Don't change, must be first rule
                'default',
                // Additional rules
                'slovak',
                'czech',
                'hungarian'
            ],
            'trim' => false,
            'separator' => ' '
        ]));
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getSlug(): Slugify { return $this->slug; }
    private function setSlug(Slugify $input): void { $this->slug = $input; }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function get(string $input): string {
        return $this->getSlug()->slugify($input);
    }
}
