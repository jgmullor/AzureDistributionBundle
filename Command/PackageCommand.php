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

namespace WindowsAzure\DistributionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Package a Symfony application for deployment.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class PackageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('windowsazure:package')
            ->setDescription('Packages this symfony application for deployment on Windows Azure.')
            ->addOption('dev-fabric', null, InputOption::VALUE_OPTIONAL, 'Build package for dev-fabric? Defaults to yes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serviceDefinition = $this->getContainer()->get('windows_azure_distribution.config.service_definition');
        $azureCmdBuilder = $this->getContainer()->get('windows_azure_distribution.deployment.azure_sdk_command_builder');
        $cmd = $azureCmdBuilder->buildPackageCmd($serviceDefinition, $input->getOption('dev-fabric'));

        $process = new Process($this->getAzureSdkBinaryFolder() . '\\' . $cmd);
        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln( $process->getOutput() );
    }

    private function getAzureSdkBinaryFolder()
    {
        $programDirectories = array('ProgramFiles', 'ProgramFiles(x86)', 'ProgramW6432');
        $binDirectories = array('Windows Azure SDK\*\bin', 'Windows Azure Emulator\emulator');
        foreach ($programDirectories as $programDirectory) {
            foreach ($binDirectories as $binDirectory) {
                if ($dirs = glob($programDirectory . '\\' . $binDirectory, GLOB_NOSORT)) {
                    return $dirs;
                }
            }
        }

        throw new \RuntimeException("Cannot find Windows Azure SDK. You can download the SDK from http://www.windowsazure.com.");
    }
}

