<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Email\StoreEmailRequest;
use App\Models\Chronology;
use App\Models\DocumentUpload;
use App\Models\EmailTemplate;
use App\Models\MailSend;
use App\Models\Owner;
use App\Services\Email\EmailService;
use App\Services\Zoho\ZohoSignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailController extends Controller
{
    public function __construct(
        private EmailService $emailService,
        private ZohoSignService $zohoSignService,
    ) {}

    public function create(): View
    {
        $this->authorize('viewAny', Chronology::class);

        return view('admin.send-emails.create', [
            'owners' => Owner::query()->notDeleted()->orderBy('full_name')->get(),
            'templates' => EmailTemplate::query()->orderBy('name')->get(),
            'documents' => DocumentUpload::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreEmailRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $owner = Owner::query()->findOrFail($data['owner_id']);
        $document = isset($data['document_id']) ? DocumentUpload::query()->find($data['document_id']) : null;
        $template = isset($data['template_id']) ? EmailTemplate::query()->find($data['template_id']) : null;

        $mailSend = MailSend::query()->create([
            'owner_id' => $owner->id,
            'properties_id' => $data['property_id'] ?? null,
            'document_id' => $document?->id,
            'template_id' => $template?->id,
            'documentname' => $document?->document,
            'is_opened' => 0,
            'created_date' => now(),
        ]);

        if ($document?->requiresZohoSign()) {
            $response = $this->zohoSignService->createDocumentFromTemplate((string) $document->signid, [
                'templates' => [
                    'template_id' => $document->signid,
                    'actions' => [
                        [
                            'action_id' => $document->zohoactionid,
                            'recipient_name' => $owner->full_name ?? $owner->first_name,
                            'recipient_email' => $owner->owner_email,
                        ],
                    ],
                ],
            ]);

            $this->zohoSignService->recordSignatureRequest(
                $owner,
                (string) $document->signid,
                $response,
                filename: $document->document,
            );
        } else {
            $this->emailService->send([
                'to' => (string) $owner->owner_email,
                'subject' => $data['subject'] ?? $template?->templatesubject ?? 'Message from Air Concierge',
                'body' => $data['body'] ?? $template?->description ?? '',
                'source' => 'send-emails',
                'metadata' => ['mail_send_id' => $mailSend->id],
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['data' => $mailSend], 201);
        }

        return redirect()
            ->route('admin.send-emails.create')
            ->with('status', 'Email / signature request sent.');
    }
}
