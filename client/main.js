const { app, BrowserWindow } = require("electron");
const path = require("path");
const ipc = require('electron').ipcMain;

let mainWindow;

function createWindow () {
    mainWindow = new BrowserWindow({
        title: "クライアント プロトタイプ",
        icon: __dirname + "/favicon.ico",
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

    //mainWindow.setMenu(null);     //TODO: to debug
    mainWindow.webContents.openDevTools();    //TODO: to debug

    mainWindow.loadURL("https://daiyas.dev/");
    //mainWindow.loadURL(`file://${__dirname}/index.html`);

    //TODO: ipc communication test
    mainWindow.webContents.on('did-finish-load', () => mainWindow.webContents.send('count', 1));

    mainWindow.on("closed", function () {
        mainWindow = null
    });

    try {
        var serialPort, port, parser;
        serialPort = require("serialPort");
        var { Readline } = serialPort.parsers;
        port = new serialPort("COM10", {
            baudRate: 9600,
            dataBits: 8,
            parity: "none",
            stopBits: 1,
            flowControl: false,
        });
        parser = port.pipe(new Readline({ delimiter: '\n' }));

        parser.on("data", function (data) {
            console.log("Data:", data);
            if (!!data) {
                var op = data.startsWith("on") ? "onCall" : "offCall";
                var tel = data.replace(/(on|off)Call: */, "");
                mainWindow.webContents.send(op, tel);
            }
        });

    } catch (error) {
        console.log("serialport error");
        console.log(error);
    }

    try {
        var winax = require('winax');

        var interval = setInterval(function () {
            winax.peekAndDispatchMessages(); // allows ActiveX event to be dispatched
        }, 50);

        // node x64 <=> x64 dll, and also x86
        var application = new ActiveXObject('COMTest.Hello');
        console.log(application.GetHelloMessage());

        var connectionPoints = winax.getConnectionPoints(application);
        var connectionPoint = connectionPoints[0];

        setTimeout(function () {
            clearInterval(interval);
            var ret = application.GetHelloMessage();
            console.log(ret);
            winax.release(application);
            console.log('application is closed.');
        }, 1000 * 10);
    } catch (error) {
        console.log("winax error");
        console.log(error);
    }

    try {
        //ffi test
        var ffi = require('ffi');

        var user32 = new ffi.Library('user32', {
            'MessageBoxW': [
                'int32', ['int32', 'string', 'string', 'int32']
            ]
        });
        console.log("user32 load");

        var isOk = user32.MessageBoxW(
            0,
            TEXT('ああああ'),
            TEXT('いいい'),
            1
        );
        console.log("isOk:" + isOk);

        var dllSamplePath = "DllSample32.dll";
        var dllSample = ffi.Library(dllSamplePath, {
            'HelloWorld': ['string', []]
        });

        var result = dllSample.HelloWorld();
        console.log(result);

    } catch (error) {
        console.log("ffi error");
        console.log(error);
    }

    //shared objects
    global.shared = {
        app: app,
        ipc: ipc,
        mainWindow: mainWindow,
        serialPort: serialPort,
        port: port,
        parser: parser,
    };
};

app.on("ready", createWindow);

app.on("window-all-closed", function () {
    if (process.platform !== "darwin") app.quit()
});

app.on("activate", function () {
    if (mainWindow === null) createWindow();
});

//ipc handlers
ipc.on('get-app-path', e => e.sender.send('got-app-path', app.getAppPath()));
ipc.on('ping-from-renderer', (e, arg) => e.sender.send('pong-from-main', 'pong'));
ipc.on('count', (e, arg) => setTimeout(() => e.sender.send("count", arg + 1), 500));
