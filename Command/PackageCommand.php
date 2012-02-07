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
use Symfony\Component\Finder\Finder;

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
            ->addOption('long-path-detection', null, InputOption::VALUE_OPTIONAL, 'Long path detection will check for file-paths >= 248 chars which are not allowed.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Loading ServiceDefinition.csdef file..");
        $serviceDefinition = $this->getContainer()->get('windows_azure_distribution.config.service_definition');

        if ($input->getOption('long-path-detection')) {
            $this->detectTooLongPathNames($serviceDefinition, $output);
        }

        $azureCmdBuilder = $this->getContainer()->get('windows_azure_distribution.deployment.azure_sdk_command_builder');
        $output->writeln("Building Azure SDK packages into directory:");
        $output->writeln($azureCmdBuilder->getOutputDir());

        $args = $azureCmdBuilder->buildPackageCmd($serviceDefinition, $input->getOption('dev-fabric'));
        $process = $azureCmdBuilder->getProcess($args);// @todo: Update to ProcessBuilder in 2.1 Symfony
        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: $process->getOutput());
        }

        $output->writeln( $process->getOutput() );
    }

    protected function detectTooLongPathNames($serviceDefinition, $output)
    {
        $output->writeln("Detecting path that are longer than 248 chars.\n");
        $output->writeln("This can take some minutes...\n");
        $physicalDirs = $serviceDefinition->getPhysicalDirectories();
        $found = array();
        foreach ($physicalDirs as $dir) {
            $finder = new Finder();
            $iterator = $finder->files()->in($dir);

            foreach ($iterator as $file) {
                if (strlen($file->getRealpath()) >= 248) {
                    $output->writeln(sprintf("* %s (%d)", $file->getRealpath(), strlen($file->getRealpath())));
                    $found[] = $file->getRealpath();
                }
            }
        }

        if ($found) {
            $output->writeln(sprintf(
                "Found %d paths that are longer than 248 chars.\n" .
                "Azure does not support longer path names.\n" .
                "You should come up with a solution to fix this." .
                count($found)
            ));
            exit(1);
        }
        $output->writeln("None found, continuing with building package.");
    }
}

