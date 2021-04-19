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
    // console.log("ipcRenderer print", target, options);

    var content = (typeof target == "object") ? target.contentDocument.documentElement.outerHTML : target;
    console.log("preload silent=" + options.silent);
    if (typeof target == "object") {
        target.parentNode.removeChild(target);
        console.log("remove print frame");
    }

    ipcRenderer.send("Print_Req", content, options);
};
