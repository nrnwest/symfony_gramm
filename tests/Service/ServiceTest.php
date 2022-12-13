<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\AppService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServiceTest extends KernelTestCase
{

    private AppService $service;

    public function testDelUser(): void
    {
        $this->service->delUser(1);
        $this->assertEquals($this->service->getUser(1), null);
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->service = new AppService($kernel->getContainer()->get('doctrine'));
    }
}
