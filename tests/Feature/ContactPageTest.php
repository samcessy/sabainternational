<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the contact page renders', function () {
    $this->get(route('contact.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Contact'));
});
