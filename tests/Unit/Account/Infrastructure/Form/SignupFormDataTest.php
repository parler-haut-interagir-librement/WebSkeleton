<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Infrastructure\Form;

use App\Account\Infrastructure\Form\SignupFormData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignupFormData::class)]
final class SignupFormDataTest extends TestCase
{
    public function testTheEmailAddressIsNormalizedAsTheAccountKeepsIt(): void
    {
        $data = new SignupFormData();
        $data->email = ' Ada@Example.COM ';
        self::assertSame('ada@example.com', $data->email);

        $data->email = null;
        self::assertNull($data->email);
    }
}
