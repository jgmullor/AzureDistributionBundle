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

use Symfony\Component\Finder\Finder;

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
        return $this->getValues('WebRole', 'name');
    }

    public function getWorkerRoleNames()
    {
        return $this->getValues('WorkerRole', 'name');
    }

    public function getRoleNames()
    {
        return array_merge($this->getWebRoleNames(), $this->getWorkerRoleNames());
    }

    public function addWebRole($name)
    {
        $existingRoles = $this->getRoleNames();
        if (in_array($name, $existingRoles)) {
            throw new \RuntimeException(sprintf("Role with name %s already exists.", $name));
        }

        $webrole = new \DOMDocument('1.0', 'UTF-8');
        $webrole->load(__DIR__ . '/../Resources/role_template/WebRole.xml');

        $roles = $webrole->getElementsByTagName('WebRole');
        $webRoleNode = $roles->item(0);
        $webRoleNode->setAttribute('name', $name);

        $webRoleNode = $this->dom->importNode($webRoleNode, true);
        $this->dom->documentElement->appendChild($webRoleNode);

        if ($this->dom->save($this->serviceDefinitionFile) === false) {
            throw new \RuntimeException(sprintf("Could not write ServiceDefinition to '%s'",
                $this->serviceDefinitionFile));
        }
    }

    private function getValues($tagName, $attributeName)
    {
        $nodes = $this->dom->getElementsByTagName($tagName);
        $values = array();
        foreach ($nodes as $node) {
            $values[] = $node->getAttribute($attributeName);
        }
        return $values;
    }

    public function getPhysicalDirectories()
    {
        $nodes = $this->dom->getElementsByTagName('WebRole');
        $dirs = array();
        foreach ($nodes as $node) {
            $sites = $node->getElementsByTagName('Site');
            if (count($sites)) {
                $dirs[$node->getAttribute('name')] = realpath(
                    dirname($this->serviceDefinitionFile) .
                    $sites->item(0)->getAttribute('physicalDirectory')
                );
            }
        }
        return $dirs;
    }

    public function getPhysicalDirectory($name)
    {
        $dirs = $this->getPhysicalDirectories();
        if (!isset($dirs[$name])) {
            throw new \RuntimeException(sprintf("There exists no role named '%s'.", $name));
        }
        return $dirs[$name];
    }

    public function createRoleFiles($outputDir)
    {
        $outputDir = realpath($outputDir);
        $s = microtime(true);
        $physicalDirs = $this->getPhysicalDirectories();
        $found = array();
        $s = microtime(true);
        $seenDirs = array();
        $longPaths = array();
        foreach ($physicalDirs as $roleName => $dir) {
            $dir = realpath($dir);
            if (isset($seenDirs[$dir])) {
                continue;
            }
            $seenDirs[$dir] = true;

            $roleFile = "";
            $finder = new Finder();
            $length = strlen($dir) + 1;
            $iterator = $finder->files()
                               ->in($dir)
                               ->ignoreDotFiles(true)
                               ->ignoreVCS(true)
                               ->exclude('build')
                               ->exclude('cache')
                               ->exclude('logs')
                               ->exclude('Tests')
                               ->exclude('tests')
                               ->exclude('docs')
                               ->exclude('test-suite')
                               ->exclude('role_template')
                               ->notName('*.swp')
                            ;

            foreach ($iterator as $file) {
                $path = substr($file, $length);
                $checkPath = $outputDir . "/roles/$roleName/approot/" . $path;
                if (strlen($checkPath) >= 248) {
                    $longPaths[] = $checkPath . " (". strlen($checkPath) . ")";
                }
                $roleFile .= $path .";".$path."\n";
            }
            file_put_contents($dir . "/roleFiles.txt", $roleFile);
        }

        if ($longPaths) {
            throw new \RuntimeException("Paths are too long. Not more than 248 chars per directory and 260 per file name allowed:\n" . implode("\n", $longPaths));
        }
    }
}

