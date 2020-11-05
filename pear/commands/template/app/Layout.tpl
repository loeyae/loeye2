<!DOCTYPE html>
<html>
    <head>
        <title>
            <{$smarty.ldelim}>block name="title"<{$smarty.rdelim}>Default Title<{$smarty.ldelim}>/block <{$smarty.rdelim}></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link type="text/css" rel="stylesheet" href="/static/css/bootstrap.css" />
        <{$smarty.ldelim}>block name="head"<{$smarty.rdelim}><{$smarty.ldelim}>/block<{$smarty.rdelim}>
    </head>
    <body>
        <header id="header" class="navbar navbar-expand navbar-fixed-top navbar-dark flex-column flex-md-row bd-navbar">
            <{$smarty.ldelim}>block name="header"<{$smarty.rdelim}><{$smarty.ldelim}>/block<{$smarty.rdelim}>
        </header>
        <section id="content" class="container-fluid">
            <div class="row flex-xl-nowrap">
                <div id="nav" class="col-md-3 col-xl-2 bd-sidebar">
                    <{$smarty.ldelim}>block name="nav"<{$smarty.rdelim}><{$smarty.ldelim}>/block<{$smarty.rdelim}>
                </div>
                <main class='col-md-9 col-xl-8 py-md-3 pl-md-5 bd-content'role='main'>
                    <div id="content" class="min-vh-100">
                        <{$smarty.ldelim}>block name="body"<{$smarty.rdelim}><{$smarty.ldelim}>/block<{$smarty.rdelim}>
                    </div>
                    <footer id="footer" class="col-md-12">
                        <{$smarty.ldelim}>block name="footer"<{$smarty.rdelim}><{$smarty.ldelim}>/block<{$smarty.rdelim}>
                    </footer>
                </main>
            </div>
        </section>
    </body>
    <script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/bootstrap.js"></script>
</html>
