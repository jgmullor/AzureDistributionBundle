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

namespace WindowsAzure\DistributionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Windows Azure Extension
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class WindowsAzureDistributionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('windows_azure_distribution.config.deployment', $config['deployment']);

        if (isset($config['session'])) {
            $this->loadSession($config['session'], $container);
        }
    }

    protected function loadSession($sessionConfig, $container)
    {
        switch($sessionConfig['type']) {
            case 'pdo':
                if (!isset($sessionConfig['database'])) {
                    throw new \RuntimeException("Key windows_azure_distribution.session.database has to be set when PDO is selected.");
                }

                $definition = new Definition('PDO');
                $definition->setArguments(array(
                    'sqlsrv:server=' . $sessionConfig['database']['host'] . ';Database=' . $sessionConfig['database']['database'],
                    $sessionConfig['database']['username'],
                    $sessionConfig['database']['password']
                ));
                $definition->addMethodCall('setAttribute', array(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION));
                $container->setDefinition('windows_azure_distribution.session.pdo', $definition);

                $definition = new Definition('%windows_azure_distribution.session_storage.pdo.class%');
                $definition->setArguments(array(
                    new Reference('windows_azure_distribution.session.pdo'),
                    $container->getParameter('session.storage.options'),
                    array('db_table' => $sessionConfig['database']['table'])
                ));
                $container->setDefinition('windows_azure_distribution.session_storage', $definition);
                $container->setAlias('session.storage', 'windows_azure_distribution.session_storage');

                $definition = new Definition('%windows_azure_distribution.cache_warmer.dbtable.class%');
                $definition->setArguments(array(
                    new Reference('windows_azure_distribution.session.pdo'),
                    array('db_table' => $sessionConfig['database']['table'])
                ));
                $definition->addTag('kernel.cache_warmer');
                break;
            default:
                throw new \RuntimeException("Unknown session config!");
        }
    }
}

