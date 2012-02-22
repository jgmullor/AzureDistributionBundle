<?php
/**
 * WindowsAzure DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
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
    public function testAzureSdkCommandBuilder()
    {
        $container = $this->createContainer();

        $service = $container->get('windows_azure_distribution.deployment.azure_sdk_command_builder');
        $this->assertInstanceOf('WindowsAzure\DistributionBundle\Deployment\AzureSDKCommandBuilder', $service);
    }

    public function testDeploymentService()
    {
        $container = $this->createContainer();
        $deployment = $container->get('windows_azure_distribution.deployment');
        $this->assertInstanceOf('WindowsAzure\DistributionBundle\Deployment\AzureDeployment', $deployment);
    }

    public function testSessionStorage()
    {
        $config = array(
            'session' => array(
                'type' => 'pdo',
                'database' => array(
                    'host' => 'localhost',
                    'username' => 'foo',
                    'password' => 'bar',
                    'database' => 'db',
                )
            ),
        );
        $container = $this->createContainer($config);
        $def = $container->findDefinition('session.storage');

        $this->assertEquals('%windows_azure_distribution.session_storage.pdo.class%', $def->getClass());

        $def = $container->findDefinition('windows_azure_distribution.session.pdo');
        $args = $def->getArguments();
        $this->assertEquals('sqlsrv:server=localhost;Database=db', $args[0]);
    }

    public function createContainer(array $config = array())
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'       => false,
            'kernel.bundles'     => array(),
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir'    => __DIR__ . '/_files/app',
            'session.storage.options' => array(),
        )));
        $loader = new WindowsAzureDistributionExtension();
        $container->registerExtension($loader);
        $loader->load(array($config), $container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array(new ResolveDefinitionTemplatesPass()));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}

