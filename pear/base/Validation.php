<?php

/**
 * Validation.php
 *
 * PHP version 7
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 * 
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年3月4日 下午9:15:45
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\base;

use \Symfony\Component\Validator\Validator\ValidatorInterface;
use \Symfony\Component\Validator\ValidatorBuilder;

/**
 * Validation
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
final class Validation {

    /**
     * Creates a new validator.
     *
     * If you want to configure the validator, use
     * {@link createValidatorBuilder()} instead.
     */
    public static function createValidator(): ValidatorInterface
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(function($class){
            return class_exists($class);
        });
        return self::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    /**
     * Creates a configurable builder for validator objects.
     */
    public static function createValidatorBuilder(): ValidatorBuilder
    {
        return new ValidatorBuilder();
    }

    /**
     * This class cannot be instantiated.
     */
    private function __construct()
    {
    }
}
