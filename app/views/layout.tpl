<!DOCTYPE html>
<!--
Licensed under the Apache License, Version 2.0 (the "License"),
see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
-->
<html>
    <head>
        <title><{block name="title"}>Default Title<{/block}></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link type="text/css" rel="stylesheet" href="/static/css/bootstrap.css" />
        <{block name="head"}><{/block}>
    </head>
    <body>
        <header id="header">
            <{block name="header"}><{/block}>
        </header>
        <nav id="nav" class="nav">
            <{block name="nav"}><{/block}>
        </nav>
        <section id="content">
            <{block name="body"}><{/block}>
        </section>
        <footer id="footer">
            <{block name="footer"}><{/block}>
        </footer>
        <script type="text/javascript" src="/static/js/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="/bootstrap/dist/js/bootstrap.min.js"></script>
    </body>
</html>
