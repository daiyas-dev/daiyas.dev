<template>
    <div class="CtiReceiver" style="display: none;">Calling</div>
</template>

<style>
CtiReceiver {
    color: black;
    background-color: orange;
    font-weight: bold;
}
</style>

<script>
export default {
    name: "CtiReceiver",
    data() {
        return {
        };
    },
    components: {
    },
    created: function () {
    },
    mounted: function () {
        var vue = this;

        vue.$root.$on("OnCall", (arg) => vue.onCall(arg));
        vue.$root.$on("OffCall", (arg) => vue.offCall(arg));

        if (window.ipcRenderer) {
            window.ipcRenderer.on("onCall", (e, arg) => {
                console.log("emit OnCall");
                vue.$root.$emit("OnCall", arg);
            });
            window.ipcRenderer.on("offCall", (e, arg) => {
                console.log("emit OffCall");
                vue.$root.$emit("OffCall", arg);
            });

            // window.ipcRenderer.on("count", (e, arg) => {
            //     console.log("pong:" + arg);
            //     if (arg > 5) {
            //         vue.$root.$emit("OffCall", arg);
            //     } else {
            //         vue.$root.$emit("OnCall", arg);
            //         setTimeout(() => {
            //             var c = arg + 1;
            //             ipcRenderer.send("count", c);
            //             console.log("ping:" + c);
            //         }, 500);
            //     }
            // });
        }
    },
    methods: {
        onCall: function(arg) {
            console.log("OnCall");
            $(".CtiReceiver").text("着信:" + arg).show();
        },
        offCall: function(arg) {
            console.log("OffCall");
            $(".CtiReceiver").hide();
        },
    }
};
</script>
