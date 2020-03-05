<?php

/**
 * CreateApp.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */

namespace loeye\commands;

use loeye\console\Command;
use \Symfony\Component\Console\{
    Input\InputInterface,
    Output\OutputInterface
};

/**
 * CreateApp
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class CreateApp extends Command {

    protected $name           = 'loeye:create-app';
    protected $args           = [
        ['property', 'required' => true, 'help' => 'property name']
    ];
    protected $params         = [
        ['path', 'p', 'required' => false, 'help' => 'path', 'default' => null]
    ];
    protected $property;
    protected $dirMap         = [
        'app'     => [
            'commands'    => 'property',
            'conf'        => [
                'modules'  => 'property',
                'router'   => 'general',
                'property' => [
                    'app'      => null,
                    'cache'    => null,
                    'database' => null,
                ],
                'validate' => 'property',
            ],
            'controllers' => 'property',
            'errors'      => null,
            'models'      => [
                'entity'     => 'property',
                'repository' => 'property',
                'server'     => 'property',
            ],
            'plugins'     => 'property',
            'resource'   => 'property',
            'views'       => 'property',
        ],
        'htdocs'  => [
            'static' => [
                'css'    => null,
                'js'     => null,
                'images' => null,
            ]
        ],
        'runtime' => null,
    ];
    protected $appConfig      = <<<'EOF'
- settings: [master]
  profile: ${LOEYAE_ACTIVE_PROFILE:local}
  constants:
        BASE_SERVER_URL: http://localhost.com/
  application:
    cache: pfile # One of "apc"; "array"; "file"; "memcached"; "parray"; "pfile"; "redis"
    database:
        default: default
        is_dev_mode: true
        encrypt_mode: explicit # One of "explicit"; "crypt"; "keydb"
  configuration:
    property_name: {property} # Required
    timezone: Asia/Shanghai # Required
  locale:
    default: zh_CN
    basename: lang # Required
    supported_languages: ["zh_CN"]
EOF;
    protected $databaseConfig = <<<'EOF'
- settings: [master] # Required
  default:
    driver: pdo_mysql
    user: root
    password: ''
    host: 127.0.0.1
    dbname: {property}
    charset : utf8mb4
EOF;
    protected $cacheConfig    = <<<'EOF'
- settings: [master] # Required
  pfile:
    lifetime: 0
EOF;
    protected $moduleConfig   = <<<'EOF'
- settings: [master] # Required
  module:
    module_id: {property}.home # Required
    plugin:               # Required
        -
            name: \loeye\plugin\TranslatorPlugin
    view:
        default:
            tpl: {property}.home.tpl
EOF;
    protected $routeConfig    = <<<'EOF'
- settings: [master]
  routes:
    home:
        path: ^/$
        module_id: {property}.home

    {property}:
        path : ^/{property}/{module}/$
        module_id : {property}.{module}
        regex:
            module: \w+
EOF;
    protected $layout         = <<<'EOF'
<!DOCTYPE html>
<html>
    <head>
        <title><{block name="title"}>Default Title<{/block}></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link type="text/css" rel="stylesheet" href="/static/css/bootstrap.css" />
        <{block name="head"}><{/block}>
    </head>
    <body>
        <header id="header" class="navbar navbar-expand navbar-fixed-top navbar-dark flex-column flex-md-row bd-navbar">
            <{block name="header"}><{/block}>
        </header>
        <section id="content" class="container-fluid">
            <div class="row flex-xl-nowrap">
                <div id="nav" class="col-md-3 col-xl-2 bd-sidebar">
                    <{block name="nav"}><{/block}>
                </div>
                <main class='col-md-9 col-xl-8 py-md-3 pl-md-5 bd-content'role='main'>
                    <div id="content" class="min-vh-100">
                        <{block name="body"}><{/block}>
                    </div>
                    <footer id="footer" class="col-md-12">
                        <{block name="footer"}><{/block}>
                    </footer>
                </main>
            </div>
        </section>
    </body>
    <script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/bootstrap.js"></script>
</html>
EOF;
    protected $homeTpl        = <<<'EOF'
<{extends file='layout.tpl'}>
<{block name="title"}>Hello World!<{/block}>
<{block name="body"}>
<div class="col-12 row-cols-1 text-center">
    Hello World!
</div>
<{/block}>

EOF;
    protected $bootstrapCss   = <<<'EOF'
@import '/bootstrap/dist/css/bootstrap.min.css';
@import '/bootstrap/dist/css/bootstrap-grid.min.css';
@import '/bootstrap/dist/css/bootstrap-reboot.min.css';
EOF;
    protected $htaccess       = <<<'EOF'
DirectoryIndex index.html Dispatcher.php index.htm
Options -Indexes

SetEnv routerDir "general"
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule .* Dispatcher.php [QSA,L]
EOF;
    protected $dispatcher     = <<<'EOF'
<?php

/**
 * Dispatcher.php
 *
 */
mb_internal_encoding('UTF-8');

define('APP_BASE_DIR', dirname(__DIR__));
define('PROJECT_NAMESPACE', 'app');
define('PROJECT_DIR', realpath(APP_BASE_DIR . '/' . PROJECT_NAMESPACE));

require_once APP_BASE_DIR . DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php';

