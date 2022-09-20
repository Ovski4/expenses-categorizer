<?php

namespace App\Test\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testTagCrud(): void
    {
        $this->listTags();
        $this->createTag();
        $this->editTag();
        $this->deleteTag();
    }

    private function listTags(): void
    {
        $this->client->request('GET', '/tag/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tag list');
        $this->assertSelectorTextNotContains('td', 'Tag 1');
    }

    private function createTag(): void
    {
        $this->client->request('GET', '/tag/');
        $crawler = $this->client->clickLink('Create a new tag');

        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $this->client->submit($form, [
            'tag[name]' => 'Tag 1',
        ]);

        // Check redirection
        $response = $this->client->getResponse();
        $this->assertResponseRedirects();
        $this->assertEquals('/tag/', $response->getTargetUrl());
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('td', 'Tag 1');
    }

    private function editTag(): void
    {
        // open Tag list page
        $crawler = $this->client->request('GET', '/tag/');
        $this->assertResponseIsSuccessful();

        // open edit page
        $link = $crawler->filter('td > a')->first()->link();
        $uri = $link->getUri();
        $crawler = $this->client->request('GET', $uri);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Edit this tag');

        // Check we have the right Tag
        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $tagName = $form->get('tag[name]')->getValue();
        $this->assertEquals('Tag 1', $tagName);

        // Edit the Tag
        $this->client->submit($form, [
            'tag[name]' => 'Tag edited',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects();

        // Ensure the acount is edited
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextNotContains('td', 'Tag 1');
        $this->assertSelectorTextContains('td', 'Tag edited');
    }

    private function deleteTag(): void
    {
        // open Tag list page
        $crawler = $this->client->request('GET', '/tag/');
        $this->assertResponseIsSuccessful();

        // open edit page
        $link = $crawler->filter('td > a')->first()->link();
        $uri = $link->getUri();
        $crawler = $this->client->request('GET', $uri);
        $this->assertResponseIsSuccessful();

        // Check we have the right Tag
        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $tagName = $form->get('tag[name]')->getValue();
        $this->assertEquals('Tag edited', $tagName);

        // Delete the Tag
        $buttonCrawlerNode = $crawler->selectButton('Delete this tag');
        $form = $buttonCrawlerNode->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();

        // Ensure the acount is not in the list
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextNotContains('td', 'Tag edited');
    }
}
