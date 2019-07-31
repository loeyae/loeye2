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

/**
 * ResourceException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ResourceException extends \loeye\base\Exception
{

    /**
     * default error code
     */
    const DEFAULT_ERROR_CODE = 404;

    /**
     * default error message
     */
    const DEFAULT_ERROR_MSG = "Resource Not Found";

    const PAGE_NOT_FOUND_CODE   = 404;
    const PAGE_NOT_FOUND_MSG   = "Page Not Found";

    const MODULE_NOT_FOUND_CODE = 404;
    const MODULE_NOT_FOUND_MSG = "Module Not Found";

    const MODULE_NOT_EXISTS_CODE = 404010;
    const MODULE_NOT_EXISTS_MSG = "Module Not Exists";

    const BUNDLE_NOT_FOUND_CODE = 404020;
    const BUMDLE_NOT_FOUND_MSG = "Bundle Not Exists";

    const FILE_NOT_FOUND_CODE   = 404030;
    const FILE_NOT_FOUND_MSG   = "File Not Exists";

    const LANGUAGE_FILE_NOT_FOUND_CODE   = 404031;
    const LANGUAGE_FILE_NOT_FOUND_MSG   = "Language File Not Exists";

    const RECORD_NOT_FOUND_CODE   = 404032;
    const RECORD_NOT_FOUND_MSG   = "Record Not Exists";

}
