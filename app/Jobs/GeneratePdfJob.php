<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Stub for Phase 4 PDF generation (ADR-005).
 *
 * Intended future consumer: payment receipt PDFs via PdfService / DomPDF.
 */
class GeneratePdfJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?string $documentKey = null) {}

    public function handle(): void
    {
        Log::info('GeneratePdfJob stub — PDF generation deferred to Phase 4.', [
            'document_key' => $this->documentKey,
        ]);
    }
}
