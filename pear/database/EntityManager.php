<?php

/**
 * EntityManager.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\database;

use Doctrine\ORM\Tools\Setup;

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(function($class) {
    return class_exists($class);
});

/**
 * EntityManager
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class EntityManager {

    static protected $entitiesDir = PROJECT_MODELS_DIR . '/entity';
    static protected $schemeDir   = PROJECT_MODELS_DIR . '/scheme';
    static protected $proxiesDir  = PROJECT_MODELS_DIR . '/proxy';
    static protected $cacheDir    = RUNTIME_CACHE_DIR . '/' . PROJECT_NAMESPACE . '/db';
    static protected $isDevMode   = (LOEYE_MODE_DEV === LOEYE_MODE || LOEYE_MODE_UNIT === LOEYE_MODE) ? true : false;

    /**
     * getManager
     *
     * @param array                       $dbSetting database setting
     * @param string                      $property  property name
     * @param Doctrine\Common\Cache\Cache $cache     cache instance
     *
     * @return \Doctrine\ORM\EntityManager
     */
    static public function getManager($dbSetting, $property, \Doctrine\Common\Cache\Cache $cache = null)
    {
        // Second configure ORM
        // globally used cache driver, in production use APC or memcached
        if (null === $cache) {
            $cache = new \Doctrine\Common\Cache\ArrayCache();
        }
        // standard annotation reader
        $annotationReader       = new \Doctrine\Common\Annotations\AnnotationReader();
        $cachedAnnotationReader = new \Doctrine\Common\Annotations\CachedReader(
                $annotationReader, // use reader
                $cache // and a cache driver
        );
        // create a driver chain for metadata reading
        $driverChain            = new \Doctrine\Persistence\Mapping\Driver\MappingDriverChain();
        // load superclass metadata mapping only, into driver chain
        // also registers Gedmo annotations.NOTE: you can personalize it
        \Gedmo\DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
                $driverChain, // our metadata driver chain, to hook into
                $cachedAnnotationReader // our cached annotation reader
        );
        // now we want to register our application entities,
        // for that we need another metadata driver used for Entity namespace
        $annotationDriver       = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(
                $cachedAnnotationReader, // our cached annotation reader
                array(realpath(self::$entitiesDir . "/" . $property)) // paths to look in
        );
        // NOTE: driver for application Entity can be different, Yaml, Xml or whatever
        // register annotation driver for our application Entity fully qualified namespace
        $driverChain->setDefaultDriver($annotationDriver);
        // general ORM configuration
        $config                 = Setup::createAnnotationMetadataConfiguration([], static::$isDevMode, self::$proxiesDir, $cache);
        $config->setProxyDir(self::$proxiesDir);
//        $repositoryFactory = new \Doctrine\ORM\Repository\DefaultRepositoryFactory();
//        $config->setRepositoryFactory($repositoryFactory);
//        $config->setProxyNamespace('\\'. PROJECT_NAMESPACE .'\\models\\proxy\\'. $property);
        $config->setAutoGenerateProxyClasses(false); // this can be based on production config.
        // register metadata driver
        $config->setMetadataDriverImpl($driverChain);
        // use our allready initialized cache driver
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);
        $logger                 = new Logger();
        $config->setSQLLogger($logger);
        // Third, create event manager and hook prefered extension listeners
        $evm                    = new \Doctrine\Common\EventManager();
        // gedmo extension listeners
        // sluggable
        $sluggableListener      = new \Gedmo\Sluggable\SluggableListener();
        // you should set the used annotation reader to listener, to avoid creating new one for mapping drivers
        $sluggableListener->setAnnotationReader($cachedAnnotationReader);
        $evm->addEventSubscriber($sluggableListener);
        // tree
        $treeListener           = new \Gedmo\Tree\TreeListener();
        $treeListener->setAnnotationReader($cachedAnnotationReader);
        $evm->addEventSubscriber($treeListener);
        // loggable, not used in example
        $loggableListener       = new \Gedmo\Loggable\LoggableListener;
        $loggableListener->setAnnotationReader($cachedAnnotationReader);
        $loggableListener->setUsername('admin');
        $evm->addEventSubscriber($loggableListener);
        // timestampable
        $timestampableListener  = new \Gedmo\Timestampable\TimestampableListener();
        $timestampableListener->setAnnotationReader($cachedAnnotationReader);
        $evm->addEventSubscriber($timestampableListener);
        // blameable
        $blameableListener      = new \Gedmo\Blameable\BlameableListener();
        $blameableListener->setAnnotationReader($cachedAnnotationReader);
        $blameableListener->setUserValue('admin'); // determine from your environment
        $evm->addEventSubscriber($blameableListener);
        // translatable
        $translatableListener   = new \Gedmo\Translatable\TranslatableListener();
        // current translation locale should be set from session or hook later into the listener
        // most important, before entity manager is flushed
        $translatableListener->setTranslatableLocale('en');
        $translatableListener->setDefaultLocale('en');
        $translatableListener->setAnnotationReader($cachedAnnotationReader);
        $evm->addEventSubscriber($translatableListener);
        // sortable, not used in example
        $sortableListener       = new \Gedmo\Sortable\SortableListener;
        $sortableListener->setAnnotationReader($cachedAnnotationReader);
        $evm->addEventSubscriber($sortableListener);
        // mysql set names UTF-8 if required
        //$evm->addEventSubscriber(new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit());
        // Finally, create entity manager
        return \Doctrine\ORM\EntityManager::create($dbSetting, $config, $evm);
    }

}
