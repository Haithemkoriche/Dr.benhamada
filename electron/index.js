const { app, BrowserWindow } = require('electron');
const { exec } = require('child_process');
const path = require('path');

let mainWindow;

function startLaravelServer() {
    exec('php artisan serve --host=0.0.0.0 --port=8000', { cwd: path.join('C:\\xampp\\htdocs\\Dr.Benhamada') }, (error, stdout, stderr) => {
        if (error) {
            console.error(`Erreur Artisan Serve : ${error.message}`);
            return;
        }
        console.log(`Serveur Laravel démarré : ${stdout}`);
    });
}

function startMySQLServer() {
    exec('C:\\xampp\\mysql_start.bat', (error, stdout, stderr) => {
        if (error) {
            console.error(`Erreur MySQL : ${error.message}`);
            return;
        }
        console.log(`MySQL démarré : ${stdout}`);
    });
}

function createMainWindow() {
    mainWindow = new BrowserWindow({
        width: 1280,
        height: 800,
        webPreferences: {
            nodeIntegration: true,
        },
    });

    setTimeout(() => {
        mainWindow.loadURL('http://127.0.0.1:8000');
    }, 1000);
}

app.on('ready', () => {
    startLaravelServer();
    startMySQLServer();
    createMainWindow();
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
        createMainWindow();
    }
});
