<template>
    <div class="CtiReceiver" v-show="isCalling || existsFax">
        <span v-show="isCalling" class="badge badge-danger">{{callInfo}}</span>
        <span v-show="existsFax" class="badge badge-danger">{{faxInfo}}</span>
    </div>
</template>

<style scoped>
.badge {
    font-size: 15px;
    font-weight: normal;
}
#vue-app > .CtiReceiver {
    z-index: 1000;
    position: fixed;
    bottom: 65px;
    right: 5px;
}
</style>

<script>
export default {
    name: "CtiReceiver",
    data() {
        return {
            isCalling: false,
            callInfo: null,
            existsFax: false,
            faxInfo: false,
        };
    },
    components: {
    },
    created: function () {
    },
    mounted: function () {
        var vue = this;

        vue.setCti();
    },
    methods: {
        setCti: function() {
            var vue = this;

            vue.$root.$on("OnCall", info => {
                console.log("message OnCall", info);

                var no = info.replace("TELCONNECT:", "");
                // vue.isCalling = true;
                // vue.callInfo = "通話中:" + no;

                vue.checkTelNo(no, moment().format("HH:mm:ss"));
            });

            if (window.ipcRenderer) {
                window.ipcRenderer.on("CTI_MessageFromMain", (e, arg) => {
                    var msg = new TextDecoder("utf-8").decode(arg);
                    var ret = msg + " : OK";

                    console.log("CTI_MessageFromMain", msg);
                    e.sender.send("CTI_MessageFromRender", ret);

                    setTimeout(
                        () => vue.$root.$emit("OnCall", msg),
                        10
                    );
                });
                ipcRenderer.on("CTI_Listening", (e, arg) => console.log("CTI_Listening"));
                ipcRenderer.on("CTI_BindError", (e, arg) => console.log("CTI_BindError", arg));
            }
        },
        checkTelNo: function(no, time) {
            var vue = this;

            if (!!no) {
                axios.post("/Utilities/CheckTelNo", { TelNo: no, noCache: true})
                    .then(res => {
                        if (!!res.data.Reject) {
                            //非顧客
                            console.log("非顧客登録済", no);
                        } else if (!!res.data.Unregist) {
                            //未登録
                            $.dialogConfirm({
                                title: "確認",
                                // contents: "本電話番号はどの顧客にも登録されておりません。<br>この電話番号はをお客様の連絡先に登録しますか？",
                                contents:
                                    $("<div>").addClass("d-block").addClass("text-center")
                                        .append(
                                            $("<div>").addClass("mb-1").addClass("justify-content-center")
                                                .append($("<label>").css("text-align", "center").text("電話番号"))
                                                .append($("<label>").text(no).width(100).css("text-align", "center").css("background-color", "antiquewhite").css("border", "solid 1px black"))
                                                .append($("<label>").css("text-align", "center").text("応答日時"))
                                                .append($("<label>").text(time).width(80).css("text-align", "center").css("background-color", "antiquewhite").css("border", "solid 1px black"))
                                        )
                                        .append(
                                            $("<div>")
                                                .append($("<label>").css("width", "unset").text("本電話番号はどの顧客にも登録されておりません。"))
                                        )
                                        .append(
                                            $("<div>")
                                                .append($("<label>").css("width", "unset").text("この電話番号はをお客様の連絡先に登録しますか？"))
                                        )
                                        .prop("outerHTML"),
                                buttons:[
                                    {
                                        text: "得意先選択",
                                        class: "btn btn-primary",
                                        click: function(){
                                            vue.showCustomerSelector(no, time, true);
                                            $(this).dialog("close");
                                        }
                                    },
                                    {
                                        text: "新規登録",
                                        class: "btn btn-primary",
                                        click: function(){
                                            //TODO: 4041得意先マスタメンテ詳細表示
                                            //TODO: 1030注文入力表示
                                            console.log("得意先マスタメンテ & 注文入力表示", no);
                                            $(this).dialog("close");
                                        }
                                    },
                                    {
                                        text: "非顧客登録",
                                        class: "btn btn-danger",
                                        click: function(){
                                            //TODO: 非顧客電話番号マスタに登録
                                            console.log("非顧客登録", no);
                                            $(this).dialog("close");
                                        }
                                    },
                                    {
                                        text: "キャンセル",
                                        class: "btn btn-primary",
                                        click: function(){
                                            $(this).dialog("close");
                                        }
                                    },
                                ],
                            });
                        } else if (!!res.data.Unique) {
                            //1件該当
                            //TODO: 1030注文入力表示
                            console.log("注文入力表示", res.data.CustomerCd);
                        } else {
                            //複数該当
                            vue.showCustomerSelector(no, time, false, true);
                        }
                    })
                    .catch(err => {
                        console.log(err);
                        $.dialogErr({
                            title: "電話番号検索失敗",
                            contents: "電話番号検索に失敗しました" + "<br/>" + err.message,
                        });
                    });
            } else {
                vue.showCustomerSelector(no, time, false);
            }
        },
        showCustomerSelector: function(no, time, regist, multi) {
            var vue = this;

            PageDialog.showSelector({
                dataUrl: "/Utilities/SearchCustomerListPartial",
                params: { TelNo: !!regist ? null : no, Start: 1, Chunk: 100 },
                title: !!multi ? "得意先選択(電話番号が複数得意先で登録されています)" : "得意先一覧",
                labelCd: "得意先CD",
                labelCdNm: "得意先名",
                isModal: true,
                showColumns: [
                    { title: "部署名", dataIndx: "部署名", dataType: "string", width: 150, maxWidth: 150, minWidth: 150, colIndx: 0, },
                    { title: "コースCD", dataIndx: "コースＣＤ", dataType: "string", align: "left", width: 100, maxWidth: 100, minWidth: 100, },
                    { title: "コース名", dataIndx: "コース名", dataType: "string", width: 200, maxWidth: 200, minWidth: 200, },
                ],
                width: 1000,
                height: 700,
                reuse: false,
                showBushoSelector: true,
                customElement: $("<div>").addClass("col-md-8").addClass("justify-content-end")
                    .append($("<label>").text("電話番号"))
                    .append($("<label>").text(!!no ? no : "非通知").width(100).css("text-align", "center").css("background-color", "antiquewhite").css("border", "solid 1px black"))
                    .append($("<label>").text("応答日時"))
                    .append($("<label>").text(time).width(80).css("text-align", "center").css("background-color", "antiquewhite").css("border", "solid 1px black"))
                    .prop("outerHTML"),
                hasClose: true,
                buttons: [
                    {
                        text: "選択",
                        class: "btn btn-primary",
                        shortcut: "Enter",
                        click: function(gridVue, grid) {
                            var rowIndx = grid.SelectRow().getFirst();
                            if (rowIndx == undefined) return false;

                            var rowData = grid.getRowData({ rowIndx: rowIndx });

                            if (!!no) {
                                if (!!regist) {
                                    $.dialogConfirm({
                                        title: "確認",
                                        contents: "この番号をお客様の連絡先に登録しますか？",
                                        buttons:[
                                            {
                                                text: "はい",
                                                class: "btn btn-primary",
                                                click: function(){
                                                    $(this).dialog("close");
                                                    //TODO: TelToCust及び未登録の場合は得意先マスタ電話番号に登録
                                                    //TODO: 1030注文入力表示
                                                    console.log("電話番号登録 & 注文入力表示", rowData.得意先ＣＤ)
                                                }
                                            },
                                            {
                                                text: "いいえ",
                                                class: "btn btn-danger",
                                                click: function(){
                                                    $(this).dialog("close");
                                                    //TODO: 1030注文入力表示
                                                    console.log("注文入力表示", rowData.得意先ＣＤ)
                                                }
                                            },
                                        ],
                                    });
                                } else {
                                    //TODO: 1030注文入力表示
                                    console.log("注文入力表示", rowData.得意先ＣＤ)
                                }
                            } else {
                                //TODO: 1030注文入力表示
                                 console.log("注文入力表示",rowData.得意先ＣＤ)
                           }

                            return false;
                        },
                    },
                    {
                        text: "新規登録",
                        class: "btn btn-primary",
                        shortcut: "ESC",
                        click: function(gridVue, grid) {
                            console.log("得意先新規登録", no);

                            $.dialogConfirm({
                                title: "確認",
                                contents: "新規客情報を登録しますか？",
                                buttons:[
                                    {
                                        text: "はい",
                                        class: "btn btn-primary",
                                        click: function(){
                                            $(this).dialog("close");
                                            //TODO: 4041得意先マスタメンテ詳細表示
                                            //TODO: 1030注文入力表示
                                             console.log("得意先マスタメンテ & 注文入力表示", no)
                                        }
                                    },
                                    {
                                        text: "いいえ",
                                        class: "btn btn-danger",
                                        click: function(){
                                            $(this).dialog("close");
                                        }
                                    },
                                ],
                            });

                            return false;
                        },
                    },
                ],
            });
        },
    }
};
</script>
