//binary download(etc .xls for chrome/edge/ie10+)
var downloadFromUrl = function(url, params, filename, callback) {
    var downloading = $.dialogProgress({
        contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i>" + filename + "のダウンロード中...",
    });

    axios.post(url, params, { responseType: "blob" })
        .then(response => {
            downloading.dialog("close");

            //エラー及び例外
            if (!!response.data.type && response.data.type.includes("application/json")) {

                var fr = new FileReader();

                fr.onloadend = function(event) {
                    var res = JSON.parse(fr.result);

                    //失敗ダイアログ
                    $.dialogErr({
                        title: "ダウンロード失敗",
                        contents: "ダウンロードに失敗しました" + "<br/>" + res.message,
                    });
                    console.log("ダウンロード失敗:" + url);
                    console.log(res);
                }
                fr.readAsText(response.data);
                return;
            }

            //ダウンロード
            var blob = new Blob([response.data]);
            if (navigator && navigator.msSaveOrOpenBlob) {
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                var dlUrl = URL.createObjectURL(blob);
                var dlLink = $("<a>").attr("href", dlUrl).attr("download", filename);
                dlLink[0].click();
            }

            //execute callback
            if (callback) callback(response);
        })
        .catch(error => {
            downloading.dialog("close");

            //失敗ダイアログ
            $.dialogErr({
                title: "ダウンロードエラー",
                contents: error,
            });
        });
};

//jQueryのfunctionとして追加
$.extend({ "downloadFromUrl": downloadFromUrl });

var downloadContents = function(contents, filename, callback) {
    var downloading = $.dialogProgress({
        contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i>" + filename + "のダウンロード中...",
    });

    //ダウンロード
    var blob = new Blob([contents]);
    if (navigator && navigator.msSaveOrOpenBlob) {
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        var dlUrl = URL.createObjectURL(blob);
        var dlLink = $("<a>").attr("href", dlUrl).attr("download", filename);
        dlLink[0].click();
    }

    //execute callback
    if (callback) callback();

    downloading.dialog("close");
};

//jQueryのfunctionとして追加
$.extend({ "downloadContents": downloadContents });

