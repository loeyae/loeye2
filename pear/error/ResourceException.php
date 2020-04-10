<?php

/**
 * ResourceException.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */

namespace loeye\error;

use loeye\base\Exception;

/**
 * ResourceException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ResourceException extends Exception
{

    /**
     * default error code
     */
    public const DEFAULT_ERROR_CODE = 404;

    /**
     * default error message
     */
    public const DEFAULT_ERROR_MSG = "Resource Not Found";

    public const PAGE_NOT_FOUND_CODE   = 404;
    public const PAGE_NOT_FOUND_MSG   = 'Page Not Found';

    public const MODULE_NOT_FOUND_CODE = 404;
    public const MODULE_NOT_FOUND_MSG = 'Module Not Found';

    public const MODULE_NOT_EXISTS_CODE = 404010;
    public const MODULE_NOT_EXISTS_MSG = 'Module: %module% Not Exists';

    public const BUNDLE_NOT_FOUND_CODE = 404020;
    public const BUNDLE_NOT_FOUND_MSG = 'Property Bundle: %bundle% Not Exists';

    public const FILE_NOT_FOUND_CODE   = 404030;
    public const FILE_NOT_FOUND_MSG   = 'File: %file% Not Exists';

    public const LANGUAGE_FILE_NOT_FOUND_CODE   = 404031;
    public const LANGUAGE_FILE_NOT_FOUND_MSG   = 'Language File: %file% Not Exists';

    public const RECORD_NOT_FOUND_CODE   = 404032;
    public const RECORD_NOT_FOUND_MSG   = 'Record Not Exists';

    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MSG, int $errorCode =
    self::DEFAULT_ERROR_CODE, $parameter = array())
    {
        parent::__construct($errorMessage, $errorCode, $parameter);
    }

}
