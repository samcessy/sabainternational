<?php

use App\Models\NewsletterSubscriber;
use Inertia\Testing\AssertableInertia as Assert;

test('a nonexistent route renders the branded 404 page', function () {
    $response = $this->get('/this-page-does-not-exist');

    $response->assertNotFound();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Error')
        ->where('status', 404)
    );
});

test('an unsigned newsletter unsubscribe link renders the branded 403 page', function () {
    $subscriber = NewsletterSubscriber::factory()->create();

    $response = $this->get("/newsletter/unsubscribe/{$subscriber->id}");

    $response->assertForbidden();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Error')
        ->where('status', 403)
    );
});
