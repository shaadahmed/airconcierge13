<?php

namespace App\Http\Controllers;

use App\Models\ChronologyMail;
use App\Services\Chronology\ChronologyService;
use Illuminate\Http\Response;

class ChronologyMailOpenController extends Controller
{
    public function __construct(private ChronologyService $chronologyService) {}

    public function __invoke(ChronologyMail $mail): Response
    {
        $this->chronologyService->markOpened($mail);

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif === false ? '' : $gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
