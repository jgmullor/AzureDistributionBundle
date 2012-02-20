@echo off
cd "%~dp0"

IF EXIST ..\composer.json IF NOT EXIST ..\vendor\.composer (
    ECHO Composer installing vendors... >> ..\startup-tasks-log.txt

    'D:\Program Files (x86)\PHP\v5.3\php.exe' ..\bin\composer.phar install
    ECHO Vendors installed >> ..\startup-tasks-log.txt
)

ECHO Starting Symfony Setup... >> ..\startup-tasks-log.txt

'D:\Program Files (x86)\PHP\v5.3\php.exe' ..\app\console --env=prod cache:clear
ECHO Symfony Cache warmed up >> ..\startup-tasks-log.txt

