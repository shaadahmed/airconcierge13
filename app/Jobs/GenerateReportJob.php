<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Pdf\PdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateReportJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public string $reportType,
        public array $filters = [],
        public bool $asPdf = true,
    ) {
        $this->timeout = 300;
    }

    public function handle(PdfService $pdfService): void
    {
        Log::info('GenerateReportJob starting.', $this->jobLogContext([
            'report_type' => $this->reportType,
            'as_pdf' => $this->asPdf,
        ]));

        if ($this->asPdf) {
            $pdfService->generateQueuedDocument('report:'.$this->reportType, $this->filters);
        }
    }
}
