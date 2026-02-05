Start-Process powershell "-NoExit -Command php artisan reverb:start"
Start-Process powershell "-NoExit -Command php artisan queue:work"
Start-Process powershell "-NoExit -Command npm run dev"
Start-Process powershell "-NoExit -Command php artisan schedule:work"
