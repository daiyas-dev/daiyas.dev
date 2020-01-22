console.log("electron preload.js");

window._process = process;
window.__devtron = { require: require, process: process };

const { ipcRenderer } = require("electron");
window.ipcRenderer = ipcRenderer;

ipcRenderer.on("log", (e, arg) => console.log(arg));

ipcRenderer.on("printToPDF", (e, arg) => {
    var pdf = new Blob([arg], { type: "application/pdf" });
    console.log("printToPDF Blob", pdf);
    window.$.showPdfViewerByContents(pdf);
});

ipcRenderer.print = (target, options) => {
    console.log("ipcRenderer print", target, options);

    var content = target.contentDocument.documentElement.outerHTML;

    ipcRenderer.send("Print_Req", content, options);
};
