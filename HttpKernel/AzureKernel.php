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
            // See add-environment-variables.ps1 for how "LocalStorageRoot" is set.
            // The Storage is defined in ServiceDefinition.csdef <LocalResources>.
            // Using the ENV Variable makes the temporary directory consistent for calls through the
            // Symfony console and access through IIS.
            if (isset($_SERVER['LocalStorageRoot'])) {
                $this->tempDir = $_SERVER['LocalStorageRoot'] . "/sf_" . crc32($this->rootDir);
            } else {
                $this->tempDir = sys_get_temp_dir() . "/sf_" . crc32($this->rootDir);
            }
        } else {
            $this->tempDir = $this->rootDir;
        }
    }

    public function getCacheDir()
    {
        return $this->tempDir . '/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return $this->tempDir . '/logs';
    }
}

