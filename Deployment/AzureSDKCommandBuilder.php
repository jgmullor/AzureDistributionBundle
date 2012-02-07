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

use Symfony\Component\Process\Process;

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

    /**
     * @var string
     */
    private $binDir;

    public function __construct($rootDir, $outputDir, $binDir = null)
    {
        $this->rootDir = $rootDir;
        $this->outputDir = $outputDir;
        $this->binDir = $binDir ?: $this->getAzureSdkBinaryFolder();
    }

    public function getOutputDir()
    {
        return $this->outputDir;
    }

    /**
     * Build Packaging command
     *
     * @param ServiceDefinition $serviceDefinition
     * @param bool $isDevFabric
     * @return array
     */
    public function buildPackageCmd(ServiceDefinition $serviceDefinition, $isDevFabric)
    {
        $args = array (
            $this->binDir . 'cspack.exe',
            $serviceDefinition->getPath()
        );
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

        return $args;
    }

    private function getAzureSdkBinaryFolder()
    {
        $programDirectories = array('ProgramFiles', 'ProgramFiles(x86)', 'ProgramW6432');
        $binDirectories = array('Windows Azure SDK\*\bin', 'Windows Azure Emulator\emulator');
        foreach ($programDirectories as $programDirectory) {
            if (isset($_SERVER[$programDirectory])) {
                $programDirectory = $_SERVER[$programDirectory];
                foreach ($binDirectories as $binDirectory) {
                    if ($dirs = glob($programDirectory . '\\' . $binDirectory, GLOB_NOSORT)) {
                        return $dirs[0] . '\\';
                    }
                }
            }
        }

        throw new \RuntimeException("Cannot find Windows Azure SDK. You can download the SDK from http://www.windowsazure.com.");
    }

    public function getProcess($arguments)
    {
        $options['bypass_shell'] = true;

        $command = array_shift($arguments);

        $script = '"'.$command.'"';
        if ($arguments) {
            $script .= ' '.implode(' ', array_map('escapeshellarg', $arguments));
        }

        $script = 'cmd /V:ON /E:ON /C "'.$script.'"';
        return new Process($script, null, null, null, 60, $options);
    }
}

