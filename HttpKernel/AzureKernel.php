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

namespace WindowsAzure\DistributionBundle\HttpKernel;

use Symfony\Component\HttpKernel\Kernel;

/**
 * Azure Kernel handles the temporary directory logic depending on the
 * deployment status of the Symfony app: On Azure or not on Azure.
 *
 * See add-environment-variables.ps1 for how "SymfonyAzureFileCache" is set.
 * The Storage is defined in ServiceDefinition.csdef <LocalResources>.
 * Using the ENV Variable makes the temporary directory consistent for calls through the
 * Symfony console and access through IIS.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
abstract class AzureKernel extends Kernel
{
    /**
     * @var string
     */
    private $tempDir;

    public function init()
    {
        parent::init();

        $isAzure = isset( $_SERVER['RdRoleId'] );
        if ($isAzure) {
            $this->tempDir = sys_get_temp_dir();
        } else {
            $this->tempDir = $this->rootDir;
        }
    }

    public function getCacheDir()
    {
        if (isset($_SERVER['SymfonyAzureFileCache']) && is_dir($_SERVER['SymfonyAzureFileCache'])) {
            return $_SERVER['SymfonyAzureFileCache'] . '/cache/' . $this->getEnvironment();
        }
        return $this->tempDir . '/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        if (isset($_SERVER['SymfonyAzureLogFiles']) && is_dir($_SERVER['SymfonyAzureLogFiles'])) {
            return $_SERVER['SymfonyAzureLogFiles'] . '/logs';
        }
        return $this->tempDir . '/logs';
    }
}

