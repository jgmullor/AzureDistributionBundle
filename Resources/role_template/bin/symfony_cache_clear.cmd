@echo off
cd "%~dp0"

ECHO Starting Symfony Setup... >> ..\startup-tasks-log.txt

"D:\Program Files (x86)\PHP\v5.3\php.exe" ..\app\console --env=azure cache:clear
ECHO Symfony Cache warmed up >> ..\startup-tasks-log.txt

