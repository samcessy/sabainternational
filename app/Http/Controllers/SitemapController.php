<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Document;
use App\Models\Event;
use App\Models\Page;
use App\Models\Program;
use App\Models\Story;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use SimpleXMLElement;

/**
 * saba.md §15.2 — "/sitemap.xml (auto-generated, cached daily)". Static
 * marketing routes (Home, About, ...) are listed alongside every published
 * Program/Story/Page/Document/Event so nothing indexable is missing.
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addDay(), fn () => $this->buildXml());

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function buildXml(): string
    {
        $urls = [
            ...$this->staticUrls(),
            ...$this->programUrls(),
            ...$this->storyUrls(),
            ...$this->pageUrls(),
            ...$this->documentUrls(),
            ...$this->eventUrls(),
        ];

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($urls as $entry) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($entry['loc'], ENT_XML1));

            if ($entry['lastmod'] !== null) {
                $url->addChild('lastmod', $entry['lastmod']);
            }

            $url->addChild('changefreq', $entry['changefreq']);
            $url->addChild('priority', $entry['priority']);
        }

        return (string) $xml->asXML();
    }

    /**
     * @return array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string}>
     */
    private function staticUrls(): array
    {
        $routes = [
            ['route' => 'home', 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['route' => 'about.show', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['route' => 'give.show', 'changefreq' => 'monthly', 'priority' => '0.9'],
            ['route' => 'contact.show', 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['route' => 'volunteer.show', 'changefreq' => 'yearly', 'priority' => '0.6'],
            ['route' => 'partnership.show', 'changefreq' => 'yearly', 'priority' => '0.6'],
            ['route' => 'programs.index', 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['route' => 'stories.index', 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['route' => 'documents.index', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['route' => 'events.index', 'changefreq' => 'weekly', 'priority' => '0.7'],
        ];

        return array_map(fn (array $route) => [
            'loc' => route($route['route']),
            'lastmod' => null,
            'changefreq' => $route['changefreq'],
            'priority' => $route['priority'],
        ], $routes);
    }

    /**
     * @return array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string}>
     */
    private function programUrls(): array
    {
        return Program::query()
            ->where('status', ContentStatus::Published)
            ->get(['slug', 'updated_at'])
            ->map(fn (Program $program) => [
                'loc' => route('programs.show', $program->slug),
                'lastmod' => $program->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ])
            ->all();
    }

    /**
     * @return array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string}>
     */
    private function storyUrls(): array
    {
        return Story::query()
            ->where('status', ContentStatus::Published)
            ->get(['slug', 'updated_at'])
            ->map(fn (Story $story) => [
                'loc' => route('stories.show', $story->slug),
                'lastmod' => $story->updated_at?->toAtomString(),
                'changefreq' => 'yearly',
                'priority' => '0.6',
            ])
            ->all();
    }

    /**
     * @return array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string}>
     */
    private function pageUrls(): array
    {
        return Page::query()
            ->where('status', ContentStatus::Published)
            ->get(['slug', 'updated_at'])
            ->map(fn (Page $page) => [
                'loc' => route('pages.show', $page->slug),
                'lastmod' => $page->updated_at?->toAtomString(),
                'changefreq' => 'yearly',
                'priority' => '0.5',
            ])
            ->all();
    }

    /**
     * @return array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string}>
     */
    private function documentUrls(): array
    {
        return Document::query()
            ->where('status', ContentStatus::Published)
            ->get(['id', 'updated_at'])
            ->map(fn (Document $document) => [
                'loc' => route('documents.show', $document->id),
                'lastmod' => $document->updated_at?->toAtomString(),
                'changefreq' => 'yearly',
                'priority' => '0.6',
            ])
            ->all();
    }

    /**
     * @return array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string}>
     */
    private function eventUrls(): array
    {
        return Event::query()
            ->where('status', ContentStatus::Published)
            ->get(['slug', 'updated_at'])
            ->map(fn (Event $event) => [
                'loc' => route('events.show', $event->slug),
                'lastmod' => $event->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ])
            ->all();
    }
}
