<?php

/*TD/D*/

declare(strict_types=1);

//namespace Pst\Core\Tests\DependencyInjection;

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\DependencyInjection\ServiceLifetime;

use Pst\Testing\Should;

Should::executeTests(function() {
    Should::equal("Transient", (string) ServiceLifetime::Transient());
    Should::equal("Singleton", (string) ServiceLifetime::Singleton());

    Should::beTrue(ServiceLifetime::Transient() == ServiceLifetime::Transient());
    Should::beFalse(ServiceLifetime::Transient() == ServiceLifetime::Singleton());
});