const { app, BrowserWindow } = require("electron");
const path = require("path");
const ipc = require('electron').ipcMain;

let mainWindow;

//CTI Config
let ctiSocket;
const HOST = "localhost";
const PORT_R = 2002;
const PORT_S = 2010;

function createWindow () {
    mainWindow = new BrowserWindow({
        title: "ダイヤスクライアント",
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

    // try {
    //     var serialPort, port, parser;
    //     serialPort = require("serialPort");
    //     var { Readline } = serialPort.parsers;
    //     port = new serialPort("COM10", {
    //         baudRate: 9600,
    //         dataBits: 8,
    //         parity: "none",
    //         stopBits: 1,
    //         flowControl: false,
    //     });
    //     parser = port.pipe(new Readline({ delimiter: '\n' }));

    //     parser.on("data", function (data) {
    //         console.log("Data:", data);
    //         if (!!data) {
    //             var op = data.startsWith("on") ? "onCall" : "offCall";
    //             var tel = data.replace(/(on|off)Call: */, "");
    //             mainWindow.webContents.send(op, tel);
    //         }
    //     });

    // } catch (error) {
    //     console.log("serialport error");
    //     console.log(error);
    // }

    // try {
    //     var winax = require('winax');

    //     var interval = setInterval(function () {
    //         winax.peekAndDispatchMessages(); // allows ActiveX event to be dispatched
    //     }, 50);

    //     // node x64 <=> x64 dll, and also x86
    //     var application = new ActiveXObject('COMTest.Hello');
    //     console.log(application.GetHelloMessage());

    //     var connectionPoints = winax.getConnectionPoints(application);
    //     var connectionPoint = connectionPoints[0];

    //     setTimeout(function () {
    //         clearInterval(interval);
    //         var ret = application.GetHelloMessage();
    //         console.log(ret);
    //         winax.release(application);
    //         console.log('application is closed.');
    //     }, 1000 * 10);
    // } catch (error) {
    //     console.log("winax error");
    //     console.log(error);
    // }

    // try {
    //     //ffi test
    //     var ffi = require('ffi');

    //     var user32 = new ffi.Library('user32', {
    //         'MessageBoxW': [
    //             'int32', ['int32', 'string', 'string', 'int32']
    //         ]
    //     });
    //     console.log("user32 load");

    //     var isOk = user32.MessageBoxW(
    //         0,
    //         TEXT('ああああ'),
    //         TEXT('いいい'),
    //         1
    //     );
    //     console.log("isOk:" + isOk);

    //     var dllSamplePath = "DllSample32.dll";
    //     var dllSample = ffi.Library(dllSamplePath, {
    //         'HelloWorld': ['string', []]
    //     });

    //     var result = dllSample.HelloWorld();
    //     console.log(result);

    // } catch (error) {
    //     console.log("ffi error");
    //     console.log(error);
    // }

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
        window.ctiSocket = ctiSocket;

    } catch (error) {
        mainWindow.webContents.send("CTI_BindError", error);
        console.log("udp error", error);
    }


    //shared objects
    global.shared = {
        app: app,
        ipc: ipc,
        mainWindow: mainWindow,
        ctiSocket: ctiSocket,
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

ipc.on('CTI_MessageFromRender', (e, arg) => {
    console.log("CTI_MessageFromRender", arg);

    ctiSocket.send(arg, 0, arg.length, PORT_S, HOST, (err, bytes) => {
        if (err) {
            console.log("CTI_SendError", err);
            mainWindow.webContents.send("CTI_SendError", err);
        }
    });
});
