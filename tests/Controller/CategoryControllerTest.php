<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testCategoryCrud(): void
    {
        $this->listCategories();
    }

    private function listCategories(): void
    {
        $this->client->request('GET', '/category/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Category list');
        $this->assertSelectorTextContains('h2', 'Expenses');
        $this->assertSelectorTextContains('span', 'Accommodation');
    }
}
