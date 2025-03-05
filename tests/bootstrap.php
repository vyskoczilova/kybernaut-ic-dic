<?php

namespace KybernautIcDic\Test;

use WP_Mock;

// First we need to load the composer autoloader so we can use WP Mock.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/WPMock/wp-functions-mock.php';

// Now call the bootstrap method of WP Mock.
// https://wp-mock.gitbook.io/documentation/getting-started/introduction
WP_Mock::setUsePatchwork(false);
WP_Mock::bootstrap();

require_once dirname(__DIR__) . '/includes/ares.php';
require_once dirname(__DIR__) . '/includes/logger.php';