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

    public function __construct($rootDir, $binDir = null)
    {
        $this->rootDir = $rootDir;
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
     * @param string $outputDir
     * @param bool $isDevFabric
     * @return array
     */
    public function buildPackageCmd(ServiceDefinition $serviceDefinition, $outputDir, $isDevFabric)
    {
        $args = array (
            $this->binDir . 'cspack.exe',
            $serviceDefinition->getPath()
        );
        foreach ($serviceDefinition->getWebRoleNames() as $roleName) {
            $args[] = $this->getRoleArgument($roleName, $serviceDefinition);
        }
        foreach ($serviceDefinition->getWorkerRoleNames() as $roleName) {
            $args[] = $this->getRoleArgument($roleName, $serviceDefinition);
        }
        $args[] = sprintf('/out:%s', $outputDir);

        if ($isDevFabric) {
            $args[] = '/copyOnly';
        }

        return $args;
    }

    public function buildDevStoreStartCmd()
    {
        return array($this->binDir . 'csrun.exe', '/devstore:start');
    }

    public function buildDevFabricStartCmd()
    {
        return array($this->binDir . 'csrun.exe', '/devfabric:start');
    }

    public function buildDevFabricRemoveAllCmd()
    {
        return array($this->binDir . 'csrun.exe', '/removeAll');
    }

    public function buildDevRunPackage($packagePath, ServiceConfiguration $serviceConfiguration)
    {
        return array($this->bindDir . 'csrun.exe', '/run:' . $packagePath . ';' . $serviceConfiguration->getPath(), '/launchBrowser');
    }

    private function getRoleArgument($roleName, $serviceDefinition)
    {
        $roleFilePath = sprintf('%s/%s.roleFiles.txt', $serviceDefinition->getPhysicalDirectory($roleName), $roleName);
        if (file_exists($roleFilePath)) {
            return sprintf('/roleFiles:%s;%s', $roleName, $roleFilePath);
        }
        return sprintf('/role:%s', $roleName);
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
            $script .= ' '.implode(' ', array_map(array($this, 'escape'), $arguments));
        }
        $script = 'cmd /V:ON /E:ON /C "'.$script.'"';
        return new Process($script, null, null, null, 60, $options);
    }

    private function escape($element)
    {
        if (strpos($element, '/out:') === 0) {
            return $element;
        }
        return escapeshellarg($element);
    }
}

