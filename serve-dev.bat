@echo off
cd /d "%~dp0"
set PHP_BIN=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe
if not exist "%PHP_BIN%" (
    echo Laragon PHP not found at %PHP_BIN%
    echo Falling back to PATH php...
    set PHP_BIN=php
)
"%PHP_BIN%" -d upload_max_filesize=2G -d post_max_size=2G -d memory_limit=512M -d max_execution_time=600 artisan serve --host=0.0.0.0 --port=8003
