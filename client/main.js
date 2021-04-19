const debug = false;

const { app, BrowserWindow, screen } = require("electron");
const path = require("path");
const ipcMain = require("electron").ipcMain;
const log = require("electron-log");
const { v4: uuidv4 } = require('uuid');

//AutoUpdater
const { appUpdater } = require("./autoUpdater.js");
const version = app.getVersion();

const appDir = app.getAppPath().replace("\\resources\\app.asar", "");

let mainWindow;

//CTI Config
let ctiSocket;
let autoUpdater;
const HOST = "localhost";
const PORT_R = 2002;
const PORT_S = 2010;

function showLogs(...targets) {
    log.debug(targets);
    console.log("log", targets);
    if (!!mainWindow) {
        mainWindow.webContents.send("log", targets);
    }
}

function createWindow() {
    log.transports.file.level = "debug";
    log.transports.file.file = appDir + '\\log.log';

    mainWindow = new BrowserWindow({
        title: "ダイヤスクライアント" + " Ver." + version,
        icon: __dirname + "/daiyas48.ico",
        width: 1250,
        height: 850,
        center: true,
        //fullscreen: true,
        webPreferences: {
            preload: path.join(__dirname, "preload.js"),
            nodeIntegration: false,
        }
    });

    //AutoUpdater
    autoUpdater = appUpdater(mainWindow, log);
    mainWindow.webContents.send("log", "autoUpdater", autoUpdater);
    var updateInterval = setInterval(() => autoUpdater.checkForUpdatesAndNotify(), 1000 * 60 * 1);
    autoUpdater.checkForUpdatesAndNotify();

    if (!debug) mainWindow.setMenu(null);
    if (debug) mainWindow.webContents.openDevTools();

    mainWindow.loadURL("https://daiyas.dev/");
    // mainWindow.loadURL("http://192.168.1.109/");
    //mainWindow.loadURL("http://192.168.10.220/");

    mainWindow.webContents.on("did-finish-load", () => {
        //CTIService起動
        require("child_process").exec('tasklist /FI "IMAGENAME eq CTIService.exe"', (error, stdout, stderr) => {
            log.debug("tasklist", stdout, stdout.includes("CtiService"), stdout.includes("CTIService"));
            if (!stdout.includes("CTIService") && !stdout.includes("CtiService")) {
                var ctiDir = app.getAppPath().replace("\\app.asar", "") + "\\cti";
                var ctiExe = ctiDir + "\\CTIService.exe";
                showLogs("cti service path", ctiExe);

                var p = require("child_process").spawn(
                    ctiExe,
                    {
                        cwd: ctiDir,
                        detached: true,
                        stdio: ["ignore", "ignore", "ignore", "ipc"]
                    }
                );
                p.unref();
                showLogs("cti service process", p);
            }
        });
    });

    mainWindow.on("closed", function () {
        if (!!printManager.queue.length) printManager.terminate();
        mainWindow = null
    });

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

    // 印刷管理オブジェクト初期化
    printManager.init();

    // 印刷管理オブジェクト監視
    printManager.observe();
};

function createPrintWindow(key, options) {
    //create PrintWindow
    pw = new BrowserWindow({
        title: "印刷プレビュー",
        icon: __dirname + "/daiyas48.ico",
        show: !(options.silent == true),
        center: false,
        parent: options.silent == true ? null : mainWindow,
        modal: !(options.silent == true),
        resizable: false,
        maximizable: false,
        minimizable: true,
        closable: options.silent == true,
        webPreferences: {
            nodeIntegration: true,
        }
    });

    var printStyle = options.style.match(/@media print .+\}/);

    if (!!printStyle.length) {
        options.landscape = printStyle[0].includes("landscape");
        options.color = printStyle[0].includes("color");
    }

    options.printBackground = true;

    var mainBounds = mainWindow.getBounds();
    var mw = mainBounds.width;
    var mh = mainBounds.height;

    var sw = screen.getPrimaryDisplay().workAreaSize.width;
    var sh = screen.getPrimaryDisplay().workAreaSize.height;

    var w, h;
    if (options.landscape) {
        // w = sw * 0.7;
        // h = w * 5 / 7;
        w = 1050;
        h = 800;
    } else {
        // w = sw * 0.6;
        // h = sh * 0.8;
        w = 700;
        h = 800;
    }

    var sw = screen.getPrimaryDisplay().workAreaSize.width;
    var sh = screen.getPrimaryDisplay().workAreaSize.height;
    pw.setBounds({
        x: Math.round(sw / 2 - w / 2),
        y: options.landscape ? Math.round(sh / 2 - h / 2) : 50,
        width: Math.round(w),
        height: Math.round(h),
    });

    if (!debug) pw.setMenu(null);
    if (debug) pw.webContents.openDevTools();    //TODO: to debug

    printWindows[key] = pw;
    return pw
}

let printWindows = {};

