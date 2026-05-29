<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateContactInquiry extends CreateRecord
{
    protected static string $resource = ContactInquiryResource::class;
}
