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

/**
 * Wraps the ServiceConfiguration.csdef file and allows convenient access.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ServiceConfiguration
{
    /**
     * @var string
     */
    private $serviceConfigurationFile;

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @param string $serviceConfigurationFile
     */
    public function __construct($serviceConfigurationFile)
    {
        if (!file_exists($serviceConfigurationFile)) {
            throw new \InvalidArgumentException(sprintf(
                "No valid file-path given. The ServiceConfiguration should be at %s but could not be found.",
                $serviceConfigurationFile
            ));
        }

        $this->serviceConfigurationFile = $serviceConfigurationFile;
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
        $this->dom->load($this->serviceConfigurationFile);
    }

    public function getPath()
    {
        return $this->serviceConfigurationFile;
    }

    public function addRole($name)
    {
        $role = new \DOMDocument('1.0', 'UTF-8');
        $role->load(__DIR__ . '/../Resources/role_template/RoleConfig.xml');

        $roles = $role->getElementsByTagName('Role');
        $roleNode = $roles->item(0);
        $roleNode->setAttribute('name', $name);

        $roleNode = $this->dom->importNode($roleNode, true);
        $this->dom->documentElement->appendChild($roleNode);

        if ($this->dom->save($this->serviceConfigurationFile) === false) {
            throw new \RuntimeException(sprintf("Could not write ServiceConfiguration to '%s'",
                        $this->serviceConfigurationFile));
        }
    }

    /**
     * Copy ServiceConfiguration over to build directory given with target path
     * and modify some of the settings to point to development settings.
     *
     * @param string $targetPath
     * @return void
     */
    public function copyForDevelopment($targetPath)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($this->dom->saveXML());

        $xpath = new \DOMXpath($dom);
        $setting = $xpath->evaluate('//ConfigurationSettings/Setting[@name="Microsoft.WindowsAzure.Plugins.Diagnostics.ConnectionString"]')->item(0);
        $setting->setAttribute('value', 'UseDevelopmentStorage=true');

        $dom->save($targetPath . '/ServiceConfiguration.cscfg');
    }

    /**
     * Copy for production, no changes to the file.
     *
     * @param string $targetPath
     * @return void
     */
    public function copyForProduction($targetPath)
    {
        copy ($this->getPath(), $targetPath . '/ServiceConfiguration.cscfg');
    }
}

