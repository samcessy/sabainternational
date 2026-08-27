<?php

use App\Models\Redirect;

test('a request to a redirected path issues the configured redirect', function () {
    Redirect::factory()->create([
        'from_path' => '/old-mission',
        'to_path' => '/pages/our-mission',
        'status_code' => 301,
    ]);

    $response = $this->get('/old-mission');

    $response->assertRedirect('/pages/our-mission');
    $response->assertStatus(301);
});

test('a request to an unmapped path falls through to 404', function () {
    $this->get('/definitely-not-a-real-path')->assertNotFound();
});
