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

/**
 * Wraps the ServiceDefinition.csdef file and allows convenient access.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ServiceDefinition
{
    /**
     * @var string
     */
    private $serviceDefinitionFile;

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @param string $serviceDefinitionFile
     */
    public function __construct($serviceDefinitionFile)
    {
        if (!file_exists($serviceDefinitionFile)) {
            throw new \InvalidArgumentException(sprintf(
                "No valid file-path given. The ServiceDefinition should be at %s but could not be found.",
                $serviceDefinitionFile
            ));
        }

        $this->serviceDefinitionFile = $serviceDefinitionFile;
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
        $this->dom->load($this->serviceDefinitionFile);
    }

    public function getPath()
    {
        return $this->serviceDefinitionFile;
    }

    public function getWebRoleNames()
    {
        return $this->getRoleNames("WebRole");
    }

    public function getWorkerRoleNames()
    {
        return $this->getRoleNames("WorkerRole");
    }

    private function getRoleNames($tagName)
    {
        $nodes = $this->dom->getElementsByTagName($tagName);
        $roleNames = array();
        foreach ($nodes as $node) {
            $roleNames[] = $node->getAttribute('name');
        }
        return $roleNames;
    }
}

