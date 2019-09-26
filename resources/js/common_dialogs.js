//jQuery Dialogのバグ対応及び拡張
var dialogCustom = function(options) {
    //ログインダイアログを表示する場合は、個別ダイアログを表示しない
    if ($("#loginDialog").is(":visible") || window.VueApp.$refs.LogonForm.isShown) {
        return;
    }

    var defOpt = {
        autoOpen: true,
        title: "",
        contents: null,
        dialogClass: "jqDialog",
        closeOnEscape: false,
        modal: true,
        resizable: false,
        width: "auto",
        minWidth: 300,
        iconClass: null,
        reuse: false,
        //TODO: edge/IEのレンダリングエンジンが弱すぎるので
        //TODO: ダイアログ内のPQGridの再描画が間に合わないのでエフェクト停止
        show: { effect: "clip", duration: 150},
        hide: { effect: "clip", duration: 150},
        create: function() {
            var op = $(this).dialog("option");

            //contents
            if (op.contents) {
                $(this).html(op.contents);
            }

            //icon
            if (op.iconClass) {
                $(this).siblings("div.ui-dialog-titlebar").html("<i class='" + op.iconClass + "'></i>" + "&nbsp" + op.title).css("font-size", "20px");
            }
            else {
                var title_icon = null;

                var awesome = opt.kind == "info" ? "fa-info-circle"
                            : opt.kind == "confirm" ? "fa-question-circle"
                            : opt.kind == "err" ? "fa-exclamation-triangle"
                            : null;

                if (awesome) {
                    title_icon = "<i class='fa " + awesome + "'></i>";
                }

                if (title_icon) {
                    $(this).siblings("div.ui-dialog-titlebar").html(title_icon + "&nbsp" + op.title).css("font-size", "18px");
                }
            }

            if (op.minWidth) $(this).css("minWidth", op.minWidth + "px");
            if (op.maxWidth) $(this).css("minWidth", op.maxWidth + "px");
        },
        close: function() {
            var op = $(this).dialog("option");
            if (op.reuse != true) {
                $(this).dialog("destroy").remove();
            }
        },
        buttons:[
            {
                text: "閉じる",
                class: "btn btn-danger",
                click: function(){
                    $(this).dialog("close");
                }
            },
        ],
    };

    var opt = $.extend(true, defOpt, options);
    opt.buttons = (options && options.buttons) ? options.buttons : opt.buttons;

    //errObj
    if (options && options.errObj) {
        if (options.errObj.onException) {
            opt.title += "例外発生";
            opt.contents = opt.contents || "";
            opt.contents += options.errObj.statusText;

            //stacktraceを含むため、consoleに出力
            console.log(options.errObj.errors);
        } else {
            opt.title += options.errObj.message;
            opt.contents = opt.contents || "";
            opt.contents += _.uniq(Object.values(options.errObj.errors).flat().filter(v => v))
                .map(v => v.replace(/\"/g, "&quot;").replace(/\'/g, "&#39;"))
                .join("<br/>")
                .replace(/\r\n/g, "<br>").replace(/(^\"|\"$)/g, "");
        }
    }

    //call jQuery dialog
    var target = typeof this == "object" ? $(this) : $("<div id='jqDialog'>");
    return target.dialog(opt);

};

//jQueryのfunctionとして追加
$.extend({ "dialogCustom": dialogCustom });
$.fn.extend({ "dialogCustom": dialogCustom });

//情報通知用
var dialogInfo = function(options) {
    options = $.extend(true, {}, options);
    options.kind = "info";
    return this.dialogCustom(options);
};

//jQueryのfunctionとして追加
$.extend({ "dialogInfo": dialogInfo });
$.fn.extend({ "dialogInfo": dialogInfo });

//エラー用
var dialogErr = function(options) {
    options = $.extend(true, {}, options);
    options.kind = "err";
    options.buttons = [
        {
            text: "閉じる",
            class: "btn btn-danger",
            click: function(){
                $(this).dialog("close");

                //ログオン表示指定がある場合、表示通知
                if (options.errObj && options.errObj.goLogon) {
                    window.VueApp.$root.$emit("showLogOn");
                }

                //callbackがあれば実行
                if (options.closeFunc) {
                    options.closeFunc(this);
                }
            }
        },
    ];

    //ログオンメッセージ重複表示抑止
    var logOnMsg = "ログオンしてください。";
    if (options.contents == logOnMsg && $(".ui-dialog div:contains(" + logOnMsg + ")").length > 0) {
        return;
    }

    return this.dialogCustom(options);
};

//jQueryのfunctionとして追加
$.extend({ "dialogErr": dialogErr });
$.fn.extend({ "dialogErr": dialogErr });

//確認用
var dialogConfirm = function(options) {
    options = $.extend(true, {}, options);
    options.kind = "confirm";
    return this.dialogCustom(options);
};

//jQueryのfunctionとして追加
$.extend({ "dialogConfirm": dialogConfirm });
$.fn.extend({ "dialogConfirm": dialogConfirm });

//実行中用
var dialogProgress = function(options) {
    options = $.extend(true, {}, options);
    options.kind = "progress";
    options.dialogClass = "progress-dialog";
    options.create = function() {
        $(this).html($(this).dialog("option").contents);
        $(this).siblings(".ui-dialog-titlebar").hide();
    };
    options.minWidth = 500;
    options.minHeight = 100;
    options.buttons = [];

    return this.dialogCustom(options);
};

//jQueryのfunctionとして追加
$.extend({ "dialogProgress": dialogProgress });
$.fn.extend({ "dialogProgress": dialogProgress });

//子画面表示用
var dialogChild = function(options) {
    options = $.extend(true, {}, options);
    options.kind = "child";

    var funcResize = function(event, ui) {
        $(event.target).css("width", "auto");
        $(event.target).css("overflow-x", "hidden");
        $(event.target).css("overflow-y", "hidden");
    }

    options.open = function(event, ui) {
        funcResize(event, ui);
    };
    options.resize = function(event, ui) {
        funcResize(event, ui);
    };
    options.resizeStop = function(event, ui) {
        funcResize(event, ui);
    };
    return this.dialogCustom(options);
};

//jQueryのfunctionとして追加
$.extend({ "dialogChild": dialogChild });
$.fn.extend({ "dialogChild": dialogChild });
