@echo off

:: Chemin vers le dossier de votre projet Laravel
cd /d C:\chemin\vers\votre\projet

:: Lancer le serveur Laravel
start php artisan serve --host=127.0.0.1 --port=8000

:: Lancer le processus npm
start npm run dev

:: Empêche la fermeture immédiate de la console (optionnel)
pause
