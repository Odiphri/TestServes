# This script is for local Windows development only. Do not use it for production deployment.
Set-Location -LiteralPath $PSScriptRoot
php artisan serve --host=127.0.0.1 --port=8000 *> storage\logs\artisan-serve-runner.log
"exit=$LASTEXITCODE" | Out-File -FilePath storage\logs\artisan-serve-exit.log -Encoding utf8