// 印刷管理オブジェクト
let printManager = {
    running: false,
    target: null,
    timer: null,
    queue: [],
    init: () => {
        printManager.running = false;
        printManager.queue = [];
        if (printManager.timer) clearInterval(printManager.timer);
    },
    observe: () => {
        // queue監視
        printManager.timer = setInterval(printManager.execute, 1000);
    },
    terminate: () => {
        if (printManager.timer) clearInterval(printManager.timer);
    },
    push: (contents, options) => {
        var item = {
            contents: contents,
            options: options || {},
        };
        printManager.queue.push(item);

        showLogs("printManager push:" + printManager.queue.length);
        mainWindow.webContents.send("PrintMessageFromMain", "accept/印刷受付" + " [" + printManager.calcPages(item) + "]");
        if (printManager.running) {
            setTimeout(
                () => {
                    console.log("message updaate");
                    printManager.sendRemainInfo();
                }
                , 3000
            );
        }
    },
    calcBytes: (target) => {
        return (target ? [target] : (printManager.queue.concat(printManager.target || { contents: "" })))
            .map(q => encodeURIComponent(q.contents).replace(/%../g, "x").length)
            .reduce((acc, cur) => acc + cur, 0)
            ;
    },
    remainBytes: (target) => {
        var bytes = printManager.calcBytes(target);

        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + sizes[i];
    },
    calcPages: (target) => {
        var pages = (target ? [target] : (printManager.queue.concat(printManager.target || { contents: "" })))
            .map(q => (q.contents.match(/page_div/g) || []).length)
            .reduce((acc, cur) => acc + cur, 0)
            ;
        return pages + "ページ";
    },
    getRemainInfo: () => {
        return "[残:" + printManager.calcPages() + "]";
    },
    sendRemainInfo: () => {
        mainWindow.webContents.send("PrintMessageFromMain", "running/送信中" + printManager.getRemainInfo());
    },
    execute: () => {
        // showLogs("printManager execute: running=" + printManager.running + " / queue=" + printManager.queue.length);
        if (printManager.queue.length == 0) return;
        if (printManager.running) return;

        var pw = null;
        console.time("execute");
        try {
            printManager.running = true;

            printManager.target = printManager.queue.shift();

            // 印刷識別キー
            var printKey = uuidv4();

            // 印刷用画面生成
            pw = createPrintWindow(printKey, printManager.target.options);

            // print.htmlのロード
            pw.loadURL("file://" + __dirname + "/print.html");

            // ロード完了callback
            pw.webContents.on("did-finish-load", () => {
                console.time("load_Contents");
                pw.webContents.send("Key_Set", printKey);
                pw.webContents.send("Print_Set", printManager.target.contents);
            });
        } catch (error) {
            if (pw) {
                pw.closable = true;
                pw.close();
                delete printWindows[printKey];
            }
            // 送信失敗メッセージ送信
            showLogs("print exception", error);
            console.timeEnd("execute");
            mainWindow.webContents.send("PrintMessageFromMain", "error/送信失敗[" + error + "]");

            printManager.running = false;
        }
    },
    print: printKey => {
        console.timeEnd("load_Contents");

        var pw = printWindows[printKey];

        printManager.sendRemainInfo();

        console.time("print_Contents");
        pw.webContents.print(
            printManager.target.options,
            (data, error) => {
                console.timeEnd("print_Contents");
                if (!!error) {
                    // 送信失敗メッセージ送信
                    showLogs("print failed", error);
                    mainWindow.webContents.send(
                        "PrintMessageFromMain",
                        error == "cancelled" ? "success/送信キャンセル" : ("error/送信失敗[" + error + "]")
                    );
                } else {
                    // 送信完了メッセージ送信
                    showLogs("print succeeded", data);
                    if (printManager.queue.length == 0) {
                        if (!!mainWindow) mainWindow.webContents.send("PrintMessageFromMain", "success/送信完了");
                        if (!mainWindow) {
                            printManager.terminate();
                            if (process.platform !== "darwin") app.quit()
                        }
                    } else {
                        printManager.target = null;
                        if (!!mainWindow) printManager.sendRemainInfo();
                    }
                }

                pw.closable = true;
                pw.close();
                delete printWindows[printKey];
                console.timeEnd("execute");

                printManager.running = false;
            }
        );
    },
};

app.on("ready", createWindow);

app.on("window-all-closed", function () {
    if (process.platform !== "darwin") app.quit()
});

app.on("activate", function () {
    if (mainWindow === null) createWindow();
});

//ipcMain handlers
ipcMain.handle("command", (e, command) => {
    try {
        var ret = eval(command);
        showLogs("command success", command, ret);
    } catch (err) {
        showLogs("command error", command, err);
    }
    var ret = eval(command);
    return ret;
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
ipcMain.on("Print_Req", (event, contents, options) => {
    showLogs("main silent=" + options.silent);
    printManager.push(contents, options);
});
ipcMain.on("Print_Ready", (event, key) => {
    printManager.print(key);
});
