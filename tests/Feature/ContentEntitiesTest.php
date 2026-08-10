<?php

use App\Enums\ConsentStatus;
use App\Enums\ContentStatus;
use App\Enums\ProgramCategory;
use App\Enums\VerificationStatus;
use App\Models\AuditLog;
use App\Models\Campaign;
use App\Models\ContactSubmission;
use App\Models\Document;
use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\Event;
use App\Models\ImpactMetric;
use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\PartnershipInquiry;
use App\Models\Program;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\Story;
use App\Models\StoryTag;
use App\Models\Supporter;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\VolunteerApplication;
use Database\Factories\ImpactMetricValueFactory;
use Illuminate\Database\QueryException;

test('program has a category cast to an enum', function () {
    $program = Program::factory()->create(['category' => ProgramCategory::Education]);

    expect($program->fresh()->category)->toBe(ProgramCategory::Education);
});

test('media has variants', function () {
    $media = Media::factory()->create();
    MediaVariant::factory()->for($media)->create();

    expect($media->variants)->toHaveCount(1);
});

test('page belongs to an author', function () {
    $user = User::factory()->create();
    $page = Page::factory()->for($user, 'author')->create();

    expect($page->author->is($user))->toBeTrue();
});

test('a team member cannot be published without a bio', function () {
    $teamMember = TeamMember::factory()->make([
        'bio' => null,
        'status' => ContentStatus::Published,
    ]);

    expect(fn () => $teamMember->save())->toThrow(RuntimeException::class);
});

test('a team member can be published with a bio', function () {
    $teamMember = TeamMember::factory()->published()->create(['bio' => 'A real bio.']);

    expect($teamMember->fresh()->status)->toBe(ContentStatus::Published);
});

test('a story cannot be published without a consent status', function () {
    $story = Story::factory()->make([
        'consent_status' => null,
        'status' => ContentStatus::Published,
    ]);

    expect(fn () => $story->save())->toThrow(RuntimeException::class);
});

test('a story can be published once consent status is set', function () {
    $story = Story::factory()->requiresConsent()->published()->create();

    expect($story->fresh()->status)->toBe(ContentStatus::Published)
        ->and($story->consent_status)->toBe(ConsentStatus::Yes);
});

test('a story can be tagged', function () {
    $story = Story::factory()->create();
    $tag = StoryTag::factory()->create();

    $story->tags()->attach($tag);

    expect($story->fresh()->tags)->toHaveCount(1);
});

test('impact metric exposes only verified values as the latest verified value', function () {
    $metric = ImpactMetric::factory()->create();
    $metric->values()->create([
        ...ImpactMetricValueFactory::new()->definition(),
        'metric_id' => $metric->id,
        'verification_status' => VerificationStatus::Unverified,
    ]);

    expect($metric->latestVerifiedValue())->toBeNull();

    $metric->values()->create([
        ...ImpactMetricValueFactory::new()->definition(),
        'metric_id' => $metric->id,
        'verification_status' => VerificationStatus::Verified,
    ]);

    expect($metric->latestVerifiedValue())->not->toBeNull();
});

test('document requires a file media record', function () {
    $document = Document::factory()->create();

    expect($document->file)->toBeInstanceOf(Media::class);
});

test('event can be created', function () {
    $event = Event::factory()->create();

    expect($event->exists)->toBeTrue();
});

test('campaign has suggested amounts stored as cents', function () {
    $campaign = Campaign::factory()->create();

    expect($campaign->suggested_amounts)->toBeArray()
        ->and($campaign->suggested_amounts[0])->toBeInt();
});

test('a donation belongs to a supporter and can have transactions', function () {
    $supporter = Supporter::factory()->create();
    $donation = Donation::factory()->for($supporter)->succeeded()->create(['amount_cents' => 5000]);
    DonationTransaction::factory()->for($donation)->create();

    expect($donation->supporter->is($supporter))->toBeTrue()
        ->and($donation->transactions)->toHaveCount(1)
        ->and($donation->amount_cents)->toBe(5000);
});

test('donation transaction gateway reference is unique', function () {
    $transaction = DonationTransaction::factory()->create(['gateway_reference' => 'pi_duplicate']);

    expect(fn () => DonationTransaction::factory()->create(['gateway_reference' => 'pi_duplicate']))
        ->toThrow(QueryException::class);
});

test('newsletter subscriber, contact submission, volunteer application, and partnership inquiry can be created', function () {
    expect(NewsletterSubscriber::factory()->create()->exists)->toBeTrue()
        ->and(ContactSubmission::factory()->create()->exists)->toBeTrue()
        ->and(VolunteerApplication::factory()->create()->exists)->toBeTrue()
        ->and(PartnershipInquiry::factory()->create()->exists)->toBeTrue();
});

test('redirect from_path is unique', function () {
    Redirect::factory()->create(['from_path' => '/old-page']);

    expect(fn () => Redirect::factory()->create(['from_path' => '/old-page']))
        ->toThrow(QueryException::class);
});

test('audit log records an action against a user', function () {
    $log = AuditLog::factory()->create(['action' => 'publish']);

    expect($log->user)->not->toBeNull();
});

test('setting can be get and set', function () {
    Setting::set('site_name', 'Saba International');

    expect(Setting::get('site_name'))->toBe('Saba International')
        ->and(Setting::get('missing_key', 'fallback'))->toBe('fallback');
});
