<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\CoreBundle\Model;

if (!class_exists(\Sonata\Doctrine\Model\BaseManager::class, false)) {
    @trigger_error(
        'The '.__NAMESPACE__.'\BaseManager class is deprecated since version 3.x and will be removed in 4.0.'
        .' Use Sonata\Doctrine\Model\BaseManager instead.',
        E_USER_DEPRECATED
    );
}

class_alias(
    \Sonata\Doctrine\Model\BaseManager::class,
    __NAMESPACE__.'\BaseManager'
);

if (false) {
    /**
     * @author Hugo Briand <briand@ekino.com>
     *
     * @deprecated since 3.x, to be removed in 4.0.
     */
    abstract class BaseManager extends \Sonata\Doctrine\Model\BaseManager implements ManagerInterface
    {
    }
}
