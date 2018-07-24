<?php

/**
 * cli-config.php
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once 'bootstrap.php';

$entityManager = GetEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
