<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\ProgramCategory;
use App\Enums\ProgramRelationshipType;
use App\Models\Program;
use Illuminate\Database\Seeder;

/**
 * The four programs found on the current live site
 * (docs/audit/current-website-audit.md). Descriptions here match the
 * homepage's audit-sourced copy exactly — short_description only, not the
 * full Problem→Context→Role narrative saba.md §6.1 wants for dedicated
 * program pages, since those don't exist yet. relationship_type stays
 * 'unconfirmed' for all four pending the stakeholder verification in
 * docs/project-overview.md §6 (including whether Hunter Initiative is an
 * official program or an independent partner).
 */
class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'name' => 'New Dawn',
                'slug' => 'new-dawn',
                'category' => ProgramCategory::Education,
                'founded_year' => 2006,
                'location' => 'Nairobi, Kenya',
                'short_description' => 'An educational center and mentorship program serving vulnerable children from Nairobi slum settlements, offering academics, counseling, spiritual guidance, and meals.',
                'external_url' => 'https://newdawneducationcenter.newdawnkenya.com/',
            ],
            [
                'name' => 'Bethel Kibera School',
                'slug' => 'bethel-kibera-school',
                'category' => ProgramCategory::Education,
                'founded_year' => 2006,
                'location' => 'Kibera, Nairobi, Kenya',
                'short_description' => 'Began as a small daycare in Kibera and has grown into a full primary school offering education, food assistance, and teen mentorship.',
                'external_url' => 'https://bethelkiberaschool.com/',
            ],
            [
                'name' => 'The Nest',
                'slug' => 'the-nest',
                'category' => ProgramCategory::ShelterFamilySupport,
                'founded_year' => 1997,
                'location' => 'Kenya',
                'short_description' => 'Provides safe housing for vulnerable children and works toward rehabilitation and family reintegration.',
                'external_url' => 'https://www.thenesthome.org/en/childrens-home/',
            ],
            [
                'name' => 'The Hunter Initiative',
                'slug' => 'the-hunter-initiative',
                'category' => ProgramCategory::YouthEconomicEmpowerment,
                'founded_year' => null,
                'location' => 'Kenya',
                'short_description' => 'Delivers software development training to economically disadvantaged youth to strengthen their earning potential.',
                'external_url' => null,
            ],
        ];

        foreach ($programs as $program) {
            Program::query()->updateOrCreate(
                ['slug' => $program['slug']],
                [
                    ...$program,
                    'relationship_type' => ProgramRelationshipType::Unconfirmed,
                    'status' => ContentStatus::Published,
                    'published_at' => now(),
                ],
            );
        }
    }
}
