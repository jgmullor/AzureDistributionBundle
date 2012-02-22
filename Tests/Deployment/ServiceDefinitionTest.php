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

class ServiceDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetWebRoleNames()
    {
        $def = new ServiceDefinition(__DIR__ . "/_files/webrole_def.xml");
        $this->assertEquals(array("TestRole"), $def->getWebRoleNames());
    }

    public function testGetWorkerRoleNames()
    {
        $def = new ServiceDefinition(__DIR__ . "/_files/workerrole_def.xml");
        $this->assertEquals(array("WorkerRoleTest"), $def->getWorkerRoleNames());
    }

    public function testCreateRoleFiles()
    {
        $def = new ServiceDefinition(__DIR__ . "/_files/webrole_def.xml");
        $roleFiles = $def->createRoleFiles(__DIR__ . '/../..', __DIR__, sys_get_temp_dir());

        $data = file_get_contents($roleFiles["TestRole"]);
        $this->assertContains('Deployment\ServiceDefinition.php;Deployment\ServiceDefinition.php', $data);
    }
}

