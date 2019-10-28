console.log("electron preload.js");

window._process = process;
window.__devtron = { require: require, process: process };

const { ipcRenderer } = require("electron");
window.ipcRenderer = ipcRenderer;

window.onload = e => {
    console.log("renderer send to main");
    console.log("ping");
    ipcRenderer.send("ping-from-renderer", "ping");
}

ipcRenderer.on("log", (e, arg) => console.log(arg));

// ipcRenderer.on("count", (e, arg) => {
//     console.log("pong:" + arg);
//     if (arg > 10) return;
//     setTimeout(() => {
//         var c = arg + 1;
//         ipcRenderer.send("count", c);
//         console.log("ping:" + c);
//     }, 500);
// });

