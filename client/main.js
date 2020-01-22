const { app, BrowserWindow, screen } = require("electron");
const path = require("path");
const ipcMain = require("electron").ipcMain;
const log = require("electron-log");

//AutoUpdater
const { appUpdater } = require("./autoUpdater.js");
const version = app.getVersion();

let mainWindow;
let printWindow;

//CTI Config
let ctiSocket;
let autoUpdater;
const HOST = "localhost";
const PORT_R = 2002;
const PORT_S = 2010;

function showLogs(...params) {
    console.log("log", params);
    mainWindow.webContents.send("log", params);
}

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


    //print window
    printWindow = new BrowserWindow({
        title: "印刷プレビュー",
        icon: __dirname + "/daiyas48.ico",
        show: false,
        center: false,
        parent: mainWindow,
        modal: true,
        resizable: false,
        maximizable: false,
        minimizable: false,
        closable: false,
        webPreferences: {
            nodeIntegration: true,
        }
    });
    printWindow.loadURL("file://" + __dirname + "/print.html");

    //dgrapm
    try {
        const dgram = require("dgram");

        ctiSocket = dgram.createSocket({ type: "udp4", reuseAddr: true });

        ctiSocket.on("listening", () => {
            const address = ctiSocket.address();
            showLogs("UDP ctiSocket listening on " + address.address + ":" + address.port);
            mainWindow.webContents.send("CTI_Listening");
        });

        ctiSocket.on("message", (message, remote) => {
            showLogs(remote.address + ":" + remote.port + " - " + message);
            mainWindow.webContents.send("CTI_MessageFromMain", message);
        });

        ctiSocket.bind(PORT_R, HOST);
    } catch (error) {
        mainWindow.webContents.send("CTI_BindError", error);
        showLogs("udp error", error);
    }
};

app.on("ready", createWindow);

app.on("window-all-closed", function () {
    if (process.platform !== "darwin") app.quit()
});

app.on("activate", function () {
    if (mainWindow === null) createWindow();
});

//ipcMain handlers
ipcMain.on("command", (e, command) => {
    try {
        var ret = eval(command);
        showLogs("command success", command, ret);
    } catch (err) {
        showLogs("command error", command, err);
    }
    var ret = eval(command);
});
ipcMain.on("CTI_MessageFromRender", (e, arg) => {
    showLogs("CTI_MessageFromRender", arg);

    ctiSocket.send(arg, 0, arg.length, PORT_S, HOST, (err, bytes) => {
        if (err) {
            showLogs("CTI_SendError", err);
            mainWindow.webContents.send("CTI_SendError", err);
        }
    });
});

//print
var printOptions = {}
ipcMain.on("Print_Req", (event, content, options) => {
    printOptions = options || {};
    showLogs("main Print_Req", content, options, printOptions);

    var printStyle = printOptions.style.match(/@media print \{.+ \}/);
    var landscape = printStyle.length && printStyle[0].includes("landscape");

    var mainBounds = mainWindow.getBounds();
    var mw = mainBounds.width;
    var mh = mainBounds.height;

    var sw = screen.getPrimaryDisplay().workAreaSize.width;
    var sh = screen.getPrimaryDisplay().workAreaSize.height;

    var w, h;
    if (landscape) {
        w = sw * 0.7;
        h = w * 5 / 7;
    } else {
        h = sh * 0.9;
        w = h * 5 / 7;
    }

    // printWindow.setMenu(null);   //TODO: to debug

    showLogs("show PrintWindow", Math.round(w), Math.round(h));
    printWindow.setBounds({
        x: Math.round(sw / 2 - w / 2),
        y: landscape ? Math.round(sh / 2 - h / 2) : 50,
        width: Math.round(w),
        height: Math.round(h),
    });
    printWindow.show();

    printWindow.webContents.send("Print_Set", content);
});
ipcMain.on("Print_Ready", (event) => {
    showLogs("main print", printOptions);

    printWindow.webContents.print(
        printOptions,
        (success, failureReason ) => {
            showLogs("main print" + success ? "success" : "fail", failureReason);
            if (success) {

            }
            printWindow.hide();
        }
    )
});
