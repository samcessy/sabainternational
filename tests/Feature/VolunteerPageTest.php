<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the volunteer page renders', function () {
    $this->get(route('volunteer.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Volunteer'));
});
