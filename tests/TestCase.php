<?php

namespace KayiwaRahim\Relworx\Tests;

use KayiwaRahim\Relworx\RelworxServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [RelworxServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('relworx.api_key', 'test-api-key');
        $app['config']->set('relworx.account_no', 'TEST-ACCOUNT');
        $app['config']->set('relworx.base_url', 'https://payments.relworx.com/api');
        $app['config']->set('relworx.webhooks.enabled', true);
        $app['config']->set('relworx.webhooks.url', 'https://merchant.example/relworx/webhook');
        $app['config']->set('relworx.webhooks.secret', 'webhook-secret');
    }
}
