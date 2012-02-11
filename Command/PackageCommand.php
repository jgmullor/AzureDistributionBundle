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
            ->addOption('dev-fabric', null, InputOption::VALUE_NONE, 'Build package for dev-fabric? This will only copy the files and startup the Azure Simulator.')
            ->addOption('output-dir', null, InputOption::VALUE_REQUIRED, 'Output directory. Will override the default directory configured as approot/build.')
            ->addOption('skip-role-file-generation', null, InputOption::VALUE_NONE, 'Skip the generation of role files for the /roleFiles argument of cspack.exe. This will reuse old existing files.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Loading Azure Deployment details..");
        $deployment = $this->getContainer()->get('windows_azure_distribution.deployment');

        if ( ! $deployment->exists()) {
            $output->writeln('<error>No Azure deployment details found</error>');
            $output->writeln('Execute the windowsazure:init command to create the basic structure for a deployment.');
            return;
        }
        $output->writeln('..done.');
        $output->writeln('');

        $serviceDefinition = $deployment->getServiceDefinition();

        $outputDir = realpath( $input->getOption('output-dir') ?: $this->getContainer()->getParameter('windows_azure_distribution.config.application_root'). '/build' );
        $output->writeln("Building Azure SDK packages into directory:");
        $output->writeln($outputDir);

        if ( ! file_exists($outputDir) ) {
            $fs = new Filesystem();
            $fs->mkdir($outputDir, 0777);
            $output->writeln('<info>Output directory created, because it didn\'t exist yet.</info>');
        }

        if ( ! is_writeable($outputDir) ) {
            throw new \RuntimeException("Output-directory is not writable!");
        }

        if ( ! $input->getOption('skip-role-file-generation')) {
            $output->writeln('Starting to compile file manifests for each physical directory.');
            $s = microtime(true);
            $serviceDefinition->createRoleFiles($outputDir);
            $output->writeln('..compiled file-manifets. (Took ' . number_format(microtime(true) - $s, 4) . ' seconds)');
        }

        $output->writeln('Calling cspack.exe');
        $azureCmdBuilder = $this->getContainer()->get('windows_azure_distribution.deployment.azure_sdk_command_builder');
        $args = $azureCmdBuilder->buildPackageCmd($serviceDefinition, $outputDir, $input->getOption('dev-fabric'));
        $process = $azureCmdBuilder->getProcess($args);// @todo: Update to ProcessBuilder in 2.1 Symfony
        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: $process->getOutput());
        }

        $output->writeln( trim($process->getOutput()) );
    }
}

