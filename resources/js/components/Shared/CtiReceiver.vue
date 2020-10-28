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
            prevNo: null,
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

                if (!!no && /^([\d-]+|非通知)$/.test(no)) {
                    vue.checkTelNo(no, moment().format("HH:mm:ss"));
                }
            });

            if (window.ipcRenderer) {
                window.ipcRenderer.on("CTI_MessageFromMain", (e, arg) => {
                    var msg = new TextDecoder("utf-8").decode(arg);
                    var ret = "SAVE_END";   //msg + " : OK";

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
                                        }
                                    },
                                    {
                                        text: "新規登録",
                                        class: "btn btn-primary",
                                        click: function(){
                                            console.log("得意先マスタメンテ & 注文入力表示", no);
                                            //4041得意先マスタメンテ詳細表示(ここでTelToCust登録)
                                            //登録後1030注文入力表示
                                            vue.show04041(no);
                                        }
                                    },
                                    {
                                        text: "非顧客登録",
                                        class: "btn btn-warning",
                                        click: function(){
                                            var $dlg = $(this);

                                            //非顧客電話番号マスタに登録
                                            axios.post("/Utilities/SetNonCustomer", { TelNo: no, EditUser: window.loginInfo.uid , noCache: true})
                                                .then(res => {
                                                    console.log("非顧客登録", no);
                                                    $dlg.dialog("close");
                                                })
                                                .catch(err => {
                                                    console.log(err);
                                                    $.dialogErr({
                                                        title: "非顧客電話番号マスタ登録失敗",
                                                        contents: "非顧客電話番号マスタ登録に失敗しました" + "<br/>" + err.message,
                                                    });
                                                });
                                        }
                                    },
                                    {
                                        text: "閉じる",
                                        class: "btn btn-danger",
                                        click: function(){
                                            $(this).dialog("close");
                                        }
                                    },
                                ],
                            });
                        } else if (!!res.data.Unique) {
                            //1件該当
                            //1030注文入力表示: OK
                            vue.show01030(res.data.CustomerInfo);
                        } else {
                            //複数該当
                            vue.showCustomerSelector(no, time, false, true);

                            //CTI前回得意先一覧 表示用
                            vue.prevNo = no;
                            vue.$root.$refs.AppHeader.hasPrevList(true);
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
                params: { TelNo: !!regist ? null : no, Start: 1, Chunk: 1000 },
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
                                                    var $dlg = $(this);

                                                    //TelToCust及び未登録の場合は得意先マスタ電話番号に登録
                                                    axios.post("/Utilities/InsertTelNo", { TelNo: no, CustomerCd: rowData.得意先ＣＤ, EditUser: window.loginInfo.uid , noCache: true})
                                                        .then(res => {
                                                            //1030注文入力表示
                                                            vue.show01030(rowData);
                                                            $dlg.dialog("close");
                                                            $(gridVue.$el).closest(".ui-dialog-content").dialog("close");
                                                        })
                                                        .catch(err => {
                                                            console.log(err);
                                                            $.dialogErr({
                                                                title: "電話番号マスタ登録失敗",
                                                                contents: "電話番号マスタ登録に失敗しました" + "<br/>" + err.message,
                                                            });
                                                        });
                                                }
                                            },
                                            {
                                                text: "いいえ",
                                                class: "btn btn-danger",
                                                click: function(){
                                                    var $dlg = $(this);
                                                    $dlg.dialog("close");
                                                    //1030注文入力表示: OK
                                                    vue.show01030(rowData);
                                                    $(gridVue.$el).closest(".ui-dialog-content").dialog("close");
                                                }
                                            },
                                        ],
                                    });
                                } else {
                                    //1030注文入力表示: OK
                                    vue.show01030(rowData);
                                    return false;
                                }
                            } else {
                                //1030注文入力表示: OK
                                 vue.show01030(rowData);
                                 return true;
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
                                            //4041得意先マスタメンテ詳細表示(ここでTelToCust登録)
                                            //登録後1030注文入力表示
                                            vue.show04041(no);
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
        show01030: function(data) {
            var vue = this;

            var params = {
                BushoCd: data.部署CD,
                CustomerCd: data.得意先ＣＤ,
                CustomerNm: data.得意先名,
                IsChild: true,
                Parent: vue,
                IsCTI: true,
            };

            PageDialog.show({
                pgId: "DAI01030",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 1250,
                height: 775,
            });
        },
        show04041: function(no) {
            var vue = this;

            var params = {
                電話番号１: no,
                IsNew: true,
                IsChild: true,
                Parent: vue,
                IsCTI: true,
            };

            //DAI04041を子画面表示
            PageDialog.show({
                pgId: "DAI04041",
                params: params,
                isModal: true,
                isChild: true,
                width: 1200,
                height: 700,
            });
        },
        after04041: function(customerInfo) {
            var vue = this;
            vue.show04091(customerInfo);
            // vue.show01030(customerInfo);
        },
        show04091: function(data) {
            var vue = this;

            var params = {
                targets: [
                    {
                        部署ＣＤ: data.部署CD,
                    }
                ],
                IsChild: true,
                Parent: vue,
                IsCTI: true,
                CustomerInfo: data,
            };

            PageDialog.show({
                pgId: "DAI04091",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 1200,
                height: 750,
            });
        },
        after04091: function(customerInfo) {
            var vue = this;
            vue.show01030(customerInfo);
        },
        showPrevList: function() {
            var vue = this;

            if(!vue.prevNo) return;
            vue.checkTelNo(vue.prevNo, moment().format("HH:mm:ss"));
        }
    }
};
</script>
