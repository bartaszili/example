<?php

declare(strict_types=1);

namespace Services\Helper\Factory;

use Services\Helper\SlugifyHelper;
use Psr\Container\ContainerInterface;

class SlugifyHelperFactory {
    public function __invoke(ContainerInterface $container): SlugifyHelper {
        return new SlugifyHelper();
    }
}
