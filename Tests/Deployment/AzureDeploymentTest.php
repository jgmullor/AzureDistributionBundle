<?php

namespace WindowsAzure\AzureDistributionBundle\Tests\Deployment;

use WindowsAzure\DistributionBundle\Deployment\AzureDeployment;
use Symfony\Component\Filesystem\Filesystem;

class AzureDeploymentTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateConfigurationAndRole()
    {
        $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "azure_distribution_bundle" . DIRECTORY_SEPARATOR;
        $configDir = $base . "config";
        $binDir = $base . "bin";

        $filesystem = new Filesystem;
        $filesystem->remove($base);

        $deployment = new AzureDeployment($configDir, $binDir);

        $this->assertFalse($deployment->exists());

        $deployment->create();
        $deployment->createRole("Sf2Web");

        $this->assertTrue($deployment->exists());
        $this->assertTrue(file_exists($binDir . DIRECTORY_SEPARATOR . "symfony_cache_clear.cmd"), "symfony_cache_clear.cmd is missing");
        $this->assertTrue(file_exists($configDir . DIRECTORY_SEPARATOR . "ServiceDefinition.csdef"), "ServiceDefinition.csdef is missing");
        $this->assertTrue(file_exists($configDir . DIRECTORY_SEPARATOR . "ServiceConfiguration.cscfg"), "ServiceConfiguration.cscfg is missing");
    }
}

