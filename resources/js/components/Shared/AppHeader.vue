<template>
    <header class="AppHeader mt-2 mb-2">
        <div class="row ml-2 mr-2">
            <div class="col-md-3">
                <span :class="['badge', this.isLogOn == true ? 'badge-success' : 'badge-danger']">{{title}}</span>
            </div>
            <div class="col-md-3 pr-2 ">
                <template v-if="isShowSilentPrint">
                    <VueCheck
                        id="VueCheck_SilentPrint"
                        ref="VueCheck_SilentPrint"
                        :vmodel=this
                        bind="silentPrint"
                        checkedCode="1"
                        customContainerStyle="border: none;"
                        :list="[
                            {code: '0', name: 'しない', label: '直接印刷'},
                            {code: '1', name: 'する', label: '直接印刷'},
                        ]"
                        :onChangedFunc=onSilentPrintChanged
                    />
                    <span :class="[
                        'font-weight-bold',
                        'ml-3',
                        this.printStatus == 'error' ? 'text-danger'
                            : (this.printStatus == 'accept' ? 'text-success' : 'text-primary')
                    ]">{{printMessage}}</span>
                </template>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-primary ctiPrevList" @click="showPrevList" disabled="false" v-if="showCtiPrevList">CTI再表示</button>
            </div>
            <div class="col-md-1"></div>
            <div class="col-md-2 justify-content-start">
                <button type="button" class="btn btn-primary webOrder" @click="showWebOrderList">
                    Web受注 <span class="badge badge-light webOrderCount">{{webOrderCount}}</span>
                </button>
            </div>
            <div class="col-md-2 justify-content-end">
                <span :class="['ml-1', 'badge', this.isLogOn == true ? 'badge-success' : 'badge-danger']">{{nowDate}}</span>
                <span :class="['ml-1', 'badge', this.isLogOn == true ? 'badge-success' : 'badge-danger']">{{isLogOn == true ? userNm : "未ログイン"}}</span>
            </div>
        </div>
    </header>
</template>

<style scoped>
.badge {
    font-size: 15px;
    font-weight: normal;
}
button.webOrder {
    font-size: 15px;
    line-height: 15px;
}
.webOrderCount {
    color: black;
    background-color: white;
}
.webOrderCount.exists {
    color: black;
    background-color: orange;
}

.webOrderCount.blinking {
    animation: blink-animation 1s infinite;
}

@-webkit-keyframes blink-animation {
    0%, 49% {
        color: black;
        background-color: rgb(117,209,63);
    }
    50%, 100% {
        color: white;
        background-color: #e50000;
    }
}
</style>

<script>
import VueCheck from "@vcs/VueCheck.vue";
import DatePickerWrapper from "@vcs/DatePickerWrapper.vue";

