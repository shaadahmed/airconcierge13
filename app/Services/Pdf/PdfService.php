<?php

namespace App\Services\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

/**
 * Thin DomPDF wrapper (ADR-005).
 *
 * Future consumer: payment receipt PDFs (Phase 4). Controllers must not generate PDFs yet.
 */
class PdfService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function loadView(string $view, array $data = []): DomPdf
    {
        return Pdf::loadView($view, $data);
    }
}
