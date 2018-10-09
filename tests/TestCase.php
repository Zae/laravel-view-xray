<?php

namespace Zae\ViewXray\Tests;

use View;
use Zae\ViewXray\ViewXrayServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ViewXrayServiceProvider::class
        ];
    }

    public function setUp()
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/views');
    }
}
