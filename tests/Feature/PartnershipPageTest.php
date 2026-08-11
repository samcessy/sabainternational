<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the partnership page renders', function () {
    $this->get(route('partnership.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Partner'));
});
