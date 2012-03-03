# Quickstart

This quickstart will guide you through the steps to deploy a clean Symfony2 application on Windows Azure. This will contain the AcmeDemoBundle that has a very simple hello world page.

## Using a downloadable Symfony version

1. Go to symfony.com/download and download the latest version with vendors. (Currently http://symfony.com/download?v=Symfony_Standard_Vendors_2.0.11.zip)

2. Unzip the archive into a directory.

3. Create a new subdirectory vendor\bundles\WindowsAzure\DistributionBundle

4. Download the WindowsAzure Distribution Bundle from https://github.com/beberlei/AzureDistributionBundle

5. Unzip the bundle and copy the contents into the vendor/bundles/WindowsAzure/DistributionBundle folder

6. Modify the app/autoload.php file to include the line `'WindowsAzure\\DistributionBundle' => __DIR__ . '/../vendor/bundles'` in the array inside the `registerNamespaces()` method.

7. Modify the app/AppKernel.php to include `new WindowsAzure\DistributionBundle\WindowsAzureDistributionBundle()` in the $bundles array. Also replace the `extends Kernel` with `extends AzureKernel` and add a new import statement to the top of the file `use WindowsAzure\DistributionBundle\HttpKernel\AzureKernel;`. Details of this step are described in the README.md of this project under the topic "Azure Kernel".

8. Open up the terminal and go to the project root. Call "php app\console". You should see a list of commands, containing two of the windows azure commands at the bottom:

    windowsazure:init
    windowsazure:package

9. Call `php app\console windowsazure:init`

10. Install the Azure TaskDemoBundle (optional) to see some of the features of Azure in a Demo application. See the section blow for a step by step introduction for this bundle.

11. Call `php app\console windowsazure:package`

12. Deploy the `build\ServiceDefinition.cscfg` and `build\azure.cspkg` using the management console

13. Browse to http://<myapp>.cloudapp.net/hello/world

## Installing the Task Demo Bundle

1. Downlad from https://github.com/beberlei/AzureTaskDemoBundle
2. Unzip files into src\WindowsAzure\TaskDemoBundle
3. Add `new WindowsAzure\TaskDemoBundle\WindowsAzureTaskDemoBundle()` into the `$bundles` array. 
4. Configure the database by modifying `app\config\azure_parameters.yml`.

An example of the parameters.yml looks like:

    # Put Azure Specific configuration parameters into
    # this file. These will overwrite parameters from parameters.yml
    parameters:
        session_type: pdo
        database_driver: pdo_sqlsrv
        database_host: tcp:DBID.database.windows.net
        database_user: USER@DBID
        database_password: PWD
        database_name: DBNAME

5. Configure Security

Open `app\config\security.yml` and exchange the line:

    - { resource: security.yml }

with the following line: 

    - { resource: ../../src/WindowsAzure/TaskDemoBundle/Resources/config/security.yml }

6. Import the contents of the "schema.sql" from src\WindowsAzure\TaskDemoBundle\Resources\schema.sql into your SQL Azure database.
