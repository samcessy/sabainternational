<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

/**
 * Team members found on the current live site
 * (docs/audit/current-website-audit.md). Bios are the live site's own
 * copy, not expanded or embellished — Scott Organ's bio mentions adopting
 * children from The Nest because that's already public on the live site
 * (saba.md §1.3 treats it as source of truth), but nothing beyond what's
 * already published is added, per the consent-sensitivity flag in
 * docs/audit/content-inventory.md.
 *
 * `board_member` is left false except for Helen Kahl (Treasurer is a board
 * officer role in virtually every nonprofit structure) — actual board
 * composition is one of the unresolved stakeholder-verification items in
 * docs/project-overview.md §6, not something to guess at for the others.
 *
 * Sammy Tongoi is deliberately NOT given a bio and stays in Draft status —
 * the audit found his bio was literally "TBD" on the live site (F-9). This
 * is a content-honesty choice, not a demonstration of the publish guard:
 * DatabaseSeeder uses WithoutModelEvents, which suppresses the `saving`
 * event entirely during seeding, so the guard itself doesn't actually fire
 * here regardless of what status this record is given. The guard is
 * correctly tested in tests/Feature/ContentEntitiesTest.php instead, where
 * events are not suppressed.
 */
class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name' => 'Tim & Cathy Woller',
                'role' => 'Founders',
                'bio' => 'Tim and Cathy Woller established Saba International in 2009 after Tim\'s U.S. Air Force assignment brought the couple to Kenya from 2005 to 2008, where they built the relationships that became Saba\'s founding partnerships.',
                'board_member' => false,
                'display_order' => 1,
            ],
            [
                'name' => 'Scott Organ',
                'role' => 'Ambassador',
                'bio' => 'Scott joined Saba\'s board in 2011. He and his family adopted twins from The Nest, giving him a personal connection to Saba\'s work that continues to shape his involvement.',
                'board_member' => false,
                'display_order' => 2,
            ],
            [
                'name' => 'Ryan Shaw',
                'role' => 'Ambassador',
                'bio' => 'Ryan became a board member in 2015, called to serve orphans and widows through Saba\'s programs.',
                'board_member' => false,
                'display_order' => 3,
            ],
            [
                'name' => 'Helen Kahl',
                'role' => 'Treasurer',
                'bio' => 'A librarian by profession, Helen has been involved with Saba since 2007, including donating school uniforms to students in Saba\'s partner programs.',
                'board_member' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Samuel Chege',
                'role' => 'Software Engineer',
                'bio' => 'Samuel graduated from New Dawn in 2010 and went on to earn a degree from Kenya Methodist University. He was trained through Saba\'s programs and now works as a software engineer.',
                'board_member' => false,
                'display_order' => 5,
            ],
        ];

        foreach ($members as $member) {
            TeamMember::query()->updateOrCreate(
                ['name' => $member['name']],
                [
                    ...$member,
                    'consent_to_publish' => true,
                    'status' => ContentStatus::Published,
                ],
            );
        }

        // See class docblock — bio intentionally left null, status stays
        // Draft. The publish guard would reject this record if this seeder
        // tried to mark it Published without a bio.
        TeamMember::query()->updateOrCreate(
            ['name' => 'Sammy Tongoi'],
            [
                'role' => 'Advisor',
                'bio' => null,
                'consent_to_publish' => false,
                'display_order' => 6,
                'status' => ContentStatus::Draft,
            ],
        );
    }
}
