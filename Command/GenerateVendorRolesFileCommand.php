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

namespace WindowsAzure\DistributionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use WindowsAzure\DistributionBundle\Deployment\VendorRoleFilesListener;

/**
 * Package a Symfony application for deployment.
 *
 * @author Dennis Benkert <dennis.benkert@sensiolabs.de>
 */
class GenerateVendorRolesFileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('windowsazure:generate:vendor-roles-file')
            ->setDescription('Generates the roles file of your projects vendor directory for Windows Azure deployments')
            ->addArgument('vendor-dir', InputArgument::REQUIRED, 'Your projects vendor dir')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Generating Windows Azure role file</info>');

        VendorRoleFilesListener::generateVendorRolesFile($input->getArgument('vendor-dir'));
    }
}