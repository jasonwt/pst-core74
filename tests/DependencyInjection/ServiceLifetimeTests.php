<?php

/*TDD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\DependencyInjection\ServiceLifetime;

use Pst\Testing\Should;

Should::executeTests(function() {
    Should::equal("ServiceLifetime::Transient", (string) ServiceLifetime::Transient());
    Should::equal("ServiceLifetime::Singleton", (string) ServiceLifetime::Singleton());

    Should::beTrue(ServiceLifetime::Transient() == ServiceLifetime::Transient());
    Should::beFalse(ServiceLifetime::Transient() == ServiceLifetime::Singleton());
});