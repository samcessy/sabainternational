<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentRequest;
use App\Http\Requests\Admin\UpdateDocumentRequest;
use App\Models\Document;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Document::class);

        return Inertia::render('admin/documents/Index', [
            'documents' => Document::query()
                ->latest()
                ->paginate(20)
                ->through(fn (Document $document) => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'document_type_label' => $document->document_type->label(),
                    'year' => $document->year,
                    'status' => $document->status->value,
                    'status_label' => $document->status->label(),
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Document::class);

        return Inertia::render('admin/documents/Create', $this->formOptions());
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === ContentStatus::Published->value) {
            $data['published_at'] = now();
        }

        $document = Document::create($data);

        $this->auditLogger->log($request->user(), 'create', $document, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$document->title}\" was created.",
        ]);

        return to_route('admin.documents.index');
    }

    public function edit(Document $document): Response
    {
        $this->authorize('update', $document);

        return Inertia::render('admin/documents/Edit', [
            'document' => [
                ...$document->only(['id', 'title', 'document_type', 'year', 'summary', 'file_media_id', 'cover_image_media_id', 'status']),
                'file_thumbnail_url' => $document->file?->thumbnailUrl(),
                'file_name' => $document->file?->filename,
                'cover_image_thumbnail_url' => $document->coverImage?->thumbnailUrl(),
            ],
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $oldValues = $document->only(array_keys($request->validated()));
        $data = $request->validated();

        if ($data['status'] === ContentStatus::Published->value && $document->published_at === null) {
            $data['published_at'] = now();
        }

        $document->update($data);

        $this->auditLogger->log($request->user(), 'update', $document, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$document->title}\" was updated.",
        ]);

        return to_route('admin.documents.index');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $title = $document->title;
        $this->auditLogger->log(request()->user(), 'delete', $document, oldValues: $document->only(['title', 'status']));
        $document->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$title}\" was deleted.",
        ]);

        return to_route('admin.documents.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'documentTypeOptions' => DocumentType::options(),
            'statusOptions' => ContentStatus::options(),
        ];
    }
}
