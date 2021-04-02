<?php

namespace TCGunel\BizimHesapB2b\Tests;

use TCGunel\BizimHesapB2b\BizimHesapB2bServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = \Faker\Factory::create("tr_TR");
    }

    protected function getPackageProviders($app): array
    {
        return [
            BizimHesapB2bServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
    }


}
