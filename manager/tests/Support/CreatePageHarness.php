<?php

namespace Tests\Support;

use App\Traits\Filament\Generals\Pages\CreatePage;

class CreatePageHarness
{
    use CreatePage;

    public function beforeCreatePublic(array $data): array
    {
        return $this->mutateFormDataBeforeCreate($data);
    }
}
