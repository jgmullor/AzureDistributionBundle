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
        $kernel = $this->getContainer()->get('kernel');
        $rootDir = $kernel->getRootDir();
        $azureDir = $rootDir . '/azure';
        $filesystem = new Filesystem();

        if ( ! file_exists($azureDir) ) {
            $output->writeln('No WindowsAzure directory found. Creating in <info>%s</info>', $azureDir));
            $filesystem->mirror($kernel->locateResource("@WindowsAzureDistributionBundle/Resources/azure_scaffold"), $azureDir, null, array('copy_on_windows' => true));
        }
    }
}

