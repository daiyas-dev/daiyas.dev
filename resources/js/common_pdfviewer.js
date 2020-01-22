//pdfjsのviewerのwrapper
var showPdfViewer = function (url, params, filename, options, opened_callback, closed_callback) {
    //実行中ダイアログ
    var showPdfViewerDlg = $.dialogProgress({
        contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 帳票表示中…",
    });

    axios.post(url, params, { responseType: "blob" })
        .then(response => {
            showPdfViewerDlg.dialog("close");

            //エラー及び例外
            if (!!response.data.type && response.data.type.includes("application/json")) {

                var fr = new FileReader();

                fr.onloadend = function (event) {
                    var res = JSON.parse(fr.result);

                    //失敗ダイアログ
                    $.dialogErr({
                        title: "帳票作成失敗",
                        contents: "帳票の作成に失敗しました" + "<br/>" + res.message,
                    });
                    console.log("帳票作成失敗:" + pdfUrl);
                    console.log(res);
                }
                fr.readAsText(response.data);
                return;
            }

            //PDF => ObjectUrl
            var objectUrl = URL.createObjectURL(response.data);

            //表示用iframe
            var preview = $("<iframe>")
                .attr("src", "/js/pdfjs-2.1.266-dist/web/viewer.html" + "?file=" + objectUrl + "#zoom=page-width")
                .addClass("pdfviewer")
                .css("width", "100%")
                .css("height", "60vh")
                ;

            //dialog表示
            var previewDlg = $("<div>")
                .append(preview)
                .dialog({
                    autoOpen: true,
                    title: "帳票表示",
                    closeOnEscape: false,
                    modal: true,
                    resizable: true,
                    width: "95vw",
                    open: function () {
                        var vwin = preview[0].contentWindow;
                        $(vwin).on("load", () => {
                            new Promise((resolve, reject) => {
                                var eventBus = vwin.PDFViewerApplication.eventBus;
                                var timer = setInterval(function () {
                                    if (!!eventBus && !!eventBus._listeners && !!eventBus._listeners.pagesloaded) {
                                        clearInterval(timer);
                                        return resolve(eventBus._listeners.pagesloaded);
                                    }
                                }, 10);
                            })
                            .then(listener => {
                                listener.push(() => {
                                    console.log("application pagesloaded");
                                    if (!!options && options.isPrintImmediately) {
                                        $(vwin.document).find("button.toolbarButton.print").click();
                                    }

                                    if (opened_callback) opened_callback(response);
                                });

                                return;
                            });
                        });
                    },
                    close: function () {
                        $(this).dialog("destroy").remove();
                    },
                    buttons: [
                        {
                            text: "閉じる",
                            class: "btn btn-danger",
                            click: function () {
                                $(this).dialog("close");
                                if (closed_callback) closed_callback(response);
                            }
                        },
                    ],
                });

            //TODO: pdfjs direct view
            // var options = options || { scale: 1 };

            // //PDFJS.disableWorker = true;
            // PDFJS.getDocument(objectUrl).then(pages => {
            //     console.log("PDFJS render");
            //     console.log(pages);

            //     var printWin = window.open();
            //     $(printWin.document.body).html("<div class='print'></div>");
            //     var container = $(".print", printWin.document.body);

            //     _.range(pages.numPages).forEach(v => pages.getPage(v + 1).then(page => {
            //         var viewport = page.getViewport(options.scale);

            //         var wrapper = $("<div>").addClass("canvas-wrapper");
            //         var canvas = $("<canvas>").width(viewport.width).height(viewport.height);
            //         wrapper.append(canvas)
            //         container.append(wrapper);

            //         page.render({
            //             canvasContext: canvas[0].getContext("2d"),
            //             viewport: viewport
            //         });
            //     }));
            // });
        })
        .catch(error => {
            showPdfViewerDlg.dialog("close");

            //失敗ダイアログ
            $.dialogErr({
                title: "帳票表示失敗",
                contents: "帳票の表示に失敗しました" + "<br/>" + error.message,
            });
        });

    //PqGridの後続処理skip
    return false;
};

//jQueryのfunctionとして追加
$.extend({ "showPdfViewer": showPdfViewer });

//print.jsでの直接印刷(chrome & safari => Working, IE, Edge => Not Working)
// var printPdf = function (url, params, filename, options, opened_callback, closed_callback) {
//     //実行中ダイアログ
//     var printingDlg;

//     printJS({
//         printable: url + (params ? "?" + $.param(params) : ""),
//         showModal: false,
//         onLoadingStart: () => {
//             //実行中ダイアログ
//             printingDlg = $.dialogProgress({
//                 contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 印刷準備中…",
//             });
//         },
//         onLoadingEnd: () => {
//             printingDlg.dialog("close");
//         },
//         onError: error => {
//             printingDlg.dialog("close");

