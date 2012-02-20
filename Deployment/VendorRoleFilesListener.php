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

namespace WindowsAzure\DistributionBundle\Deployment;

use Symfony\Component\Finder\Finder;

/**
 * Utility Listener for Composer and bin/vendors to automatically generate new
 * azureRoleFiles.txt for all vendor files which can be re-used during
 * packaging and considerably speed up the operation.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class VendorRoleFilesListener
{
    /**
     * Generating an azureRoleFiles.txt as post updatE/install hook in
     * composer.
     *
     * Put the following into your root packages "composer.json":
     *
     * "scripts": {
     *    "post-update-cmd": "WindowsAzure\\DistributionBundle\\Deployment\\VendorRoleFilesListener::listenPostInstallUpdate",
     *    "post-install-cmd": "WindowsAzure\\DistributionBundle\\Deployment\\VendorRoleFilesListener::listenPostInstallUpdate"
     * }
     *
     * @param object $event
     * @return void
     */
    static public function listenPostInstallUpdate($event)
    {
        $io = $event->getIo();
        $io->write('<info>Generating vendor/azureRoleFiles.txt for Azure deployment</info>', true);
        $im = $event->getComposer()->getInstallationManager();
        $vendorPath = $im->getVendorPath(true);
        self::generateVendorRolesFile($vendorPath);
    }

    static public function generateVendorRolesFile($vendorDir)
    {
        $vendorDir = realpath($vendorDir);
        if ( !file_exists($vendorDir) || !is_dir($vendorDir)) {
            throw new \RuntimeException("No valid vendor directory given.");
        }
        $dirName = basename($vendorDir);

        $finder = new Finder();
        $finder->files()
               ->in($vendorDir)
               ->ignoreVCS(true)
               ->ignoreDotFiles(false)
               ->exclude('tests')
               ->exclude('Tests')
               ->exclude('test-suite')
               ->exclude('docs')
               ->notName('#(.*)\.swp$#');

        $length = strlen($vendorDir) + 1;
        $roleFile = "";
        foreach ($finder as $file) {
            if (is_dir($file)) {
                continue;
            }
            $path = $dirName . '\\' . str_replace(DIRECTORY_SEPARATOR, "\\", substr($file, $length));
            $roleFile .= $path .";".$path."\r\n";
        }
        file_put_contents($vendorDir."/azureRoleFiles.txt", $roleFile);
    }
}

