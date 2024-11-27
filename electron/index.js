const { app, BrowserWindow } = require('electron');
const { exec } = require('child_process');
const path = require('path');

let mainWindow;

function startLaravelServer() {
    const laravelProcess = exec(
        'php artisan serve --host=0.0.0.0 --port=8000',
        { cwd: path.join('C:\\xampp\\htdocs\\Dr.Benhamada') },
        (error, stdout, stderr) => {
            if (error) {
                console.error(`Erreur Artisan Serve : ${error.message}`);
                return;
            }
            console.log(`Serveur Laravel démarré : ${stdout}`);
        }
    );

    // Restart the Laravel server if it crashes
    laravelProcess.on('exit', (code) => {
        console.error(`Laravel server stopped unexpectedly with code ${code}. Restarting...`);
        setTimeout(startLaravelServer, 1000);
    });
}

function startMySQLServer() {
    const mysqlProcess = exec('C:\\xampp\\mysql_start.bat', (error, stdout, stderr) => {
        if (error) {
            console.error(`Erreur MySQL : ${error.message}`);
            return;
        }
        console.log(`MySQL démarré : ${stdout}`);
    });

    // Monitor MySQL process
    mysqlProcess.on('exit', (code) => {
        console.error(`MySQL server stopped unexpectedly with code ${code}. Restarting...`);
        setTimeout(startMySQLServer, 1000);
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
        mainWindow.loadURL('http://127.0.0.1:8000').catch((err) => {
            console.error(`Failed to load URL: ${err.message}`);
        });
    }, 5000); // Delay to ensure servers are ready
}

app.on('ready', () => {
    startMySQLServer();
    startLaravelServer();
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
