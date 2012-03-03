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

namespace WindowsAzure\DistributionBundle\Deployment;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Access to details of the azure deployment of this project.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class AzureDeployment
{
    const ROLE_WEB = 'WebRole';
    const ROLE_WORKER = 'WorkerRole';

    /**
     * @var string
     */
    private $configDir;
    /**
     * @var string
     */
    private $binDir;

    /**
     * @var ServiceDefinition
     */
    private $serviceDefinition;

    /**
     * @var ServiceConfiguration
     */
    private $serviceConfiguration;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $storage;

    /**
     * @param string $configDir Directory with Azure specific configuration
     * @param string $binDir Directory where binaries are placed.
     * @param array $options
     * @param array $storage
     */
    public function __construct($configDir, $binDir, array $options = array(), array $storage = array())
    {
        $this->configDir = $configDir;
        $this->binDir = $binDir;
        $this->options = $options;
        $this->storage = $storage;
    }

    /**
     * Create required directory structore for this azure deployment if not
     * exists already.
     *
     * @return void
     */
    public function create()
    {
        $filesystem = new Filesystem();
        if ( !file_exists($this->configDir)) {
            $filesystem->mkdir($this->configDir, 0777);
            $filesystem->copy(__DIR__ . '/../Resources/role_template/ServiceConfiguration.cscfg', $this->configDir . '/ServiceConfiguration.cscfg');
            $filesystem->copy(__DIR__ . '/../Resources/role_template/ServiceDefinition.csdef', $this->configDir . '/ServiceDefinition.csdef');
            $filesystem->mirror(__DIR__ . '/../Resources/role_template/resources', $this->configDir . '/resources', null, array('copy_on_windows' => true));
            $filesystem->mirror(__DIR__ . '/../Resources/role_template/php', $this->configDir . '/php', null, array('copy_on_windows' => true));
        }

        if ( !file_exists($this->binDir)) {
            $filesystem->mkdir($this->binDir, 0777);
        }
        $filesystem->mirror(__DIR__ . '/../Resources/role_template/bin', $this->binDir, null, array('copy_on_windows' => true));
    }

    public function createRole($name, $type = self::ROLE_WEB)
    {
        $serviceDefinition = $this->getServiceDefinition();
        $serviceConfig = $this->getServiceConfiguration();

        switch ($type) {
            case self::ROLE_WEB:
                $serviceDefinition->addWebRole($name);
                $serviceConfig->addRole($name);

                $filesystem = new Filesystem();
                $filesystem->mkdir($this->configDir . '/' . $name);
                $filesystem->copy(__DIR__ . '/../Resources/role_template/Web.config', $this->configDir . '/' . $name . '/web.config');
                $filesystem->copy(__DIR__ . '/../Resources/role_template/index.php', $this->configDir . '/' . $name . '/index.php');
                $filesystem->copy(__DIR__ . '/../Resources/role_template/diagnotics.wadcfg', $this->binDir . '/diagnostics.wadcfg');
                // TODO: explicit directory more robust?
                $filesystem->copy(__DIR__ . '/../Resources/role_template/config_azure.yml', $this->configDir . '/../config/config_azure.yml');
                $filesystem->copy(__DIR__ . '/../Resources/role_template/parameters_azure.yml', $this->configDir . '/../config/parameters_azure.yml');
                break;
            default:
                throw new \RuntimeException("Unsupported role $type cannot be created");
        }
    }

    public function exists()
    {
        return file_exists($this->configDir);
    }

    /**
     * @return \WindowsAzure\DistributionBundle\Deployment\ServiceDefinition
     */
    public function getServiceDefinition()
    {
        if ( ! $this->serviceDefinition) {
            $roleFiles = isset($this->options['roleFiles']) ? $this->options['roleFiles'] : array();
            $this->serviceDefinition = new ServiceDefinition($this->configDir . '/ServiceDefinition.csdef', $roleFiles);
        }
        return $this->serviceDefinition;
    }

    /**
     * @return \WindowsAzure\DistributionBundle\Deployment\ServiceConfiguration
     */
    public function getServiceConfiguration()
    {
        if ( ! $this->serviceConfiguration) {
            $this->serviceConfiguration = new ServiceConfiguration($this->configDir . '/ServiceConfiguration.cscfg', $this->storage);
        }
        return $this->serviceConfiguration;
    }
}

