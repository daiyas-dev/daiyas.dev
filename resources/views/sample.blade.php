<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}" />
    <link rel="stylesheet" href="{{ mix('/css/daiyas.css') }}" />
    <title>Title</title>
</head>
<body class="daiyas">
    <h1>HMR Test</h1>
    <h2>This is Vue Example Component</h2>
    <div id="vue-app">
        <example-component ref="ExampleComponent"></example-component>
        <logon-form ref="LogonForm"></logon-form>
    </div>
    <script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>
