<?php
/**
 * CreateService.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020/4/11 19:21
 * @link     https://github.com/loeyae/loeye2.git
 */


namespace loeye\commands;

use Doctrine\Persistence\Mapping\ClassMetadata;
use loeye\console\Command;
use loeye\console\helper\EntityGeneratorTrait;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

/**
 * Class CreateService
 * @package loeye\commands
 */
class CreateService extends Command
{

    use EntityGeneratorTrait;

    protected const BASE_DIR_NAME = 'services';

    protected $name = 'loeye:create-service';
    protected $desc = 'create service';
    protected $args = [
        ['property', 'required' => true, 'help' => 'The application property name.']
    ];
    protected $params = [
        ['db-id', 'd', 'required' => false, 'help' => 'database setting id', 'default' => 'default'],
        ['filter', 'f', 'required' => false, 'help' => 'filter', 'default' => null],
        ['force', null, 'required' => false, 'help' => 'force update file', 'default' => false],
    ];
    protected $property;

    protected $dispatcher = <<<'EOF'
<?php

/**
 * Dispatcher.php
 *
 */
 use loeye\service\Dispatcher;
 
mb_internal_encoding('UTF-8');

define('APP_BASE_DIR', dirname(__DIR__));
define('PROJECT_NAMESPACE', 'app');
define('PROJECT_DIR', realpath(APP_BASE_DIR . '/' . PROJECT_NAMESPACE));

require_once APP_BASE_DIR . DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php';

define('LOEYE_MODE', LOEYE_MODE_DEV);

$dispatcher = new Dispatcher();
$dispatcher->init([
    'rewrite' => [
        '/<module:\w+>/<service:\w+>/<handler:\w+>/<id:\w+>' => '{module}/{service}/{handler}',
        '/<module:\w+>/<service:\w+>/<handler:\w+>' => '{module}/{service}/{handler}',
    ]
]);
$dispatcher->dispatch();
EOF;

    protected $clientTemplate = <<<'EOF'
<?php

/**
 * <className>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <datetime>
 */
namespace <namespace>;

use loeye\base\Exception;
use loeye\client\Client;
use loeye\client\Request;
use loeye\client\Response;

/**
 * <className>
 *
 * @author Zhang Yi <loeyae@gmail.com>
 */
class <className> extends Client
{
    /**
     * property name
     */
    private $bundle = '<property>';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct($this->bundle);
    }

<classBody>

    /**
     * @inheritDoc
     */
    public function responseHandle($cmd, Response $resp)
    {
        $result = json_decode($resp->getContent(), true);
        $code = $result['status']['code'];
        $statusCode = $resp->getStatusCode();
        if ($statusCode === self::REQUEST_STATUS_OK && (int)$code === LOEYE_REST_STATUS_OK) {
            switch ($cmd) {
                default:
                    return $result;
            }
        } else {
            $req_url = $resp->getRequest()->getUri();
            if ($code !== LOEYE_REST_STATUS_OK) {
                $errmsg = $result['status']['message'];
                $msg = sprintf(
                    "[%s] request :%s \nhttp_code : %s\nmessage:%s",
                    self::class, $req_url, $statusCode, $errmsg
                );
                return new Exception($msg, $code);
            }

            $errcode = $resp->getErrorCode();
            $errmsg = $resp->getErrorMsg();
            $msg = sprintf(
                "[%s] request :%s \nhttp_code : %s\nmessage:%s",
                self::class, $req_url, $errcode, $errmsg
            );
            return new \Exception($msg, $errcode);
        }
    }
}
EOF;

    protected $clientMethodTemplate = <<<'EOF'

    /**
     * <methodName>
     *
<paramsStatement>
     * @param mixed &$ret  result
     *
     * @return mixed
     */
    public function <methodName>(<params>, &$ret = false)
    {
        $path = <path>;
        $req = new Request();
        $this->setReq($req, '<method>', $path);
        <requestBody>
        return $this->request(__FUNCTION__, $req, $ret);
    }
EOF;

    protected $requestBodyTemplate = <<<'EOF'
$req->setContent('application/json', json_encode(array('request_data' => <parameter>), JSON_UNESCAPED_SLASHES | 
JSON_UNESCAPED_UNICODE));
EOF;


    protected $abstractHandlerTemplate = <<<'EOF'
<?php

/**
 * <className>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <datetime>
 */
namespace <namespace>;


use <fullServerClass>;
use loeye\base\Context;
use loeye\service\Handler;

/**
 * <className>
 *
 * @author Zhang Yi <loeyae@gmail.com>
 */
