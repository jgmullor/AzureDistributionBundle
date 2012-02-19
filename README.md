# WindowsAzure Distribution Bundle

Bundle provides tools to deploy a Symfony2 based application on Windows Azure.

    Note: This bundle is in very early development and will change. The functionality mentioned above is not yet implemented. Implemented functionality may not be working correctly yet.

## Architecture

Cloud-Services put constraints on how an application is allowed to run on their hosted services. This is done for security and scalability reasons. The default Symfony Sandbox won't run on Azure without some slight changes. The Azure deployment related tasks will be solved by this bundle:

* Command for packaging applications for Windows Azure (Done)
* Startup tasks for cache:clear/cache:warmup are invoked for new instances. (Done)
* Writing cache and log-files into a writable directory. (Done)
* Distributed sessions (through PDO or Windows Azure Table)
* Deploying assets to Azure Blob Storage
* Aid for generation remote desktop usables instances and other common configuration options for ServiceDefinition.csdef and ServiceConfiguration.cscfg
* Wrapper API for access to Azure Globals such as RoleId etc.
* Logging to a central server/blob storage.

## Azure Roles and Symfony applications

Windows Azure ships with a concept of roles. You can have different Web- or Worker Roles and each of them can come in one or many instances. Web- and Worker roles don't easily match to a Symfony2 application.

Symfony applications encourage code-reuse while azure roles enforce complete code seperation. You can have a Multi Kernel application in Symfony, but that can still contain both commands (Worker) and controllers (Web).

Dividing the code that is used on a worker or a web role for Azure will amount to considerable work. However package size has to be taken into account for faster boot operations. This is why the most simple approach to role building with Symfony2 is to ship all the code no matter what the role is. This is how this bundle works by default. If you want to keep the packages smaller you can optionally instruct the packaging to lazily fetch vendors using the Composer library.

## Installation

For [Composer](http://www.packagist.org)-based application, add this package to your composer.json:

    ```javascript
    {
        "require": {
            "beberlei/azure-distribution-bundle": "*"
        }
    }
    ```

For a 'bin/vendors' based application add the Git path to your 'deps' file.

## Azure Kernel

The Azure kernel can be used to set the temporary and cache directories to `sys_get_tempdir()` on production. These are the only writable directories for the webserver on Azure.

    ```php
    <?php

    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Config\Loader\LoaderInterface;
    use WindowsAzure\DistributionBundle\HttpKernel\AzureKernel;

    class AppKernel extends AzureKernel
    {
        $bundles = array(
            // ...
            new WindowsAzure\DistributionBundle\WindowsAzureDistributionBundle();
            // ...
        );

        // keep the old code here.

        return $bundles;
    }
    ```

This feature is totally optional. You can make the necessary modifications yourself to get the application running on Azure by pointing the log and cache directory to `sys_get_temp_dir()`.

## Packaging

Before you start generating Azure packages you have to create the basic infrastructure. This bundle will create a folder `app/azure` with a bunch of configuration and resource files that are necessary for deployment. To initialize this infrastructure call:

    php app\console windowsazure:init

The main configuration files are `app\azure\ServiceDefinition.csdef` and `app\aure\ServiceConfiguration.cscfg`. They will be created with one Azure role "Sf2Azure.Web", which will is configured as an HTTP application.

To generate an Azure package just invoke:

    php app\console windowsazure:package

The command will then try and generate an Azure package for you given the current azure Configuration files. You can re-run this script as often as you want. The result will be generated into the 'build' directory of your application.

The deployment process (windowsazure:package) executes the following steps:

1. For every role a list of all files to be deployed is generated into the file %RoleName.roleFiles.txt. These files will be used with the `/roleFiles` parameter of [cspack.exe](http://msdn.microsoft.com/en-us/library/windowsazure/gg432988.aspx). By default directories with the name 'tests', 'Tests', 'build', 'logs', 'cache', 'docs' and 'test-suite' are excluded from the deployment.

For configuration of this process see the "Configure Packaging" section of the docs.

Because compiling the roles file can take some time you can optimize this process. See the "Optimizing Packaging" section in the docs for this.

2. cspack.exe is called with the appropriate parameters.

3. If the --dev-fabric flag isset after package generation the Azure dev-fabric will be started.

## Optimizing Packaging

Packaging always compiles a list of files to deploy before the actual packaging takes place and cspack.exe is called. This list of files includes the vendor directory by default. The vendor directory contains the largest amount of code in your application and takes longest to search through. However vendor code is rather static. The only time files are updated here is when you call:

1. php bin/vendors in Symfony 2.0.x
2. composer update/install in Symfony starting 2.1

You can hook in both vendor updating processes to directly generate a list of files once and re-use it whenever you call 'app\console windowsazure:package'. This can speedup this step of deployment by roughly 90%.

To get this workin under Symfony 2.0.x put the following code into your 'bin/vendors' php script:

    \WindowsAzure\DistributionBundle\Deployment\VendorRoleFilesListener::generateVendorRolesFile(__DIR__ . "/../vendor");

In Symfony 2.1 and higher put the following block into your applications composer.json:

    "scripts": {
        "post-update-cmd": "WindowsAzure\\DistributionBundle\\Deployment\\VendorRoleFilesListener::listenPostInstallUpdate",
        "post-install-cmd": "WindowsAzure\\DistributionBundle\\Deployment\\VendorRoleFilesListener::listenPostInstallUpdate"
    }

## Configure Packaging

..

## Troubleshooting

1. Large cspkg files

Currently the vendor directories are included in cspkg, which can lead to large files to be generated. This obviously causes network traffic and initial startup time overhead. The plan is to change this in the near future by using composer to fetch all the necessary dependencies on the web and worker roles during deployment.

2. Path is too long

Some Symfony2 bundles contain deep paths and hence your app does too. Windows filesystem functions have a limit of 248/260 chars for paths/files. This can lead to problem when packaging Azure applications, because the files are copied to a temporary directory inside the user directory during this operation. This bundle includes a detection for very long file names and throws an exception detailing the file paths that can cause problems.

Another solution when this occurs during execution of "csrun.exe" is to set the environment variable `_CSRUN_STATE_DIRECTORY=c:\t` to a small directory, in this case `c:\t`.

3. Access denied during cspack.exe

You have to make sure that the specified output-path is writable and that the files/directories in there can be cleaned up during the package command.
