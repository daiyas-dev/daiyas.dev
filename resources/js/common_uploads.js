var uploadFile = function(file, url, success, failed) {
    var fd = new FormData();
    fd.append("file", file);

    //実行中ダイアログ
    var progressDlg = $.dialogProgress({
        contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> アップロード中…",
    });

    axios.post(
        url,
        fd,
        {
            headers: {
                "Content-Type": "multipart/form-data",
                "X-CSRF-TOKEN": Laravel.csrfToken,
            }
        })
        .then(res => {
            progressDlg.dialog("close");

            if (res.onError || res.onException) {
                console.log(res);
                if (failed) {
                    failed(res);
                } else {
                    //ダイアログ
                    $.dialogErr({
                        title: "例外発生",
                        contents: "アップロードに失敗しました<br/" + res.message,
                    });
                    return;
                }
            }

            if (success) success(res);
        })
        .catch(error => {
            progressDlg.dialog("close");

            console.log(error);

            if (failed) {
                failed(error);
            } else {
                //ダイアログ
                $.dialogErr({
                    title: "例外発生",
                    contents: "アップロードに失敗しました<br/" + error.message,
                });
            }
        });
};

//jQueryのfunctionとして追加
$.extend({ "uploadFile": uploadFile });
