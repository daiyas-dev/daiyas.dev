<template>
    <header class="AppHeader mt-2 mb-2">
        <div class="row ml-2 mr-2">
            <div class="col-md-2">
                <span :class="['badge', this.isLogOn == true ? 'badge-success' : 'badge-danger']">{{title}}</span>
            </div>
            <div class="col-md-4 pr-2 justify-content-end">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-primary" @click="showPrevList">CTI再表示</button>
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
            VueCheck: VueCheck,
            DatePickerWrapper: DatePickerWrapper,
            fetch: true,
            interval: null,
        }
    },
    components: {
    },
    computed: {
        nowDate: function () {
            return moment().format('YYYY/MM/DD', new Date());
        }
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
        },
        startPolling: function() {
            var vue = this;
            vue.fetch = true;

            if (!vue.interval) {
                vue.interval = setInterval(
                    () => {
                        if (!!vue.fetch) {
                            axios.post("/Utilities/SearchWebOrderList", { UnRegisted: "1", noCache: true })
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
                params: { TargetDate: moment().format("YYYYMMDD"), UnRegisted: "1", Start: 1, Chunk: 100 },
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
                    { title: "取込", dataIndx: "取込", dataType: "string", align: "center", width: 100, maxWidth: 100, minWidth: 100,
                      render: ui => {
                          return { text: ui.rowData.確認 == "0" ? "" : "取込済"  };
                      },
                    },
                ],
                width: 1000,
                height: 700,
                reuse: false,
                showBushoSelector: true,
                customParams: { TargetDate: moment().format("YYYYMMDD"), UnRegisted: "1" },
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

                    var vc = new (VueApp.createInstance(vue.VueCheck))(
                        {
                            propsData: {
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
                            }
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
        show01032: function(data, grid) {
            var vue = this;

            var params = _.cloneDeep(data);
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
        },
        showPrevList: function() {
            var vue = this;
            vue.$root.$refs.CtiReceiver.showPrevList();
        },
    }
}
</script>