abstract class <className> extends Handler
{

    /**
     * @var <serverClass>
     */
    protected $server;

    /**
     * @inheritDoc
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->server = new <serverClass>($context->getAppConfig());
    }

}
EOF;

    protected $handlerTemplate = <<<'EOF'
<?php

/**
 * <className>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <datetime>
 */

namespace <namespace>;

/**
 * <className>
 *
 * @author Zhang Yi <loeyae@gmail.com>
 */
class <className> extends <abstractClassName>
{

    /**
     * @inheritDoc
     */
    protected function process($req)
    {
<parameterStatement>
        return $this->server-><method>(<parameter>);
    }
}
EOF;

    protected $getHandlerParameterStatementTemplate = <<<'EOF'
        $<parameter> = $this->pathParameter['<parameter>'];
EOF;


    protected $postHandlerParameterStatementTemplate = <<<'EOF'
        $<parameter> = $req['<parameter>'];
EOF;

    protected $pageHandlerTemplate = <<<'EOF'
<?php

/**
 * <className>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <datetime>
 */

namespace <namespace>;

use loeye\base\Logger;
use Throwable;

/**
 * <className>
 *
 * @author Zhang Yi <loeyae@gmail.com>
 */
class <className> extends <abstractClassName>
{

    /**
     * @inheritDoc
     */
    protected function process($req)
    {
<parameterStatement>
        try {
            return $this->server-><method>(<parameter>);
        } catch (Throwable $e) {
            Logger::exception($e);
        }
        return [];
    }
}
EOF;

    private $handlerDir;
    private $clientDir;

    /**
     * generateFile
     *
     * @param SymfonyStyle $ui
     * @param ClassMetadata $metadata
     * @param string $namespace
     * @param string $destPath
     * @param boolean $force
     * @throws ReflectionException
     */
    protected function generateFile(SymfonyStyle $ui, ClassMetadata $metadata, $namespace, $destPath, $force): void
    {
        $entityName = $this->getEntityName($metadata->reflClass->name);
        $namespace .= '\\' . $entityName;
        $destPath .= D_S . $entityName;
        $serverClass = $this->getServerClass($metadata->reflClass->name);
        $this->writeClient($ui, $entityName, $serverClass, $force);
        $this->writeAbstractHandler($ui, $namespace, $entityName, $serverClass, $destPath, $force);
        $this->writeHandler($ui, $namespace, $entityName, $serverClass, $destPath, $force);
    }

    /**
     * writeClient
     *
     * @param SymfonyStyle $ui
     * @param string $entityName
     * @param string $serverClass
     * @param bool $force
     * @throws ReflectionException
     */
    protected function writeClient(SymfonyStyle $ui, $entityName, $serverClass, $force = false): void
    {

        $clientName = ucfirst($entityName) . 'Client';
        $namespace = $this->getNamespace($this->clientDir);
        $fullClientClassName = $namespace . $clientName;
        $ui->text(sprintf('Processing Client "<info>%s</info>"', $fullClientClassName));
        $classBody = $this->generateClientBody($serverClass, $this->property, $entityName);
        $code = $this->generateClientFile($clientName, $namespace, $this->property, $classBody);

        $this->writeFile($this->clientDir, $clientName, $code, $force);
    }

    /**
     * generateClientFile
     *
     * @param string $className
     * @param string $namespace
     * @param string $property
     * @param string $classBody
     *
     * @return string
     */
    protected function generateClientFile($className, $namespace, $property, $classBody): string
    {
        $variables = [
            '<className>' => $className,
            '<datetime>' => date('Y-m-d H:i:s'),
            '<namespace>' => $namespace,
            '<property>' => $property,
            '<classBody>' => $classBody,
        ];

        return self::generateTemplate($variables, $this->clientTemplate);
    }

    /**
     * generateClientBody
     *
     * @param $serverClass
     * @param $property
     * @param $entityName
     * @return string
     * @throws ReflectionException
     */
    protected function generateClientBody($serverClass, $property, $entityName): string
    {
        $refClass = new ReflectionClass($serverClass);
        $methods = $refClass->getMethods();
        $body = [];
        foreach ($methods as $method) {
            if ($method->isConstructor() || $method->isFinal() || $method->isPrivate()) {
                continue;
            }
            [$paramsStatement, $params, $type, $path, $requestBody] = $this->generateParameter($method, $property,
                $entityName);
            $variables = [
                '<methodName>' => $method->getName(),
                '<paramsStatement>' => $paramsStatement,
                '<params>' => $params,
                '<path>' => $path,
                '<method>' => $type,
                '<requestBody>' => $requestBody,
            ];
            $body[] = self::generateTemplate($variables, $this->clientMethodTemplate);
        }
        return implode("\r\n", $body);
    }