define('LOEYE_MODE', LOEYE_MODE_DEV);

$dispatcher = new loeye\web\Dispatcher();
$dispatcher->dispatche();
EOF;
    protected $generalError   = <<<'EOF'
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
EOF;


    /**
     * process
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $this->property = $input->getArgument('property');
        $ui             = new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);
        $dir            = $input->getOption('path') ?? getcwd();
        $ui->block($dir);
        $this->mkdir($ui, $dir, $this->dirMap);
        $this->initFile($ui, $dir);
    }


    /**
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle $ui
     * @param string                                        $base
     * @param mixed                                         $var
     *
     * @return string
     */
    protected function mkdir(\Symfony\Component\Console\Style\SymfonyStyle $ui, string $base, $var): ?string
    {
        $dir = $base;
        if (is_array($var)) {
            foreach ($var as $key => $val) {
                $this->mkdir($ui, $this->mkdir($ui, $base, $key), $val);
            }
        } else {
            if ('property' == $var) {
                $var = $this->property;
            }
            if (null != $var) {
                $dir .= D_S . $var;
            }
            if (!file_exists($dir)) {
                $ui->block(sprintf('mkdir: %1s', $dir));
                mkdir($dir, 0755, true);
            }
        }
        return $dir;
    }


    /**
     * initFile
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle $ui
     * @param string                                        $base
     * @return void
     */
    protected function initFile(\Symfony\Component\Console\Style\SymfonyStyle $ui, string $base): void
    {
        $fileSystem       = new \Symfony\Component\Filesystem\Filesystem();
        $appConfig        = $this->buildAppConfigFile($base, 'app');
        $fileSystem->dumpFile($appConfig, $this->replaceProperty($this->appConfig));
        $ui->block(sprintf('create file: %1s', $appConfig));
        $dbConfig         = $this->buildAppConfigFile($base, 'database');
        $fileSystem->dumpFile($dbConfig, $this->replaceProperty($this->databaseConfig));
        $ui->block(sprintf('create file: %1s', $dbConfig));
        $cacheConfig      = $this->buildAppConfigFile($base, 'cache');
        $fileSystem->dumpFile($cacheConfig, $this->replaceProperty($this->cacheConfig));
        $ui->block(sprintf('create file: %1s', $cacheConfig));
        $moduleConfig     = $this->buildConfigFile($base, 'modules');
        $fileSystem->dumpFile($moduleConfig, $this->replaceProperty($this->moduleConfig));
        $ui->block(sprintf('create file: %1s', $moduleConfig));
        $routerConfig     = $this->buildConfigFile($base, 'router');
        $fileSystem->dumpFile($routerConfig, $this->replaceProperty($this->routeConfig));
        $ui->block(sprintf('create file: %1s', $routerConfig));
        $generalErrorFile = $this->buildPath($base, 'app', 'errors', 'GeneralError.php');
        $fileSystem->dumpFile($generalErrorFile, $this->replaceProperty($this->generalError));
        $ui->block(sprintf('create file: %1s', $generalErrorFile));
        $layout           = $this->buildPath($base, 'app', 'views', 'layout.tpl');
        $fileSystem->dumpFile($layout, $this->replaceProperty($this->layout));
        $ui->block(sprintf('create file: %1s', $layout));
        $home             = $this->buildPath($base, 'app', 'views', $this->property, 'home.tpl');
        $fileSystem->dumpFile($home, $this->replaceProperty($this->homeTpl));
        $ui->block(sprintf('create file: %1s', $home));
        $css              = $this->buildPath($base, 'app', 'htdocs', 'static', 'css', 'bootstrap.css');
        $fileSystem->dumpFile($css, $this->replaceProperty($this->bootstrapCss));
        $ui->block(sprintf('create file: %1s', $css));
        $htaccss          = $this->buildPath($base, 'app', 'htdocs', '.htaccess');
        $fileSystem->dumpFile($htaccss, $this->replaceProperty($this->htaccess));
        $ui->block(sprintf('create file: %1s', $htaccss));
        $dispatcher       = $this->buildPath($base, 'app', 'htdocs', 'Dispatcher.php');
        $fileSystem->dumpFile($dispatcher, $this->replaceProperty($this->dispatcher));
        $ui->block(sprintf('create file: %1s', $dispatcher));
    }


    /**
     * buildAppConfigFile
     *
     * @param string $base
     * @param string $type
     *
     * @return strig
     */
    protected function buildAppConfigFile(string $base, string $type): string
    {
        return $this->buildPath($base, 'app', 'conf', $this->property, $type, 'master.yml');
    }


    /**
     * buildConfigFile
     *
     * @param string $base
     * @param string $type
     *
     * @return string
     */
    protected function buildConfigFile(string $base, string $type): string
    {
        return $this->buildPath($base, 'app', 'conf', $type, $this->property, 'master.yml');
    }


    /**
     * buildPath
     *
     * @param string $path
     *
     * @return string
     */
    protected function buildPath(string ...$path): string
    {
        return implode(D_S, $path);
    }


    /**
     * replaceProperty
     *
     * @param string $tpl
     *
     * @return string
     */
    protected function replaceProperty(string $tpl): string
    {
        return str_replace('{property}', $this->property, $tpl);
    }

}
