<?php
/**
 * WindowsAzure DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace WindowsAzure\DistributionBundle\Tests;

use WindowsAzure\DistributionBundle\DependencyInjection\WindowsAzureDistributionExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
                        'kernel.debug'       => false,
                        'kernel.bundles'     => array(),
                        'kernel.cache_dir'   => sys_get_temp_dir(),
                        'kernel.environment' => 'test',
                        'kernel.root_dir'    => __DIR__ . '/_files/app',
                        )));
        $loader = new WindowsAzureDistributionExtension();
        $container->registerExtension($loader);
        $loader->load(array(array()), $container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array(new ResolveDefinitionTemplatesPass()));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        $service = $container->get('windows_azure_distribution.deployment.azure_sdk_command_builder');
        $this->assertInstanceOf('WindowsAzure\DistributionBundle\Deployment\AzureSDKCommandBuilder', $service);

        $serviceDefinition = $container->get('windows_azure_distribution.config.service_definition');
        $this->assertInstanceOf('WindowsAzure\DistributionBundle\Deployment\ServiceDefinition', $serviceDefinition);
    }
}

