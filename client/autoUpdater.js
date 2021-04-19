const os = require("os");
const { app, dialog } = require("electron");
const autoUpdater = require("electron-updater").autoUpdater;

let updateNotifyEnabled = true;
let isShownDialog = false;

function appUpdater(mainWindow, log) {
    autoUpdater.logger = log;

    autoUpdater.on("update-available", event => log.info("update-available"));
    autoUpdater.on("error", err => log.info("updater error", error));
    autoUpdater.on("checking-for-update", event => log.info("checking-for-update"));
    autoUpdater.on("update-not-available", () => log.info("update-not-available"));
    autoUpdater.on("update-downloaded", (event, releaseNotes, releaseName, releaseDate, updateURL) => {
        log.info("update-downloaded", event);

        let message = "バージョン:" + event.version;
        if (releaseNotes) {
            const splitNotes = releaseNotes.split(/[^\r]\n/);
            message += "\n\nリリース内容:\n";
            splitNotes.forEach(notes => {
                message += notes + "\n\n";
            });
        }

        var response = dialog.showMessageBoxSync(
            mainWindow,
            {
                title: "ダイヤスクライアント",
                type: "question",
                buttons: ["今すぐ再起動", "あとで実行"],
                defaultId: 1,
                message: "新しいバージョンをダウンロードしました。\nクライアントを再起動しますか？",
                detail: message
            }
        );
        if (response === 0) {
            autoUpdater.quitAndInstall();
        }
    });

    return autoUpdater;
}
exports = module.exports = {
    appUpdater
};
