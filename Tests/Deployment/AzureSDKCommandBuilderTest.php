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

namespace WindowsAzure\DistributionBundle\Tests\Deployment;

use WindowsAzure\DistributionBundle\Deployment\ServiceDefinition;
use WindowsAzure\DistributionBundle\Deployment\AzureSDKCommandBuilder;

class AzureSDKCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPackageCommand()
    {
        $rootPath = "C:\symfony\app";
        $outputPath = "C:\output";
        $serviceDefFile = __DIR__ . '/_files/webrole_def.xml';
        $def = new ServiceDefinition($serviceDefFile);
        $builder = new AzureSDKCommandBuilder($rootPath, "c:\bin\\");

        $args = $builder->buildPackageCmd($def, $outputPath, true);
        $this->assertEquals(array(
            'c:\bin\cspack.exe',
            $serviceDefFile,
            '/role:TestRole',
            '/out:C:\output',
            '/copyOnly'
        ), $args);
    }

    public function testGetPackageCommandRoleFiles()
    {
        $rootPath = __DIR__ . '/_files/';
        $outputPath = "C:\output";
        $serviceDefFile = __DIR__ . '/_files/sf2role.xml';
        $def = new ServiceDefinition($serviceDefFile);
        $builder = new AzureSDKCommandBuilder($rootPath, "c:\bin\\");

        $args = $builder->buildPackageCmd($def, $outputPath, true);
        $this->assertEquals(array(
            'c:\bin\cspack.exe',
            $serviceDefFile,
            '/roleFiles:Sf2Web;'.$rootPath.'/Sf2Web.roleFiles.txt',
            '/out:C:\output',
            '/copyOnly'
        ), $args);
    }
}

