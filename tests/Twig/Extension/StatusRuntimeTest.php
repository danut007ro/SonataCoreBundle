<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\CoreBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\CoreBundle\Component\Status\StatusClassRendererInterface;
use Sonata\CoreBundle\Twig\Extension\StatusRuntime;

class StatusRuntimeTest extends TestCase
{
    public function testStatusClassDefaultValue(): void
    {
        $runtime = new StatusRuntime();
        $statusService = $this->createMock(StatusClassRendererInterface::class);

        $statusService->expects($this->once())
            ->method('handlesObject')
            ->will($this->returnValue(false));

        $runtime->addStatusService($statusService);
        $this->assertSame('test-value', $runtime->statusClass(new \stdClass(), 'getStatus', 'test-value'));
    }
}