    /**
     * generateParameter
     *
     * @param ReflectionMethod $method
     * @param $property
     * @param $entityName
     * @return array
     */
    protected function generateParameter(ReflectionMethod $method, $property, $entityName): array
    {
        $paramsStatementArray = [];
        $paramsArray = [];
        $parameters = $method->getParameters();
        $path = '\'/' . $property . '/' . $entityName . '/' . $method->getName();
        $type = $method->getName() === 'get' ? 'GET' : 'POST';
        foreach ($parameters as $parameter) {
            $pType = $parameter->getType();
            if (!$pType) {
                $pType = 'mixed';
            }
            $paramsStatementArray[] = '     * @param ' . $pType . ' $' . $parameter->getName();
            $paramsArray[] = $parameter->getName();
        }
        $requestBody = '';
        $mappedParamsArray = array_map(static function ($item) {
            return '$' . $item;
        }, $paramsArray);
        if (!empty($parameters)) {
            if ($type === 'GET') {
                $m = array_map(static function ($item) {
                    return '\'. $' . $item;
                }, $paramsArray);
                $path .= '/' . implode('.\'/', $m);
            } else {
                $path .= '\'';
                $m = array_map(static function ($item) {
                    return '\'' . $item . '\' => $' . $item;
                }, $paramsArray);
                $p = ['<parameter>' => '[' . implode(',', $m) . ']'];
                $requestBody = self::generateTemplate($p, $this->requestBodyTemplate);
            }
        } else {
            $path .= '\'';
        }
        return [implode("\r\n", $paramsStatementArray), implode(', ', $mappedParamsArray), $type, $path, $requestBody];
    }

    /**
     * writeAbstractHandler
     *
     * @param SymfonyStyle $ui
     * @param $namespace
     * @param $className
     * @param $serverClass
     * @param $destPath
     * @param $force
     */
    protected function writeAbstractHandler(SymfonyStyle $ui, $namespace, $className, $serverClass, $destPath, $force): void
    {
        $abstractClassName = 'Abstract' . ucfirst($className) . 'Handler';
        $fullClassName = $namespace .'\\'. $abstractClassName;
        $ui->text(sprintf('Processing AbstractClassFile "<info>%s</info>"', $fullClassName));
        $variable = [
            '<className>' => $abstractClassName,
            '<namespace>' => $namespace,
            '<datetime>' => date('Y-m-d H:i:s'),
            '<fullServerClass>' => $serverClass,
            '<serverClass>' => self::getClassName($serverClass),
        ];
        $code = self::generateTemplate($variable, $this->abstractHandlerTemplate);
        $this->writeFile($destPath, $abstractClassName, $code, $force);
    }

    /**
     * writeHandler
     *
     * @param SymfonyStyle $ui
     * @param $namespace
     * @param $className
     * @param $serverClass
     * @param $destPath
     * @param $force
     * @throws ReflectionException
     */
    protected function writeHandler(SymfonyStyle $ui, $namespace, $className, $serverClass, $destPath, $force): void
    {
        $refClass = new ReflectionClass($serverClass);
        $methods = $refClass->getMethods();
        foreach ($methods as $method) {
            if ($method->isConstructor() || $method->isFinal() || $method->isPrivate()) {
                continue;
            }
            $methodName = $method->getName();
            $nClassName =  ucfirst($methodName) . 'Handler';
            $abstractClassName = 'Abstract' . ucfirst($className) . 'Handler';
            $fullClassName = $namespace . '\\' . $nClassName;
            $ui->text(sprintf('Processing ClassFile "<info>%s</info>"', $fullClassName));
            $type = $methodName === 'get' ? 'GET' : 'POST';
            $parameters = $method->getParameters();
            if ($type === 'GET') {
                [$parameterStatement, $parameter] = $this->generateGetHandlerParameter($parameters);
            } else {
                [$parameterStatement, $parameter] = $this->generatePostHandlerParameter($parameters);
            }
            $variable = [
                '<className>' => $nClassName,
                '<namespace>' => $namespace,
                '<datetime>' => date('Y-m-d H:i:s'),
                '<abstractClassName>' => $abstractClassName,
                '<method>' => $method->getName(),
                '<parameterStatement>' => $parameterStatement,
                '<parameter>' => $parameter,
            ];
            if ($methodName === 'page') {
                $code = self::generateTemplate($variable, $this->pageHandlerTemplate);
            } else {
                $code = self::generateTemplate($variable, $this->handlerTemplate);
            }
            $this->writeFile($destPath, $nClassName, $code, $force);
        }
    }

