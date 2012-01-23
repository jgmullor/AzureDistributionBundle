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

namespace WindowsAzure\DistributionBundle\Deployment;

/**
 * Abstraction layer to build commands from input parameters.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class AzureSDKCommandBuilder
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $outputDir;

    public function __construct($rootDir, $outputDir)
    {
        $this->rootDir = $rootDir;
        $this->outputDir = $outputDir;
    }

    /**
     * Build Packaging command
     *
     * @param ServiceDefinition $serviceDefinition
     * @param bool $isDevFabric
     * @return string
     */
    public function buildPackageCmd(ServiceDefinition $serviceDefinition, $isDevFabric)
    {
        $args = array($serviceDefinition->getPath());
        foreach ($serviceDefinition->getWebRoleNames() as $roleName) {
            $args[] = sprintf('/role:%s;%s', $roleName, $this->rootDir); // TODO: Only standard layout
        }
        foreach ($serviceDefinition->getWorkerRoleNames() as $roleName) {
            $args[] = sprintf('/role:%s;%s', $roleName, $this->rootDir); // TODO: Only standard layout
        }
        $args[] = sprintf('/out:%s', $this->outputDir);

        if ($isDevFabric) {
            $args[] = '/copyOnly';
        }

        return 'cspack.exe ' . implode(" ",  $args);
    }
}

