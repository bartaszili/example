<?php
/**
 * Doctrine CLI console
 * 
 * Access from the application root.
 * Command example: 'php vendor/bin/doctrine list'
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

$container = require __DIR__ . '/container.php';

return ConsoleRunner::createHelperSet(
    $container->get(EntityManager::class)
);
