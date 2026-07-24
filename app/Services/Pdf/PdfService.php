<?php

namespace App\Services\Pdf;

use App\Models\BookingPayment;
use App\Services\Payments\PaymentService;
use App\Services\Reports\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

/**
 * DomPDF wrapper and queued document generation (ADR-005).
 *
 * Controllers must not generate PDFs — dispatch GeneratePdfJob instead.
 */
class PdfService
{
    public function __construct(
        private PaymentService $paymentService,
        private ReportService $reportService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function loadView(string $view, array $data = []): DomPdf
    {
        return Pdf::loadView($view, $data);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function generateQueuedDocument(string $documentKey, array $options = []): string
    {
        if (str_starts_with($documentKey, 'payment-receipt:')) {
            $paymentId = (int) substr($documentKey, strlen('payment-receipt:'));

            return $this->generatePaymentReceipt($paymentId);
        }

        if (str_starts_with($documentKey, 'report:')) {
            $reportType = substr($documentKey, strlen('report:'));

            return $this->generateReportPdf($reportType, $options);
        }

        throw new InvalidArgumentException("Unknown PDF document key [{$documentKey}].");
    }

    public function generatePaymentReceipt(int $bookingPaymentId): string
    {
        $payment = BookingPayment::query()
            ->with(['booking', 'paymentType', 'receipt'])
            ->findOrFail($bookingPaymentId);

        $path = "receipts/payment-{$payment->id}.pdf";

        // Idempotent: reuse existing stored receipt when present.
        if ($payment->receipt !== null && filled($payment->receipt->receipt_path)
            && Storage::disk('local')->exists($payment->receipt->receipt_path)) {
            return $payment->receipt->receipt_path;
        }

        $pdf = $this->loadView('pdf.payment-receipt', [
            'payment' => $payment,
        ]);

        Storage::disk('local')->put($path, $pdf->output());

        $this->paymentService->recordReceiptPath($payment, $path);

        return $path;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function generateReportPdf(string $reportType, array $filters = []): string
    {
        $data = match ($reportType) {
            'booking-summary' => $this->reportService->bookingSummary($filters),
            'tot' => $this->reportService->totReport($filters),
            'metrics' => $this->reportService->propertyMetrics($filters),
            default => throw new InvalidArgumentException("Unknown report type [{$reportType}]."),
        };

        $path = sprintf('reports/%s-%s.pdf', $reportType, now()->format('YmdHis'));

        $pdf = $this->loadView('pdf.report', [
            'reportType' => $reportType,
            'rows' => $data,
            'filters' => $filters,
        ]);

        if (! Storage::disk('local')->put($path, $pdf->output())) {
            throw new RuntimeException("Failed to store report PDF at [{$path}].");
        }

        return $path;
    }
}
