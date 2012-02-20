# Quickstart

This quickstart will guide you through the steps to deploy a clean Symfony2 application on Windows Azure. This will contain the AcmeDemoBundle that has a very simple hello world page.

## Using a downloadable Symfony version

1. Go to symfony.com/download and download the latest version with vendors. (Currently http://symfony.com/download?v=Symfony_Standard_Vendors_2.0.10.zip)

2. Unzip the file into a directory.

3. Create a new subdirectory vendor/bundles/WindowsAzure/DistributionBundle

4. Download the WindowsAzure Distribution Bundle from https://github.com/beberlei/AzureDistributionBundle

5. Unzip the bundle and copy the contents into the vendor/bundles/WindowsAzure/DistributionBundle folder

6. Modify the app/autoload.php file to include the line 'WindowsAzure\\DistributionBundle' => __DIR__ . '/../vendor/bundles' in the 'registerNamespaces()' array.

7. Modify the app/AppKernel.php to include 'new WindowsAzure\DistributionBundle\WindowsAzureDistributionBundle()' in the $bundles array. Also replace the "extends Kernel" with "extends AzureKernel" and add a new import statement to the top of the file "use WindowsAzure\DistributionBundle\HttpKernel\AzureKernel;". Details of this step are described in the README.md of this project under the topic "Azure Kernel".

8. Open up the terminal and go to the project root. Call "php app\console". You should see a list of commands, containing two of the windows azure commands at the bottom:

    windowsazure:init
    windowsazure:package

9. Call 'php app\console windowsazure:init'

10. Call 'php app\console windowsazure:package'

11. Deploy the 'build\ServiceDefinition.cscfg' and 'build\azure.cspkg' using the management console

12. Browse to http://<myapp>.cloud.net/hello/world

