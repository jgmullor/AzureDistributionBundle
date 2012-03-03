[Reflection.Assembly]::LoadWithPartialName("Microsoft.WindowsAzure.ServiceRuntime")

$rdRoleId = [Environment]::GetEnvironmentVariable("RdRoleId", "Machine")

[Environment]::SetEnvironmentVariable("RdRoleId", [Microsoft.WindowsAzure.ServiceRuntime.RoleEnvironment]::CurrentRoleInstance.Id, "Machine")
[Environment]::SetEnvironmentVariable("RoleName", [Microsoft.WindowsAzure.ServiceRuntime.RoleEnvironment]::CurrentRoleInstance.Role.Name, "Machine")
[Environment]::SetEnvironmentVariable("RoleInstanceID", [Microsoft.WindowsAzure.ServiceRuntime.RoleEnvironment]::CurrentRoleInstance.Id, "Machine")
[Environment]::SetEnvironmentVariable("RoleDeploymentID", [Microsoft.WindowsAzure.ServiceRuntime.RoleEnvironment]::DeploymentId, "Machine")

if (![Microsoft.WindowsAzure.ServiceRuntime.RoleEnvironment]::CurrentRoleInstance.Id.Contains("deployment")) {
    if ($rdRoleId -ne [Microsoft.WindowsAzure.ServiceRuntime.RoleEnvironment]::CurrentRoleInstance.Id) {
        Restart-Computer
    }
}

[Environment]::SetEnvironmentVariable('Path', $env:RoleRoot + '\base\x86;' + [Environment]::GetEnvironmentVariable('Path', 'Machine'), 'Machine')
[Environment]::SetEnvironmentVariable("SymfonyAzureFileCache", [Microsoft.WindowsAzure.ServiceRuntime.RoleEnvironment]::GetLocalResource('SymfonyFileCache').RootPath, "Machine")
[Environment]::SetEnvironmentVariable("SymfonyAzureLogFiles", [Microsoft.WindowsAzure.ServiceRuntime.RoleEnvironment]::GetLocalResource('SymfonyLogFiles').RootPath, "Machine")

# Detect the script path of this ps1, assume its in "approot\bin" and
# find the application directory. Set this as environment variable.
# We need the application root in our front-controller to find the
# application code.
$scriptPath = Split-Path -parent $MyInvocation.MyCommand.Definition
$applicationPath = Join-Path $scriptPath "\..\"
[Environment]::SetEnvironmentVariable("ApplicationPath", (Resolve-Path $applicationPath).Path, "Machine")
