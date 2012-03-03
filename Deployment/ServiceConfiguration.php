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
     * @var array
     */
    private $storage;

    /**
     * @param string $serviceConfigurationFile
     * @param array $storage
     */
    public function __construct($serviceConfigurationFile, array $storage = array())
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
        $this->storage = $storage;
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
    public function copyForDeployment($targetPath, $development = true)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($this->dom->saveXML());

        $xpath = new \DOMXpath($dom);
        $xpath->registerNamespace('sc', $dom->lookupNamespaceUri($dom->namespaceURI));
        $settings = $xpath->evaluate('//sc:ConfigurationSettings/sc:Setting[@name="Microsoft.WindowsAzure.Plugins.Diagnostics.ConnectionString"]');
        foreach ($settings as $setting) {
            if ($development) {
                $setting->setAttribute('value', 'UseDevelopmentStorage=true');
            } else if (strlen($setting->getAttribute('value')) === 0) {
                if ($this->storage) {
                    $setting->setAttribute('value', sprintf('DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
                        $this->storage['accountName'], $this->storage['accountKey']
                    ));
                } else {
                    throw new \RuntimeException(<<<EXC
ServiceConfiguration.csdef: Missing value for 'Microsoft.WindowsAzure.Plugins.Diagnostics.ConnectionString'.

You have to modify the app/azure/ServiceConfiguration.csdef to contain a value for the diagnostics connection
string or better configure 'windows_azure_distribution.diagnostics.accountName' and
'windows_azure_distribution.diagnostics.accountKey' in your app/config/config.yml

If you don't want to enable diagnostics you should delete the connection string
elements from ServiceConfiguration.csdef file.
EXC
                    );
                }
            }
        }

        $dom->save($targetPath . '/ServiceConfiguration.cscfg');
    }
}

