<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OfflineTest extends TestCase
{
    public function testValidateValidRc(): void
    {
        $this->assertEquals(
            true,
            woolab_icdic_verify_rc('8956190067')
        );
    }

    public function testValidateInvalidRc(): void
    {
        $this->assertEquals(
            false,
            woolab_icdic_verify_rc('1234567890')
        );
    }
}