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
 * Initialize an Azure deployment
 *
 * Currently only exactly one web role is assumed/created when using the command.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class InitCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('windowsazure:init')
            ->setDescription('Initialize the basic necessary structure to deploy your Symfony project on Windows Azure')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deployment = $this->getContainer()->get('windows_azure_distribution.deployment');
        if ($deployment->exists()) {
            throw new \RuntimeException("Azure is already initialized for this Symfony project.");
        }

        $deployment->create();
        $deployment->createRole('Sf2.Web');

        $output->writeln('<info>Created basic Azure structure and one WebRole "SymfonyWeb"</info>');
    }
}
