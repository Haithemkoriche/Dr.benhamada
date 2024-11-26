const { app, BrowserWindow, shell } = require('electron');
const path = require('path');
// const AutoLaunch = require('electron-auto-launch');
const { exec } = require('child_process');
let mainWindow;
// const Registry = require('electron-windows-registry');

// Configuration pour démarrer automatiquement l'application
// const myAppLauncher = new AutoLaunch({
//     name: 'Dr.Benhamada', // Remplacez par le nom de votre application
//     path: process.execPath,         // Chemin vers le fichier exécutable de votre application
// });

// myAppLauncher.isEnabled()
//     .then((isEnabled) => {
//         if (!isEnabled) {
//             return myAppLauncher.enable(); // Active le démarrage automatique si ce n'est pas encore fait
//         }
//     })
//     .catch((err) => {
//         console.error('Erreur lors de l\'activation de l\'auto-lancement :', err);
//     });
// function createWindow() {
//     const win = new BrowserWindow({
//         width: 1280,
//         height: 720,
//         webPreferences: {
//             nodeIntegration: true,
//             contextIsolation: false,
//             enableRemoteModule: true,
//         },
//     });

//     // Charger l'application Vuexy en mode production
//     win.loadURL('http://localhost:8000'); // L'URL de votre application Laravel en local
// }

app.on('ready', () => {
    // const autoLaunchKey = new Registry({
    //     hive: 'HKCU',
    //     key: '\\Software\\Microsoft\\Windows\\CurrentVersion\\Run',
    // });
    // autoLaunchKey.set('Dr.Benhamada', process.execPath, Registry.REG_SZ, (err) => {
    //     if (err) {
    //         console.error('Erreur lors de l\'ajout à l\'auto-lancement :', err);
    //     } else {
    //         console.log('L\'application est configurée pour démarrer au lancement de Windows.');
    //     }
    // });
    // Créer la fenêtre Electron
    mainWindow = new BrowserWindow({
        width: 1280,
        height: 800,
        webPreferences: {
            nodeIntegration: true,
        },
    });

    // Lancer `php artisan serve`
    exec('php artisan serve --host=127.0.0.1 --port=8000', { cwd: 'C:\\xampp\\htdocs\\Dr.Benhamada' }, (error, stdout, stderr) => {
        if (error) {
            console.error(`Erreur Artisan Serve : ${error.message}`);
            return;
        }
        console.log(`Serveur Laravel démarré : ${stdout}`);
    });
    exec('npm run dev', { cwd: 'C:\\xampp\\htdocs\\Dr.Benhamada' }, (error, stdout, stderr) => {
        if (error) {
            console.error(`Erreur NPM Run Dev : ${error.message}`);
            return;
        }
        console.log(`Vue.js démarré : ${stdout}`);
    });
    exec('C:\\xampp\\mysql_start.bat', (error, stdout, stderr) => {
        if (error) {
            console.error(`Erreur MySQL : ${error.message}`);
            return;
        }
        console.log(`MySQL démarré : ${stdout}`);
    });

    // Attendre que les serveurs soient prêts avant de charger l'URL
    setTimeout(() => {
        mainWindow.loadURL('http://127.0.0.1:8000');
    }, 5000); // Délai de 5 secondes
});


app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
        createWindow();
    }
});
