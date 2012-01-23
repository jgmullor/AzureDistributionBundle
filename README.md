# WindowsAzure Distribution Bundle

Bundle provides tools to deploy a Symfony2 based application on Windows Azure.

    Note: This bundle is in very early development and will change. The functionality mentioned above is not yet implemented fully.

## Architecture

Cloud-Services put constraints on how an application is allowed to run on their hosted services. This is done for security and scalability reasons. The following architecture constraints will be solved by this bundle:

* Startup tasks for cache:clear/cache:warmup are invoked for new instances.
* Writing cache and log-files into a writable directory.
* Distributed sessions (through PDO or Windows Azure Table)
* Deploying assets to Azure Blob Storage

