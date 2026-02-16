#!/usr/bin/env powershell
$env:GIT_PAGER = ""
cd "c:\xamppp\htdocs\Yakan-WebApp"

Write-Host "Adding changes..." -ForegroundColor Green
git add .

Write-Host "Committing changes..." -ForegroundColor Green
git commit -m "Fix chat image uploads to use persistent storage"

Write-Host "Pushing to main..." -ForegroundColor Green
git push origin main

Write-Host "Done!" -ForegroundColor Green