//             //失敗ダイアログ
//             $.dialogErr({
//                 title: "印刷準備失敗",
//                 contents: "印刷準備に失敗しました" + "<br/>" + error.message,
//             });
//         },
//     });
// };

// //jQueryのfunctionとして追加
// $.extend({ "printPdf": printPdf });

//複数PDF結合表示
var showPdfViewerWithMerge = function(reports, params) {

    //実行中ダイアログ
    var showPdfViewerDlg = $.dialogProgress({
        contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> PDF表示中…",
    });

    //reportsはURL内のrdlxファイル名を渡す(単一帳票ならリテラル、複数帳票ならArray)
    var targets = _.isArray(reports) ? reports : [reports];

    //PDF取得共通アクションメソッドURL
    var pdfUrl = "/Utilities/GetPdf?reports=" + targets.join("&reports=");

    axios.post(pdfUrl, params, { responseType: "blob" })
        .then(response => {

            //エラー及び例外
            if (!!response.data.type && response.data.type.includes("application/json")) {

                var fr = new FileReader();

                fr.onloadend = function(event) {
                    var res = JSON.parse(fr.result);

                    //失敗ダイアログ
                    $.dialogErr({
                        title: "帳票作成失敗",
                        contents: "帳票の作成に失敗しました" + "<br/>" + res.message,
                    });
                    console.log("帳票作成失敗:" + pdfUrl);
                    console.log(res);

                    showPdfViewerDlg.dialog("close");
                }
                fr.readAsText(response.data);
                return;
            }

            //PDF => ObjectUrl
            var objectUrl = URL.createObjectURL(response.data);

            //表示用iframe
            var preview = $("<iframe>")
                .attr("src", "/js/pdfjs-2.1.266-dist/web/viewer.html" + "?file=" + objectUrl + "#zoom=page-width")
                .css("width", "100%")
                .css("height", "60vh")
                ;

            //dialog表示
            $("<div>")
                .append(preview)
                .dialog({
                    autoOpen: true,
                    title: "帳票表示",
                    closeOnEscape: false,
                    modal: true,
                    resizable: true,
                    width: "90vw",
                    open: function() {
                        showPdfViewerDlg.dialog("close");

                        //完了ダイアログ
                        $.dialogInfo({
                            title: "帳票作成完了",
                            contents: "** 帳票を作成しました **",
                        });

                        console.log("showPdfViewerWithMerge completed");
                    },
                    close: function() {
                        $(this).dialog("destroy").remove();
                    },
                    buttons:[
                        {
                            text: "閉じる",
                            click: function(){
                                $(this).dialog("close");
                            }
                        },
                    ],
                });
        })
        .catch(error => {
            showPdfViewerDlg.dialog("close");

            //失敗ダイアログ
            $.dialogErr({
                title: "帳票表示失敗",
                contents: "帳票の表示に失敗しました" + "<br/>" + error.message,
            });

            console.log("showPdfViewerWithMerge failed");
            console.log(error);
        });

    //PqGridの後続処理skip
    return false;
};

//jQueryのfunctionとして追加
$.extend({ "showPdfViewerWithMerge": showPdfViewerWithMerge });

var showPdfViewerByContents = function(contents) {
    //実行中ダイアログ
    var showPdfViewerDlg = $.dialogProgress({
        contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> PDF表示中…",
    });

    //ObjectUrl
    var objectUrl = URL.createObjectURL(contents);

    //表示用iframe
    var preview = $("<iframe>")
        .attr("src", "/js/pdfjs-2.1.266-dist/web/viewer.html" + "?file=" + objectUrl + "#zoom=page-width")
        .css("width", "100%")
        .css("height", "60vh")
        ;

    //dialog表示
    $("<div>")
        .append(preview)
        .dialog({
            autoOpen: true,
                    title: "帳票表示",
            closeOnEscape: false,
            modal: true,
            resizable: true,
            width: "90vw",
            open: function() {
                showPdfViewerDlg.dialog("close");

                //完了ダイアログ
                $.dialogInfo({
                    title: "帳票作成完了",
                    contents: "** 帳票を作成しました **",
                });

                console.log("showPdfViewer completed");
            },
            close: function() {
                $(this).dialog("destroy").remove();
            },
            buttons:[
                {
                    text: "閉じる",
                    click: function () {
                        URL.revokeObjectURL(objectUrl);
                        $(this).dialog("close");
                    }
                },
            ],
        });

    //PqGridの後続処理skip
    return false;
};

