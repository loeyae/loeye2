<!DOCTYPE html>
<!--
Licensed under the Apache License, Version 2.0 (the "License"),
see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
-->
<html>
    <head>
        <title>出错了</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link type="text/css" rel="stylesheet" href="/static/css/bootstrap.css" />
    </head>
    <body>
        <header id="header" class="navbar navbar-expand navbar-dark flex-column flex-md-row bd-navbar">
            <a class="navbar-brand mr-0 mr-md-2" href="/"></a>
            <div class=""></div>
        </header>
        <div class="container-fluid min-vh-100">
            <nav id="nav" class="nav clearfix col-12 text-center row-cols-1 my-2">
                <a href="/" class="float-left col-auto">首页</a><?php if (!empty($_SERVER['HTTP_REFERER'])) { ?><a href="<?= $_SERVER['HTTP_REFERER']; ?>" class="float-right col-auto">返回前页</a><?php } ?>
            </nav>
            <section id="content" class="text-center">
                <p class="text-danger">
                <?php
                if ($exc instanceof \loeye\error\ResourceException) {
                    $message = '找不到了';
                } else {
                    $message = '内部错误';
                }
                echo $message;
                ?>
                </p>
            </section>
        </div>
        <footer id="footer" class="text-black-50 col-12 text-center mt-1 mb-4">
            <div id="copyright"><span>©<?= date('Y'); ?>&nbsp;</span><span>Loeyae.com&nbsp;</span></div>
        </footer>
        <script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
        <script type="text/javascript" src="/bootstrap/dist/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
