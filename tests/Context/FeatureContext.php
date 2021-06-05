<?php

namespace WP_CLI_Dotenv\Tests\Context;

class FeatureContext extends \WP_CLI\Tests\Context\FeatureContext
{
    /**
     * @Given a .env file
     */
    public function aEnvFile()
    {
        touch($this->variables['RUN_DIR'] . '/.env');
    }
}
