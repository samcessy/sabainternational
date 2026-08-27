<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Document;
use App\Models\Event;
use App\Models\Page;
use App\Models\Program;
use App\Models\Story;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * saba.md §17 — V1 database search across Stories, Programs, Documents
 * (reports), Pages, and Events. Deliberately plain LIKE-based matching
 * rather than MySQL MATCH/AGAINST: the test suite runs on SQLite, and a
 * driver-specific raw query would either break there or need parallel
 * per-driver branches. Relevance is approximated with a title-match
 * ranking (exact > starts-with > contains) — real relevance scoring across
 * five unrelated tables is what V2's dedicated search index is for.
 *
 * Each content type gets its own concretely-typed query/present pair
 * rather than a shared generic helper - looping over a generic Model
 * defeats Larastan's per-model column/attribute resolution (see
 * DashboardController's pendingApprovals for the same lesson).
 */
class SearchController extends Controller
{
    private const RESULTS_PER_TYPE = 5;

    private const RESULTS_PER_PAGE = 20;

    public function index(Request $request): Response
    {
        $term = trim((string) $request->string('q'));
        $type = $request->string('type')->value() ?: null;

        if ($term === '') {
            return Inertia::render('Search', [
                'query' => '',
                'type' => $type,
                'results' => [],
                'pagination' => null,
            ]);
        }

        if ($type !== null) {
            return $this->filteredResults($type, $term);
        }

        $results = [
            ...$this->storyQuery($term)->limit(self::RESULTS_PER_TYPE)->get()
                ->map(fn (Story $story) => $this->presentStory($story))->all(),
            ...$this->programQuery($term)->limit(self::RESULTS_PER_TYPE)->get()
                ->map(fn (Program $program) => $this->presentProgram($program))->all(),
            ...$this->documentQuery($term)->limit(self::RESULTS_PER_TYPE)->get()
                ->map(fn (Document $document) => $this->presentDocument($document))->all(),
            ...$this->pageQuery($term)->limit(self::RESULTS_PER_TYPE)->get()
                ->map(fn (Page $page) => $this->presentPage($page))->all(),
            ...$this->eventQuery($term)->limit(self::RESULTS_PER_TYPE)->get()
                ->map(fn (Event $event) => $this->presentEvent($event))->all(),
        ];

        return Inertia::render('Search', [
            'query' => $term,
            'type' => null,
            'results' => $results,
            'pagination' => null,
        ]);
    }

    private function filteredResults(string $type, string $term): Response
    {
        [$results, $pagination] = match ($type) {
            'story' => $this->paginate($this->storyQuery($term), fn (Story $story) => $this->presentStory($story)),
            'program' => $this->paginate($this->programQuery($term), fn (Program $program) => $this->presentProgram($program)),
            'document' => $this->paginate($this->documentQuery($term), fn (Document $document) => $this->presentDocument($document)),
            'page' => $this->paginate($this->pageQuery($term), fn (Page $page) => $this->presentPage($page)),
            'event' => $this->paginate($this->eventQuery($term), fn (Event $event) => $this->presentEvent($event)),
            default => [[], null],
        };

        return Inertia::render('Search', [
            'query' => $term,
            'type' => $type,
            'results' => $results,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @param  Closure(TModel): array{type: string, title: string, snippet: string|null, href: string}  $present
     * @return array{0: array<int, array{type: string, title: string, snippet: string|null, href: string}>, 1: array{current_page: int, last_page: int, total: int}}
     */
    private function paginate(Builder $query, Closure $present): array
    {
        $paginator = $query->paginate(self::RESULTS_PER_PAGE)->withQueryString();

        return [
            collect($paginator->items())->map($present)->all(),
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * @return Builder<Story>
     */
    private function storyQuery(string $term): Builder
    {
        return Story::query()
            ->where('status', ContentStatus::Published)
            ->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%"))
            ->orderByRaw('case when title = ? then 0 when title like ? then 1 else 2 end', [$term, "{$term}%"])
            ->orderBy('title');
    }

    /**
     * @return Builder<Program>
     */
    private function programQuery(string $term): Builder
    {
        return Program::query()
            ->where('status', ContentStatus::Published)
            ->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%")
                ->orWhere('overview', 'like', "%{$term}%"))
            ->orderByRaw('case when name = ? then 0 when name like ? then 1 else 2 end', [$term, "{$term}%"])
            ->orderBy('name');
    }

    /**
     * @return Builder<Document>
     */
    private function documentQuery(string $term): Builder
    {
        return Document::query()
            ->where('status', ContentStatus::Published)
            ->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$term}%")
                ->orWhere('summary', 'like', "%{$term}%"))
            ->orderByRaw('case when title = ? then 0 when title like ? then 1 else 2 end', [$term, "{$term}%"])
            ->orderBy('title');
    }

    /**
     * @return Builder<Page>
     */
    private function pageQuery(string $term): Builder
    {
        return Page::query()
            ->where('status', ContentStatus::Published)
            ->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%"))
            ->orderByRaw('case when title = ? then 0 when title like ? then 1 else 2 end', [$term, "{$term}%"])
            ->orderBy('title');
    }

    /**
     * @return Builder<Event>
     */
    private function eventQuery(string $term): Builder
    {
        return Event::query()
            ->where('status', ContentStatus::Published)
            ->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%"))
            ->orderByRaw('case when title = ? then 0 when title like ? then 1 else 2 end', [$term, "{$term}%"])
            ->orderBy('title');
    }

    /**
     * @return array{type: string, title: string, snippet: string|null, href: string}
     */
    private function presentStory(Story $story): array
    {
        return [
            'type' => 'story',
            'title' => $story->title,
            'snippet' => $story->excerpt,
            'href' => "/stories/{$story->slug}",
        ];
    }

    /**
     * @return array{type: string, title: string, snippet: string|null, href: string}
     */
    private function presentProgram(Program $program): array
    {
        return [
            'type' => 'program',
            'title' => $program->name,
            'snippet' => $program->short_description,
            'href' => "/programs/{$program->slug}",
        ];
    }

    /**
     * @return array{type: string, title: string, snippet: string|null, href: string}
     */
    private function presentDocument(Document $document): array
    {
        return [
            'type' => 'document',
            'title' => $document->title,
            'snippet' => $document->summary,
            'href' => "/documents/{$document->id}",
        ];
    }

    /**
     * @return array{type: string, title: string, snippet: string|null, href: string}
     */
    private function presentPage(Page $page): array
    {
        return [
            'type' => 'page',
            'title' => $page->title,
            'snippet' => $page->body ? Str::limit($page->body, 160) : null,
            'href' => "/pages/{$page->slug}",
        ];
    }

    /**
     * @return array{type: string, title: string, snippet: string|null, href: string}
     */
    private function presentEvent(Event $event): array
    {
        return [
            'type' => 'event',
            'title' => $event->title,
            'snippet' => $event->description,
            'href' => "/events/{$event->slug}",
        ];
    }
}
