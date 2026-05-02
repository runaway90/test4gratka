<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function test_home_page_is_accessible_without_login(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.photo-grid, .empty-state');
    }

    public function test_home_page_filter_form_is_present(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form.filter-form');
        $this->assertSelectorExists('input[name="location"]');
        $this->assertSelectorExists('input[name="camera"]');
        $this->assertSelectorExists('input[name="description"]');
        $this->assertSelectorExists('input[name="taken_at"]');
        $this->assertSelectorExists('input[name="username"]');
    }

    public function test_home_page_with_filters_returns_200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/', ['location' => 'Paris', 'camera' => 'Canon']);

        $this->assertResponseIsSuccessful();
    }

    public function test_login_page_is_accessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="username"]');
        $this->assertSelectorExists('input[name="password"]');
    }

    public function test_profile_redirects_to_login_when_unauthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');

        $this->assertResponseRedirects('/login');
    }

    public function test_login_with_invalid_credentials_shows_error(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login', [
            'username' => 'nonexistent_user',
            'password' => 'wrong_password',
        ]);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.flash-message.error');
    }
}
