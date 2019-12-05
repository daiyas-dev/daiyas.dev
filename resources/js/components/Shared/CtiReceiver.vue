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
                vue.isCalling = true;
                var no = info.replace("TELCONNECT:", "");
                vue.callInfo = "通話中:" + no;
            });

            if (window.ipcRenderer) {
                window.ipcRenderer.on("CTI_MessageFromMain", (e, arg) => {
                    var msg = new TextDecoder("utf-8").decode(arg);
                    var ret = msg + " : OK";

                    console.log("CTI_MessageFromMain", msg);
                    e.sender.send("CTI_MessageFromRender", ret);

                    vue.$root.$emit("OnCall", msg);
                });
                ipcRenderer.on("CTI_Listening", (e, arg) => console.log("CTI_Listening"));
                ipcRenderer.on("CTI_BindError", (e, arg) => console.log("CTI_BindError", arg));
            }
        },
    }
};
</script>
