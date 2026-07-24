<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Pdf\PdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Async PDF generation (ADR-005 DomPDF via PdfService).
 *
 * documentKey formats:
 * - "payment-receipt:{bookingPaymentId}"
 * - "report:{reportType}" (optional filters in $options)
 */
class GeneratePdfJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public string $documentKey,
        public array $options = [],
    ) {}

    public function handle(PdfService $pdfService): void
    {
        Log::info('GeneratePdfJob rendering document.', $this->jobLogContext([
            'document_key' => $this->documentKey,
        ]));

        $pdfService->generateQueuedDocument($this->documentKey, $this->options);
    }
}
