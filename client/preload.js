console.log("electron preload.js");

window._process = process;
window.__devtron = { require: require, process: process };

const { ipcRenderer } = require("electron");
window.ipcRenderer = ipcRenderer;

window.onload = e => {
    console.log("renderer send to main");
}

ipcRenderer.on("log", (e, arg) => console.log(arg));
