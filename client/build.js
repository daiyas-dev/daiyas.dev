
const builder = require("electron-builder");
const fs = require("fs");
const rimraf = require("rimraf");
const package = require("./package.json");

builder.build(
    {
        config: {
            "releaseInfo": {
                "releaseNotes": "1.0.0"
            },
            "win": {
                "target": {
                    "target": "nsis",
                    "arch": [
                        "x64",
                        "ia32"
                    ],
                },
                "icon": "./daiyas256.ico",
                "publish": [
                    {
                        "provider": "generic",
                        "url": "https://daiyas.dev/client/win/",
                        // "url": "http://192.168.10.220/client/win/",
                    },
                ],
            },
        },
    })
    .then(function (result) {
        //setup exe copy
        fs.copyFile(
            "./build/" + package.build.productName + " Setup " + package.version + ".exe",
            "../public/client/win/" + package.build.productName + " Setup " + package.version + ".exe",
            err => {
                if (err) {
                    console.log("exe copy failed", err);
                }
                else {
                    console.log("exe copy done");
                }
            }
        );
        //blockmap copy
        fs.copyFile(
            "./build/" + package.build.productName + " Setup " + package.version + ".exe.blockmap",
            "../public/client/win/" + package.build.productName + " Setup " + package.version + ".exe.blockmap",
            err => {
                if (err) {
                    console.log("blockmap copy failed", err);
                }
                else {
                    console.log("blockmap copy done");
                }
            }
        );
        //latest.yml copy
        fs.copyFile(
            "./build/latest.yml",
            "../public/client/win/latest.yml",
            err => {
                if (err) {
                    console.log("latest.yml copy failed", err);
                }
                else {
                    console.log("latest.yml copy done");
                }
            }
        );
        //setup exe renamed copy(for download)
        fs.copyFile(
            "./build/" + package.build.productName + " Setup " + package.version + ".exe",
            "../public/client/win/" + package.build.productName + "Setup.exe",
            err => {
                if (err) {
                    console.log("exe copy failed", err);
                }
                else {
                    console.log("exe copy done");
                    //delete unpacked files
                    rimraf("./build/win-unpacked", () => console.log("unpacked files deleted"));
                    rimraf("./build/win-ia32-unpacked", () => console.log("unpacked files deleted"));
                }
            }
        );
    })
    .catch(function (e) {
        console.log("build error", e);
        throw e;
    });