    /**
     * generateGetHandlerParameter
     *
     * @param ReflectionParameter[] $parameters
     * @return array
     */
    protected function generateGetHandlerParameter($parameters): array
    {
        $codes = [];
        $parameterList = [];
        foreach ($parameters as $parameter) {
            $codes[] = self::generateTemplate(['<parameter>' => $parameter->getName()],
                $this->getHandlerParameterStatementTemplate);
            $parameterList[] = '$'. $parameter->getName();
        }
        return [implode("\r\n", $codes), implode(', ', $parameterList)];
    }

    /**
     * generatePostHandlerParameter
     *
     * @param ReflectionParameter[] $parameters
     * @return array
     */
    protected function generatePostHandlerParameter($parameters): array
    {
        $codes = [];
        $parameterList = [];
        foreach ($parameters as $parameter) {
            $code = self::generateTemplate(['<parameter>' => $parameter->getName()],
                $this->postHandlerParameterStatementTemplate);
            try {
                $default = $parameter->getDefaultValue();
                if (is_numeric($default) || is_bool($default)) {
                    $code = str_replace(';', ' ?? ' . $default. ';', $code);
                } else if ($default === null) {
                    $code = str_replace(';', ' ?? null;', $code);
                }else {
                    $code = str_replace(';', ' ?? \'' . $default . '\';', $code);
                }
            } catch (Throwable $e) {
                $e->getTraceAsString();
            }
            $codes[] = $code;
            $parameterList[] = '$'. $parameter->getName();
        }
        return [implode("\r\n", $codes), implode(', ', $parameterList)];
    }

    /**
     * getServerClass
     *
     * @param string $className
     * @return string
     */
    protected function getServerClass($className): string
    {
        return '\\' . str_replace('entity', 'server', $className) . 'Server';
    }

    /**
     * getEntityName
     *
     * @param string $fullClassName
     * @return string
     */
    protected function getEntityName($fullClassName): string
    {
        return lcfirst(substr($fullClassName, strrpos($fullClassName, '\\') + 1));
    }


    /**
     *
     * @param InputInterface $input
     *
     * @param SymfonyStyle $ui
     * @return string
     */
    protected function getDestPath(InputInterface $input, SymfonyStyle $ui): string
    {
        $baseDir = dirname(PROJECT_DIR);
        $this->createServiceDispatcher($baseDir, $ui);
        $property = $input->getArgument('property');
        $this->property = $property;
        [$handlerDir, $clientDir] = $this->mkdir($baseDir, $ui, $property);
        $this->handlerDir = $handlerDir;
        $this->clientDir = $clientDir;
        return $handlerDir;
    }

    /**
     * createServiceDispatcher
     *
     * @param string $baseDir
     * @param SymfonyStyle $ui
     */
    protected function createServiceDispatcher($baseDir, SymfonyStyle $ui): void
    {
        $dir = $baseDir . D_S . 'htdocs';
        if (!file_exists($dir) && !mkdir($dir, 755) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        $fileSystem = new Filesystem();
        $dispatcher = $dir . D_S . 'Service.php';
        $fileSystem->dumpFile($dispatcher, $this->dispatcher);
        $ui->block(sprintf('create file: %1s', $dispatcher));
    }

    /**
     * @param string $baseDir
     * @param SymfonyStyle $ui
     * @param string $property
     * @return array
     */
    protected function mkdir($baseDir, SymfonyStyle $ui, string $property): array
    {
        $handlerDir = $baseDir . D_S . 'app' . D_S . self::BASE_DIR_NAME . D_S . 'handler' . D_S . $property;
        if (!file_exists($handlerDir) && (!mkdir($handlerDir, 0755, true) || !is_dir($handlerDir))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $handlerDir));
        }
        $ui->block(sprintf('create dir: %1s', $handlerDir));
        $clientDir = $baseDir . D_S . 'app' . D_S . self::BASE_DIR_NAME . D_S . 'client' . D_S . $property;
        if (!file_exists($clientDir) && (!mkdir($clientDir, 0755, true) || !is_dir($clientDir))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $clientDir));
        }
        $ui->block(sprintf('create dir: %1s', $clientDir));
        return [$handlerDir, $clientDir];
    }

}