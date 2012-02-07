# WindowsAzure Distribution Bundle

Bundle provides tools to deploy a Symfony2 based application on Windows Azure.

    Note: This bundle is in very early development and will change. The functionality mentioned above is not yet implemented fully.

## Architecture

Cloud-Services put constraints on how an application is allowed to run on their hosted services. This is done for security and scalability reasons. The following architecture constraints will be solved by this bundle:

* Startup tasks for cache:clear/cache:warmup are invoked for new instances.
* Writing cache and log-files into a writable directory.
* Distributed sessions (through PDO or Windows Azure Table)
* Deploying assets to Azure Blob Storage

## Azure Roles and Symfony applications

Windows Azure ships with a concept of roles. You can have different Web- or Worker Roles and each of them can come in one or many instances. Web- and Worker roles don't easily match to a Symfony2 application.

Symfony applications encourage code-reuse while azure roles enforce complete code seperation. You can have a Multi Kernel application in Symfony, but that can still contain both commands (Worker) and controllers (Web).

Dividing the code that is used on a worker or a web role for Azure will amount to considerable work. However package size has to be taken into account for faster boot operations. This is why the most simple approach to role building with Symfony2 is to ship all the code no matter what the role is. This is how this bundle works by default. If you want to keep the packages smaller you can optionally instruct the packaging to lazily fetch vendors using the Composer library.
