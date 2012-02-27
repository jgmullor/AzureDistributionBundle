# Azure Environment

The WindowsAzure DistributionBundle creates a new environment called 'azure' that inherits from 'prod'. This happens when you call `php app\console windowsazure:init`. The following files are responsible for this environment:

    * app/config/config_azure.yml
    * app/config/parameters_azure.yml
    * app/azure/Sf2.Web/index.php

The two YAML files contain the Azure specific configuration of your application. This configuration will only be used when deploying on Microsoft Windows Azure. The parameters_azure.yml contains parameters to override that are also in the parameters.yml.

The index.php file bootstraps the kernel with the 'azure' environment.

Whenever you need to execute a command on Windows Azure you have to remember to set the environment flag, for example:

    php app\console --env=azure cache:clear



