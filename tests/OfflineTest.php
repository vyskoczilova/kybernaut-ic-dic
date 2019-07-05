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

    public function testValidateValidIc(): void
    {
        $this->assertEquals(
            true,
            woolab_icdic_verify_ic('88922961')
        );
    }

    public function testValidateInvalidIc(): void
    {
        $this->assertEquals(
            false,
            woolab_icdic_verify_ic('1234567890')
        );
    }

    public function testValidateValidIcSk(): void
    {
        $this->assertEquals(
            true,
            woolab_icdic_verify_ic_sk('36161098') // https://finstat.sk/36562939
        );
    }

    public function testValidateInvalidIcSk(): void
    {
        $this->assertEquals(
            false,
            woolab_icdic_verify_ic_sk('1234567890')
        );
    }

    public function testValidateValidDicSk(): void
    {
        $this->assertEquals(
            true,
            woolab_icdic_verify_dic_sk('2021863811') // https://finstat.sk/36562939
        );
    }

    public function testValidateInvalidDicSk(): void
    {
        $this->assertEquals(
            false,
            woolab_icdic_verify_dic_sk('12345678910')
        );
    }

    public function testValidateValidIcDphSk(): void
    {
        $this->assertEquals(
            'SK2021863811',
            woolab_icdic_verify_dic_dph_sk('SK2021863811') // https://finstat.sk/36562939
        );
    }

    public function testValidateInvalidIcDphSk(): void
    {
        $this->assertEquals(
            false,
            woolab_icdic_verify_dic_dph_sk('SK12345678910')
        );
    }
}