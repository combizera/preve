<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Load .env.testing with createMutable so it overrides
 * Docker-injected environment variables.
 */
Dotenv::createMutable(dirname(__DIR__), '.env.testing')->load();
