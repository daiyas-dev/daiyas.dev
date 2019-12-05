const { app, BrowserWindow } = require("electron");
const path = require("path");
const ipc = require("electron").ipcMain;
const log = require("electron-log");

//AutoUpdater
const { appUpdater } = require("./autoUpdater.js");
const version = app.getVersion();

let mainWindow;

//CTI Config
let ctiSocket;
let autoUpdater;
const HOST = "localhost";
const PORT_R = 2002;
const PORT_S = 2010;

function createWindow() {
    log.transports.file.level = "debug";

    mainWindow = new BrowserWindow({
        title: "ダイヤスクライアント",
        icon: __dirname + "/daiyas48.ico",
        width: 1800,
        height: 800,
        center: true,
        //fullscreen: true,
        webPreferences: {
            preload: path.join(__dirname, "preload.js"),
            devTools: true,
            nodeIntegration: false,
        }
    });

    //AutoUpdater
    autoUpdater = appUpdater(log);
    mainWindow.webContents.send("log", "autoUpdater", autoUpdater);
    var updateInterval = setInterval(() => autoUpdater.checkForUpdatesAndNotify(), 10000);

    //mainWindow.setMenu(null);
    mainWindow.webContents.openDevTools();    //TODO: to debug

    mainWindow.loadURL("https://daiyas.dev/");

    mainWindow.on("closed", function () {
        mainWindow = null
    });

    //dgrapm
    try {
        const dgram = require("dgram");

        ctiSocket = dgram.createSocket({ type: "udp4", reuseAddr: true });

        ctiSocket.on("listening", () => {
            const address = ctiSocket.address();
            console.log("UDP ctiSocket listening on " + address.address + ":" + address.port);
            mainWindow.webContents.send("CTI_Listening");
        });

        ctiSocket.on("message", (message, remote) => {
            console.log(remote.address + ":" + remote.port + " - " + message);
            mainWindow.webContents.send("CTI_MessageFromMain", message);
        });

        ctiSocket.bind(PORT_R, HOST);
    } catch (error) {
        mainWindow.webContents.send("CTI_BindError", error);
        console.log("udp error", error);
    }

    //shared objects
    global.shared = {
        app: app,
        ipc: ipc,
        mainWindow: mainWindow,
        autoUpdater: autoUpdater,
        ctiSocket: ctiSocket,
    };

    mainWindow.webContents.send("Shared", global.shared);
};

app.on("ready", createWindow);

app.on("window-all-closed", function () {
    if (process.platform !== "darwin") app.quit()
});

app.on("activate", function () {
    if (mainWindow === null) createWindow();
});

//ipc handlers
ipc.on("CTI_MessageFromRender", (e, arg) => {
    console.log("CTI_MessageFromRender", arg);

    ctiSocket.send(arg, 0, arg.length, PORT_S, HOST, (err, bytes) => {
        if (err) {
            console.log("CTI_SendError", err);
            mainWindow.webContents.send("CTI_SendError", err);
        }
    });
});

