<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}" />
    <link rel="stylesheet" href="{{ mix('/css/daiyas.css') }}" />
    <script>
        window.Laravel = {};
        window.Laravel.csrfToken = "{{ csrf_token() }}";
    </script>
</head>
<body class="daiyas">
    <div id="vue-app" class="vh-100">
        <!-- menu -->
        <top-menu ref="TopMenu"></top-menu>
        <!-- header -->
        <app-header ref="AppHeader"></app-header>
        <!-- main -->
        <div class="container body-content ml-0 mt-0 mr-0 mb-3 p-0" style="max-width: none;">
            <transition name="fade" mode="out-in">
                <!-- keyによるkeep-alive切替 -->
                <keep-alive :max=50>
                    <router-view class="w-100" :key="$route.meta.keepAlive ? $route.fullPath : Date.parse(new Date())">
                </router-view>
                </keep-alive>
            </transition>
        </div>
        <!-- footer -->
        <app-footer ref="AppFooter"></app-footer>
        <!-- logon -->
        <logon-form ref="LogonForm"></logon-form>
        <!-- dialog -->
        <page-dialog ref="PageDialog"></page-dialog>
    </div>
    <script src="{{ mix('/js/manifest.js') }}"></script>
    <script src="{{ mix('/js/vendor.js') }}"></script>
    <script src="{{ mix('/js/app.js') }}"></script>
    </body>
</html>
