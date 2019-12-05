const os = require("os");
//const { app, autoUpdater, dialog } = require("electron");
const { app, dialog } = require("electron");
const autoUpdater = require("electron-updater").autoUpdater;
const version = app.getVersion();
const platform = os.platform() + "_" + os.arch();

let updateNotifyEnabled = true;
let isShownDialog = false;

function appUpdater(log) {
    autoUpdater.logger = log;

    autoUpdater.on("update-available", event => log.info("update-available"));
    autoUpdater.on("error", err => log.info("updater error", error));
    autoUpdater.on("checking-for-update", event => log.info("checking-for-update"));
    autoUpdater.on("update-not-available", () => log.info("update-not-available"));
    autoUpdater.on("update-downloaded", (event, releaseNotes, releaseName, releaseDate, updateURL) => {
        log.info("update-downloaded");

        let message = "最新バージョン:" + app.getName();
        if (releaseNotes) {
            const splitNotes = releaseNotes.split(/[^\r]\n/);
            message += "\n\nリリース内容:\n";
            splitNotes.forEach(notes => {
                message += notes + "\n\n";
            });
        }

        log.info("notyfy dialog", updateNotifyEnabled, isShownDialog);
        if (updateNotifyEnabled && !isShownDialog) {
            isShownDialog = true;
            dialog.showMessageBox({
                type: "question",
                buttons: ["再起動", "あとで"],
                defaultId: 0,
                message: "新しいバージョンをダウンロードしました。再起動しますか？",
                detail: message
            }, response => {
                if (response === 0) {
                    setTimeout(() => autoUpdater.quitAndInstall(), 1);
                } else {
                    updateNotifyEnabled = false;
                }
            });
        }
    });

    return autoUpdater;
}
exports = module.exports = {
    appUpdater
};
