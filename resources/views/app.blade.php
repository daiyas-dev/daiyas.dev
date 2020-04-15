<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico" size="48x48">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" />
    <script>
        window.Laravel = {};
        window.Laravel.csrfToken = "{{ csrf_token() }}";
    </script>
</head>
<body class="daiyas" id="daiyas-body">
    <div id="vue-app" class="vh-100">
        <!-- menu -->
        <top-menu ref="TopMenu"></top-menu>
        <!-- header -->
        <app-header ref="AppHeader"></app-header>
        <!-- cti recever -->
        <cti-receiver ref="CtiReceiver"></cti-receiver>
        <!-- main -->
        <div class="container body-content m-0 p-0" style="max-width: none;">
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
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    </body>
    <!-- bootstrap/jquery-ui への再設定含む為、jsの後に読み込む -->
    <link rel="stylesheet" href="{{ mix('css/daiyas.css') }}" />
    <!-- pqgridのimportでcssが強制的に読み込まれてしまうので、themeはその後に読み込む -->
    <link rel="stylesheet" href="{{ mix('css/pqgrid/themes/steelblue/pqgrid.css') }}" />
</html>