export default {
    name: "AppHeader",
    data() {
        return {
            title: null,
            userId: null,
            userNm: null,
            isLogOn: null,
            webOrderCount: 0,
            VueCheckComp: VueCheck,
            DatePickerWrapper: DatePickerWrapper,
            fetch: true,
            interval: null,
            showCtiPrevList: false,
            hasSilentPrint: false,
            silentPrint: "0",
            printStatus: "",
            printMessage: "",
            printMessageInterval: null,
        }
    },
    components: {
        "VueCheck": VueCheck,
    },
    computed: {
        nowDate: function () {
            return moment().format('YYYY/MM/DD', new Date());
        },
        isShowSilentPrint: function() {
            var vue = this;
            return !!vue.hasSilentPrint && !!window.ipcRenderer;
        },
    },
    created: function () {
    },
    mounted: function () {
        var vue = this;

        //logOn/logOff handler
        vue.$root.$on("logOn", vue.logOn);
        vue.$root.$on("logOff", vue.logOff);

        //title handler
        vue.$root.$on("setTitle", vue.setTitle);

        //ログイン済みの場合はその内容を表示
        var info = vue.$root.$refs.TopMenu.$data;
        vue.userId = info.userId;
        vue.userNm = info.userNm;
        vue.isLogOn = info.isLogOn;

        if (vue.isLogOn) {
            vue.startPolling();
        }

        if (!!window.ipcRenderer) {
            vue.showCtiPrevList = true;

            vue.$root.$on("showSilentPrint", vue.showSilentPrint);
            vue.$root.$on("setSilentPrint", vue.setSilentPrint);

            vue.$root.$on("PrintMessageFromMain", vue.setPrintMessage);
            window.ipcRenderer.on("PrintMessageFromMain", (e, arg) => vue.$root.$emit("PrintMessageFromMain", arg));
        }
    },
    methods: {
        logOn: function (info) {
            var vue = this;
            vue.userId = info.user.uid;
            vue.userNm = info.user.unm;
            vue.isLogOn = info.isLogOn;

            vue.startPolling();
        },
        logOff: function (info) {
            var vue = this;
            vue.userId = "";
            vue.userNm = "";
            vue.isLogOn = false;

            vue.stopPolling();
        },
        setTitle: function(title) {
            var vue = this;
            vue.title = title;
            vue.hasSilentPrint = false;
        },
        startPolling: function() {
            var vue = this;
            vue.fetch = false;

            if (!vue.interval) {
                vue.interval = setInterval(
                    () => {
                        if (!!vue.fetch) {
                            //ログ出力用にcaptureFlagを追加
                            axios.post("/Utilities/SearchWebOrderList", { UnRegisted: "1", Timeout: "0", noCache: true, captureFlag: false })
                                .then(res => {
                                    if (!res.data.length) return;

                                    var count = res.data[0].Count;
                                    vue.webOrderCount = count;

                                    var ele = $(vue.$el).find(".webOrderCount");

                                    if (count == 0) {
                                        ele.removeClass("exists");
                                    } else {
                                        ele.addClass("exists");
                                        ele.addClass("blinking")
                                                .delay(3000)
                                                .queue(next => {
                                                    ele.removeClass("blinking");
                                                    next();
                                                });
                                    }
                                })
                                .catch(err => {
                                    console.log("SearchWebOrderList Error", err);
                                })
                        } else {
                            clearInterval(vue.interval);
                        }
                    }
                    ,60000
                );
            }
        },
        stopPolling: function() {
            var vue = this;
            vue.fetch = false;
        },
        showWebOrderList: function() {
            var vue = this;

            PageDialog.showSelector({
                dataUrl: "/Utilities/SearchWebOrderList",
                //ログ出力用にcaptureFlagを追加
                params: { TargetDate: moment().format("YYYYMMDD"), UnRegisted: "1", Timeout: "0", Start: 1, Chunk: 100,captureFlag: false },
                title: "Web受注一覧",
                labelCd: "得意先CD",
                labelCdNm: "得意先名",
                isModal: true,
                showColumns: [
                    { title: "部署名", dataIndx: "部署名", dataType: "string", align: "center", width: 125, maxWidth: 125, minWidth: 125, colIndx: 0, },
                    { title: "配送日", dataIndx: "配送日", dataType: "date", align: "center", format: "yy/mm/dd", width: 100, maxWidth: 100, minWidth: 100, },
                    { title: "注文日時", dataIndx: "注文日時", dataType: "string", align: "center", width: 150, maxWidth: 150, minWidth: 150,
                      render: ui => {
                          return moment(ui.rowData.注文日時).format("YYYY/MM/DD HH:mm");
                      },
                    },
                    { title: "締切", dataIndx: "締切時刻", dataType: "string", align: "center", width: 100, maxWidth: 100, minWidth: 100,
                      render: ui => {
                          var ret = vue.calcDeadlline(ui.rowData.配送日, ui.rowData.締切時刻, ui);
                          return ret;
                      },
                    },
                    { title: "取込", dataIndx: "取込", dataType: "string", align: "center", width: 100, maxWidth: 100, minWidth: 100,
                      render: ui => {
                          return { text: ui.rowData.確認 == "0" ? "" : "取込済"  };
                      },
                    },
                ],
                width: 1100,
                height: 700,
                reuse: false,
                showBushoSelector: true,
                customParams: { TargetDate: moment().format("YYYYMMDD"), UnRegisted: "1",  Timeout: "0",},
                customElementFunc: (targetVue, container) => {
                    var dp = new (VueApp.createInstance(vue.DatePickerWrapper))(
                        {
                            propsData: {
                                id: "DatePicker_SearchWebOrderList",
                                ref: "DatePicker_SearchWebOrderList",
                                vmodel: targetVue.customElementParams,
                                bind: "TargetDate",
                                editable: true,
                                onChangedFunc: () => targetVue.conditionChanged(),
                            }
                        }
                    );
                    dp.$mount();

                    var vct = new (VueApp.createInstance(vue.VueCheckComp))(
                        {
                            propsData: {
                                id: "VueCheck_SearchWebOrderList_Timeout",
                                ref: "VueCheck_SearchWebOrderList_Timeout",
                                vmodel: targetVue.customElementParams,
                                bind: "Timeout",
                                checkedCode: "1",
                                customContainerStyle: "border: none;",
                                list: [
                                    {code: '0', name: '全て', label: '締切済のみ'},
                                    {code: '1', name: '表示', label: '締切済のみ'},
                                ],
                                onChangedFunc: () => targetVue.conditionChanged(),
                            }
                        }
                    );
                    vct.$mount();

                    var vc = new (VueApp.createInstance(vue.VueCheckComp))(
                        {
                            /* Web受注一覧へ取込済のレコードを常時表示により「取込済も表示」チェックボックスを非表示 */
                            /* propsData: {
                                id: "VueCheck_SearchWebOrderList",
                                ref: "VueCheck_SearchWebOrderList",
                                vmodel: targetVue.customElementParams,
                                bind: "UnRegisted",
                                checkedCode: "0",
                                customContainerStyle: "border: none;",
                                list: [
                                    {code: '0', name: '全て', label: '取込済も表示'},
                                    {code: '1', name: '表示', label: '取込済も表示'},
                                ],
                                onChangedFunc: () => targetVue.conditionChanged(),
                            } */
                        }
                    );
                    vc.$mount();

                    container.append(
                        $("<div>").addClass("col-md-4").addClass("justify-content-start")
                            .append($("<label>").text("対象日付"))
                            .append(dp.$el)
                    )
                    .append(
                        $("<div>").addClass("col-md-4").addClass("justify-content-end")
                            .append(vct.$el)
                            .append(vc.$el)
                    );
                },
                hasClose: true,
                buttons: [
                    {
                        text: "表示",
                        class: "btn btn-primary",
                        shortcut: "Enter",
                        click: function(gridVue, grid) {
                            var rowIndx = grid.SelectRow().getFirst();
                            if (rowIndx == undefined) return false;

                            var rowData = grid.getRowData({ rowIndx: rowIndx });

                            vue.show01032(rowData, grid);

                            return false;
                        },
                    },
                ],
            });
        },
        calcDeadlline: function(targetDate, deadLine, ui) {
            var vue = this;

            if (!deadLine) return "締切設定無し";

            var dl = moment(moment(targetDate).format("YYYY/MM/DD ") + deadLine);
            if (moment().isAfter(dl)) {
                return "締切済: " + dl.format("HH:mm");
            } else {
                var dt = dl.diff(moment(), "days");

                var dur = moment.duration(dl.diff(moment()), "milliseconds");
                var formatter = new Intl.NumberFormat('ja', { minimumIntegerDigits: 2 });
                var dh =  formatter.format(Math.floor(dur.asHours()));
                var dm =  formatter.format(dur.minutes());

                return "あと: " + (!!dt ? (dt + "日") : (dh + ":" + dm));
            }
        },
        show01032: function(data, grid) {
            var vue = this;

            axios.post(
                "/Utilities/SearchWebOrderList",
                //ログ出力用にcaptureFlagを追加
                { TargetDate: moment(data.配送日).format("YYYYMMDD"), Start: 1, Chunk: 1000, noCache: true, captureFlag: false},
            )
            .then(res => {
                if (!res.data.length) return;

                var target = res.data[0].Result.find(v => v.Web得意先ＣＤ == data.Web得意先ＣＤ);
                console.log("find 01032 target", target);

                if (!!target) {
                    var params = _.cloneDeep(target);
                    params.IsChild = true;
                    params.Parent = vue;
                    params.Grid = grid;

                    PageDialog.show({
                        pgId: "DAI01032",
                        params: params,
                        isModal: true,
                        isChild: true,
                        reuse: false,
                        width: 1200,
                        height: 750,
                    });
                }
            });
        },
        showPrevList: function() {
            var vue = this;
            vue.$root.$refs.CtiReceiver.showPrevList();
        },
        hasPrevList: function(hasPrevList) {
            var vue = this;
            $(vue.$el).find(".ctiPrevList").attr("disabled", !hasPrevList);
        },
        showSilentPrint: function(show) {
            var vue = this;
            vue.hasSilentPrint = show;
        },
        setSilentPrint: function(enabled) {
            var vue = this;
            vue.silentPrint = enabled ? "1" : "0";
        },
        onSilentPrintChanged: function() {
            var vue = this;
            if (vue.silentPrint == "0") {
                vue.printStatus = null;
                vue.printMessage = "";
            }
            vue.$root.$emit("setSilentPrint", vue.silentPrint == "1");
        },
        setPrintMessage: function(arg) {
            var vue = this;

            if (vue.silentPrint == "0") return;

            var ret = arg.split("/");
            var status = ret[0];
            var msg = ret[1];

            vue.printStatus = status;
            vue.printMessage = msg;

            if (vue.printStatus == "running") {
                if (vue.printMessageInterval) clearInterval(vue.printMessageInterval);

                vue.printMessageInterval = setInterval(
                    () => {
                        if (vue.printStatus == "running") {
                            var m = vue.printMessage.match(/\.+$/);
                            var r = !!m ? (m[0].length % 5 + 1) : 1;
                            vue.printMessage = vue.printMessage.replace(/\.+$/, "") + ".".repeat(r);
                        } else {
                            clearInterval(vue.printMessageInterval);
                        }
                    },
                    1000
                );
            }
        },
    }
}
</script>
