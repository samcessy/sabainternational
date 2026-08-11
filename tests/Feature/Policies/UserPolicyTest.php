<?php

use App\Enums\AdminRole;
use App\Models\User;

test('a super administrator can delete another user', function () {
    $superAdmin = User::factory()->create(['admin_role' => AdminRole::SuperAdministrator]);
    $otherUser = User::factory()->create();

    expect($superAdmin->can('delete', $otherUser))->toBeTrue();
});

test('a super administrator cannot delete their own account', function () {
    $superAdmin = User::factory()->create(['admin_role' => AdminRole::SuperAdministrator]);

    expect($superAdmin->can('delete', $superAdmin))->toBeFalse();
});

test('a viewer cannot delete any user, including themselves', function () {
    $viewer = User::factory()->create(['admin_role' => AdminRole::Viewer]);
    $otherUser = User::factory()->create();

    expect($viewer->can('delete', $otherUser))->toBeFalse()
        ->and($viewer->can('delete', $viewer))->toBeFalse();
});
