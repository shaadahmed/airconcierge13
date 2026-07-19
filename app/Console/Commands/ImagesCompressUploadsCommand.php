<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\StubDomainCommand;
use Illuminate\Console\Command;

class ImagesCompressUploadsCommand extends Command
{
    use StubDomainCommand;

    protected $signature = 'images:compress-uploads {--limit=1000 : Max images to process when implemented}';

    protected $description = 'Phase 1 stub — implement in the matching domain phase.';

    public function handle(): int
    {
        return $this->reportStub();
    }
}
