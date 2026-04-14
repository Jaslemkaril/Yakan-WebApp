# GitHub QR Access Guide

This project now includes a presentation-ready QR code that opens the GitHub folder.

## Files Added

- public/github-folder-qr.png
- public/github-qr.html

## GitHub Target URL

https://github.com/Jaslemkaril/Yakan-WebApp/tree/main

## How To Use During Presentation

1. Start your Laravel server.
2. Open http://localhost:8000/github-qr.html
3. Maximize browser or enter full screen.
4. Ask panelists to scan the QR code.

## If You Need To Regenerate The QR

Use this PowerShell command from the project root:

powershell
$repoUrl = 'https://github.com/Jaslemkaril/Yakan-WebApp/tree/main'
$encoded = [System.Uri]::EscapeDataString($repoUrl)
Invoke-WebRequest -Uri "https://quickchart.io/qr?text=$encoded&size=1200&ecLevel=H&margin=2" -OutFile "public/github-folder-qr.png"

