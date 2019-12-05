/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');
    require("jquery-ui-pack");

    $.widget.bridge('uibutton', $.ui.button);
    $.widget.bridge('uitooltip', $.ui.tooltip);

    window.Popper = require('popper.js').default;
    require('bootstrap');

    window.Push = require('push.js');

    //Cache
    const NodeCache = require("node-cache");
    const myCache = new NodeCache({ stdTTL: 60 });
    myCache.on("expired", function (k, v) {
        console.log("Cache Expired", k, v);
    });
    window.myCache = myCache;

} catch (e) {
    console.log("bootstrap error", e);
}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

//axios use node-cache
window.axios.interceptors.response.use(
    response => {
        var url = response.config.url;
        var params = response.config.data;
        var data = response.data;

        //console.log("axios response interceptor", url, params);
        var key = response.config.url + (params ? ("?" + $.param(JSON.parse(params))) : "");

        var all = [
            "/Utilities/GetBushoList",
            "/Utilities/GetTantoList",
            "/Utilities/GetCodeList",
        ];

        var excepts = [
            "/Account/Login",
            "/Account/Logout",
        ];

        if (excepts.includes(url)) {
            return response;
        }

        if (all.includes(url) && !params) {
            //console.log("axios response Cached", url);
            window.myCache.set(url, data, 0);
        } else {
            //console.log("axios response Cached", key);
            window.myCache.set(key, data);
        }

        return response;
    },
    error => {
        return Promise.reject(error);
    }
);
window.axios.interceptors.request.use(
    request => {
        var url = request.url;
        var params = request.data;

        //console.log("axios request interceptor", url, params);
        var key = request.url + (params ? ("?" + $.param(params)) : "");

        if (!window.myCache.has(key) && url == "/Utilities/GetTantoList") {
            key = url;
        }
        if (!window.myCache.has(key) && url == "/Utilities/GetCodeList") {
            key = url;
        }

        if (window.myCache.has(key)) {
            var cache = window.myCache.get(key);

            if (key == "/Utilities/GetTantoList" && !!params.bushoCd) {
                //console.log("axios request extract Cache", url, params);
                cache = cache.filter(v => !!v.部署 && v.部署.部署CD == params.bushoCd);
            }
            if (key == "/Utilities/GetCodeList" && !!params.cd) {
                //console.log("axios request extract Cache", url, params);
                cache = cache.filter(v => v.各種CD == params.cd);
            }
            //console.log("axios request find Cache", url, cache);

            request.adapter = () => {
                return Promise.resolve({
                    data: cache,
                    status: request.status,
                    statusText: request.statusText,
                    headers: request.headers,
                    config: request,
                    request: request
                });
            };
        }

        return request;
    },
    error => {
        return Promise.reject(error)
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'
// window.io = require('socket.io-client');

// window.Echo = new Echo({
//     broadcaster: 'socket.io',
//     host: 'https://daiyas.dev:6001',
// });
