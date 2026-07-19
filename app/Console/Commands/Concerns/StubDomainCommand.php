<?php

namespace App\Console\Commands\Concerns;

trait StubDomainCommand
{
    protected function reportStub(): int
    {
        $this->components->info(sprintf(
            '%s: stub only — implement in the matching domain phase.',
            $this->getName() ?? static::class,
        ));

        return self::SUCCESS;
    }
}
