<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testAccountCrud(): void
    {
        $this->listAccounts();
        $this->createAccount();
        $this->editAccount();
        $this->deleteAccount();
    }

    private function listAccounts(): void
    {
        $this->client->request('GET', '/account/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Account list');
        $this->assertSelectorTextNotContains('td', 'Account 1');
    }

    private function createAccount(): void
    {
        $this->client->request('GET', '/account/');
        $crawler = $this->client->clickLink('Create a new account');

        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $this->client->submit($form, [
            'account[name]' => 'Account 1',
            'account[currency]' => 'EUR'
        ]);

        // Check redirection
        $response = $this->client->getResponse();
        $this->assertResponseRedirects();
        $this->assertEquals('/account/', $response->getTargetUrl());
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('td', 'Account 1');
    }

    private function editAccount(): void
    {
        // open account list page
        $crawler = $this->client->request('GET', '/account/');
        $this->assertResponseIsSuccessful();

        // open edit page
        $link = $crawler->filter('td > a')->first()->link();
        $uri = $link->getUri();
        $crawler = $this->client->request('GET', $uri);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Edit this account');

        // Check we have the right account
        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $accountName = $form->get('account[name]')->getValue();
        $this->assertEquals('Account 1', $accountName);

        // Edit the account
        $this->client->submit($form, [
            'account[name]' => 'Account edited',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects();

        // Ensure the acount is edited
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextNotContains('td', 'Account 1');
        $this->assertSelectorTextContains('td', 'Account edited');
    }

    private function deleteAccount(): void
    {
        // open account list page
        $crawler = $this->client->request('GET', '/account/');
        $this->assertResponseIsSuccessful();

        // open edit page
        $link = $crawler->filter('td > a')->first()->link();
        $uri = $link->getUri();
        $crawler = $this->client->request('GET', $uri);
        $this->assertResponseIsSuccessful();

        // Check we have the right account
        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $accountName = $form->get('account[name]')->getValue();
        $this->assertEquals('Account edited', $accountName);

        // Delete the account
        $buttonCrawlerNode = $crawler->selectButton('Delete this account');
        $form = $buttonCrawlerNode->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();

        // Ensure the acount is not in the list
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextNotContains('td', 'Account edited');
    }
}
