<?php

namespace App\Services\Owners;

use App\Models\DynamicContent;
use App\Models\OwnerTermsAgreement;
use App\Models\User;
use App\Services\Email\EmailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class OwnerTermsService
{
    public function __construct(private EmailService $emailService) {}

    /**
     * @return array{content: string, agreed: bool, content_updated_at: string|null, page_id: string}
     */
    public function show(User $user): array
    {
        $page = DynamicContent::query()
            ->where('page_id', DynamicContent::OWNER_AGREEMENT_PAGE_ID)
            ->first();

        return [
            'page_id' => DynamicContent::OWNER_AGREEMENT_PAGE_ID,
            'content' => $page?->decodedContent() ?? '',
            'agreed' => $user->hasAgreedToTerms(),
            'content_updated_at' => $page?->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{agreed: bool, redirect_to: string}
     */
    public function agree(User $user): array
    {
        OwnerTermsAgreement::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['agreed_terms' => true],
        );

        $this->notifyAgreement($user, agreed: true);

        return [
            'agreed' => true,
            'redirect_to' => '/admin/dashboard',
        ];
    }

    /**
     * @return array{agreed: bool, logout: bool, redirect_to: string}
     */
    public function disagree(User $user): array
    {
        OwnerTermsAgreement::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['agreed_terms' => false],
        );

        $this->notifyAgreement($user, agreed: false);

        return [
            'agreed' => false,
            'logout' => true,
            'redirect_to' => '/login',
        ];
    }

    private function notifyAgreement(User $user, bool $agreed): void
    {
        $page = DynamicContent::query()
            ->where('page_id', DynamicContent::OWNER_AGREEMENT_PAGE_ID)
            ->first();

        $properties = $user->owners()
            ->with('properties')
            ->get()
            ->flatMap(fn ($owner) => $owner->properties)
            ->map(fn ($property) => trim(implode(' ', array_filter([
                $property->street_address,
                $property->city,
                $property->state,
                $property->zipcode,
            ]))))
            ->filter()
            ->values()
            ->all();

        $propertyList = implode(', ', $properties);
        $updatedAt = $page?->updated_at instanceof Carbon
            ? $page->updated_at->format('m/d/Y g:i A')
            : now()->format('m/d/Y g:i A');

        $body = view('emails.owner-terms-agreement', [
            'username' => $user->email,
            'email' => $user->email,
            'firstname' => $user->name,
            'lastname' => '',
            'datetime' => $updatedAt,
            'agreement' => $agreed ? 1 : 0,
            'properties' => $propertyList,
            'termsOfUseUrl' => config('owner_terms.terms_of_use_url'),
        ])->render();

        $mailData = [
            'to' => $agreed ? config('owner_terms.agree_to') : config('owner_terms.disagree_to'),
            'bcc' => $agreed ? config('owner_terms.agree_bcc') : config('owner_terms.disagree_bcc'),
            'from' => config('owner_terms.from'),
            'subject' => $agreed
                ? 'There was an agreement of the updated terms'
                : 'There was a Non acceptance of the updated terms',
            'body' => $body,
            'source' => 'owner-terms',
            'metadata' => [
                'user_id' => $user->id,
                'agreed' => $agreed,
            ],
        ];

        if ($agreed && $page !== null) {
            $pdf = Pdf::loadView('pdf.owner-terms-agreement', [
                'content' => $page->decodedContent(),
                'mode' => 'email',
            ]);

            $attachmentName = sprintf(
                '%s, %s, %s, Agreement of Updated Terms.pdf',
                $user->name ?: 'Owner',
                $properties[0] ?? 'Property',
                now()->format('m/d/Y'),
            );

            $mailData['attachments'] = [[
                'fileName' => $attachmentName,
                'fileContent' => $pdf->output(),
            ]];
        }

        $this->emailService->send($mailData, queue: true);
    }
}
