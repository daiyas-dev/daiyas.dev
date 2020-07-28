//jQuery Dialogのバグ対応及び拡張
var dialogCustom = function(options) {
    //ログインダイアログを表示する場合は、個別ダイアログを表示しない
    if ($("#loginDialog").is(":visible") || (window.VueApp && window.VueApp.$refs.LogonForm.isShown)) {
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
        show: { effect: "clip", duration: 300},
        hide: { effect: "clip", duration: 200 },
        create: function () {
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
                shortcut: "ESC",
                click: function(){
                    $(this).dialog("close");
                }
            },
        ],
    };

    var opt = $.extend(true, defOpt, options);
    opt.buttons = (options && options.buttons) ? options.buttons : opt.buttons;
    if (!!options.opened) {
        opt.open = options.opened;
    }

    //errObj
    if (options && options.errObj) {
        if (options.errObj.onException) {
            opt.title += "例外発生";
            opt.contents = opt.contents || "";
            opt.contents += options.errObj.statusText;
        } else {
            opt.title += options.errObj.message;
            opt.contents = opt.contents || "";
            if (options.errObj.errors) {
                opt.contents += _.uniq(Object.values(options.errObj.errors).flat().filter(v => v))
                    .map(v => v.replace(/\"/g, "&quot;").replace(/\'/g, "&#39;"))
                    .join("<br/>")
                    .replace(/\r\n/g, "<br>").replace(/(^\"|\"$)/g, "");
            }
        }

        //stacktraceを含むため、consoleに出力
        console.log(options.errObj.errors || options.errObj.trace);
    }

    //画面サイズ対応
    var ratio = document.body.style.getPropertyValue("--ratio") * 1;

    if (ratio != 1 && opt.width != "auto") {
        var dw = (window.innerWidth / ratio - opt.width) / 2;
        var dh = (window.innerHeight / ratio - opt.height) / 2;

        opt.position = {
            at: "left+" + dw + " " + "top+" + dh,
            my: "left top",
        };
        console.log("dialog position", opt.position);
    }

    //call jQuery dialog
    var target = typeof this == "object" ? $(this) : $("<div id='jqDialog'>");
    var dlg = target.dialog(opt);

    dlg.closest(".ui-dialog").on("keydown", function (evt) {
        if (opt.keyDownHandler) {
            return opt.keyDownHandler(target, opt, evt);
        } else {
            var sc = $(evt.currentTarget)
                .find(".ui-dialog-buttonpane button[shortcut]:visible:enabled")
                .filter((i, v) => {
                    var shortcut = $(v).attr("shortcut");

                    var keys = shortcut.split(/ *, */);
                    var match = _.some(keys, key => {
                        var op = key
                            .split(/ *\+ */)
                            .filter(k => !["Shift", "Ctrl", "Alt"].includes(k))
                            .map(k => {
                                var conv = {
                                    "←": "ArrowLeft",
                                    "→": "ArrowRight",
                                    "↑": "ArrowUp",
                                    "↓": "ArrowDown",
                                };

                                return conv[k] || k;
                            });

                        var isShift = key.includes("Shift");
                        var isCtrl = key.includes("Ctrl");
                        var isAlt = key.includes("Alt");

                        var alias = evt.key == "Escape" ? ["Escape", "ESC"] : [evt.key];

                        var ret =
                            (
                                _.some(op, k => [evt.code, evt.key, evt.keyCode, evt.which].includes(k))
                                ||
                                _.some(op, k => [evt.code, evt.key, evt.keyCode, evt.which].includes(k.toLowerCase()))
                                ||
                                _.some(op, k => alias.includes(k))
                            )
                            && evt.shiftKey == isShift
                            && evt.ctrlKey == isCtrl
                            && evt.altKey == isAlt;

                        return ret;
                    });

                    return match;
                });

            if (sc.length) {
                sc.each((i, v) => $(v).click());
                return false;
            }

            return true;
        }
    });

    return dlg;
};

//jQueryのfunctionとして追加
$.extend({ "dialogCustom": dialogCustom });
$.fn.extend({ "dialogCustom": dialogCustom });

//情報通知用
var dialogInfo = function(options) {
    options = $.extend(true, {}, options);
    options.kind = "info";
    options.closeOnEscape = true;
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
            shortcut: "ESC",
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

    var yesbtn = options.buttons.find(v => v.text == "はい");
    if (!!yesbtn) yesbtn.shortcut = "Enter";
    var nobtn = options.buttons.find(v => v.text == "いいえ");
    if (!!nobtn) nobtn.shortcut = "ESC";

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