//jQueryのfunctionとして追加
$.extend({ "showPdfViewerByContents": showPdfViewerByContents });

//画面のハードコピーをPDF化
import html2canvas from "html2canvas";
import jspdf from "jspdf";
// import printJS from "print-js";

var getScreenPdf = function(target) {

    console.log("getScreenPdf called");
    console.log(target);
    console.log(navigator.userAgent);

    //実行中ダイアログ
    var getScreenPdfDlg = $.dialogProgress({
        contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> PDF生成中…",
    });

    var ele = target instanceof jQuery ? target[0] : target;

    //TODO:scrollがある場合を考慮するか否か

    //指定されたelementをpdf化して利用
    html2canvas(ele,
        {
            async: false,
            backgroundColor: "white",
            ignoreElements: (element) => element.id == "footer-bar",
        }
    )
    .then(function(canvas) {

        //画像生成
        var imgData = canvas.toDataURL("image/jpeg", 1.0);

        //TODO:確認用
        //if (canvas.msToBlob) {
        //    $.downloadContents(canvas.msToBlob(), "xxx.jpg")
        //} else {
        //    canvas.toBlob(blob => $.downloadContents(blob, "xxx.jpg"), "image/jpeg", 1.0);
        //}

        //pdf生成(jspdf)
        var pdf = new jspdf("l", "pt", "a4");

        var pdfW = pdf.internal.pageSize.getWidth();
        var pdfH = pdf.internal.pageSize.getHeight();

        var wRate = canvas.width / pdfW;
        var hRate = canvas.height / pdfH;
        var rate = wRate > hRate ? wRate : hRate;

        pdf.addImage(imgData, "JPEG", 0, 0, pdfW * wRate / rate , pdfH * hRate / rate);

        //TODO: IEでの画面ハードコピーPDFのViewer表示停止/ダウンロード対応
        //IE11のPromise対応及びレンダラ不備(HWのドライバによるものとも…)
        // https://github.com/mozilla/pdf.js/issues/5031
        // https://twitter.com/adrianba/status/488820643492667393
        //IEのオプションでソフトウェアレンダリングを選択すれば解決との報告はあるが、全ての環境において有効ではない模様
        if (!!window.MSInputMethodContext && !!document.documentMode) {
            //ダウンロードファイル名に画面名を設定
            var scname = ($(ele).find("#screen-title") || $(ele).closest("form").find("#screen-title")).text();
            pdf.save(scname + ".pdf");

            getScreenPdfDlg.dialog("close");

            //完了ダイアログ
            $.dialogInfo({
                title: "帳票作成完了",
                contents: "** 帳票を作成しました **",
            });
            return;
        }

        var pdfuri = pdf.output("datauri");

        //base64 object変換(PDFJSのviewer.htmlがbase64に対応していないため)
        var raw = atob(pdfuri.replace(/^.+base64,/g, ""));
        var binary = [new Uint8Array(new ArrayBuffer(raw.length)).map((v, i) => raw.charCodeAt(i))];

        //PDF => ObjectUrl
        var objectUrl = URL.createObjectURL(new Blob(binary, { type: "application/pdf" }));

        //表示用iframe
        var preview = $("<iframe>")
            .attr("src", "/Scripts/pdfjs-1.10.100-dist/web/viewer.html" + "?file=" + objectUrl + "#zoom=page-width")
            .css("width", "100%")
            .css("height", "60vh")
            ;

        //dialog表示
        $("<div>")
            .append(preview)
            .dialog({
                autoOpen: true,
                title: "帳票表示",
                closeOnEscape: false,
                modal: true,
                resizable: true,
                width: "90vw",
                open: function() {
                    getScreenPdfDlg.dialog("close");

                    //完了ダイアログ
                    $.dialogInfo({
                        title: "帳票作成完了",
                        contents: "** 帳票を作成しました **",
                    });

                    console.log("getScreenPdf pdfjs completed");
                },
                close: function() {
                    $(this).dialog("destroy").remove();
                },
                buttons:[
                    {
                        text: "閉じる",
                        click: function(){
                            $(this).dialog("close");
                        }
                    },
                ],
            });
    })
    .catch(error => {
        getScreenPdfDlg.dialog("close");

        //失敗ダイアログ
        $.dialogErr({
            title: "帳票表示失敗",
            contents: "帳票の表示に失敗しました" + "<br/>" + error.message,
        });
        console.log("getScreenPdf html2canvas failed");
        console.log(error);
    });
};

//jQueryのfunctionとして追加
$.extend({ "getScreenPdf": getScreenPdf });

