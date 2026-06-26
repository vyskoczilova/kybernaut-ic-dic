<?php

namespace KybernautIcDic\Test;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the mathematical validation helpers in includes/helpers.php.
 *
 * All functions under test are pure (no WordPress dependencies),
 * so no WP_Mock setup is needed.
 */
class HelpersTest extends TestCase {

    /**
     * @dataProvider providerIc
     */
    public function testVerifyIc( $ic, $expected ) {
        $this->assertSame( $expected, (bool) woolab_icdic_verify_ic( $ic ), "IC: {$ic}" );
    }

    public static function providerIc() {
        return [
            'valid (Alza)'              => [ '27082440', true ],
            'valid (Česká pošta)'       => [ '47114983', true ],
            'valid with spaces'         => [ '270 824 40', true ],
            'invalid checksum'          => [ '27082441', false ],
            'too short'                 => [ '1234567', false ],
            'too long'                  => [ '123456789', false ],
            'non-numeric'               => [ 'abcdefgh', false ],
            'empty'                     => [ '', false ],
        ];
    }

    /**
     * @dataProvider providerRc
     */
    public function testVerifyRc( $rc, $expected ) {
        $this->assertSame( $expected, (bool) woolab_icdic_verify_rc( $rc ), "RC: {$rc}" );
    }

    public static function providerRc() {
        return [
            'valid male with slash'     => [ '740104/0020', true ],
            'valid male without slash'  => [ '7401040020', true ],
            'valid female (month +50)'  => [ '745104/0025', true ],
            'valid 9-digit pre-1954'    => [ '530101/123', true ],
            'invalid checksum'          => [ '740104/0021', false ],
            'invalid date (Feb 30)'     => [ '530230/123', false ],
            'garbage'                   => [ 'abc', false ],
            'empty'                     => [ '', false ],
        ];
    }

    /**
     * @dataProvider providerDic
     */
    public function testVerifyDic( $dic, $expected ) {
        $this->assertSame( $expected, (bool) woolab_icdic_verify_dic( $dic ), "DIC: {$dic}" );
    }

    public static function providerDic() {
        return [
            // 8 digits, legal entities (same modulo-11 scheme as IČO).
            'legal entity valid'        => [ '27082440', true ],
            'legal entity invalid'      => [ '27082441', false ],
            // 9 digits, individuals (RC-based, no check digit).
            'individual standard valid' => [ '440104002', true ],
            // 9 digits starting with 6, special cases with lookup-table check digit.
            'individual special valid'  => [ '640903926', true ],
            'individual special invalid'=> [ '640903925', false ],
            // 10 digits, individuals.
            '10-digit valid'            => [ '7401040020', true ],
            '10-digit invalid'          => [ '7401040021', false ],
            'garbage'                   => [ 'ABC', false ],
            'too short'                 => [ '1234', false ],
            'empty'                     => [ '', false ],
        ];
    }

    /**
     * @dataProvider providerDicSk
     */
    public function testVerifyDicSk( $dic, $expected ) {
        $this->assertSame( $expected, (bool) woolab_icdic_verify_dic_sk( $dic ), "SK DIC: {$dic}" );
    }

    public static function providerDicSk() {
        return [
            // Divisible by 11, so the case stays valid once the
            // checksum TODO in woolab_icdic_verify_dic_sk() is implemented.
            'valid 10 digits'           => [ '2020317057', true ],
            'valid with spaces'         => [ '20 2031 7057', true ],
            'too short'                 => [ '123456789', false ],
            'too long'                  => [ '12345678901', false ],
            'non-numeric'               => [ 'abcdefghij', false ],
            'with SK prefix'            => [ 'SK2020317057', false ],
            'empty'                     => [ '', false ],
        ];
    }

    /**
     * @dataProvider providerDicDphSk
     */
    public function testVerifyDicDphSk( $dic_dph, $expected ) {
        $this->assertSame( $expected, (bool) woolab_icdic_verify_dic_dph_sk( $dic_dph ), "SK DIC DPH: {$dic_dph}" );
    }

    public static function providerDicDphSk() {
        return [
            'valid'                     => [ 'SK2020317057', true ],
            'valid with spaces'         => [ 'SK 2020 317 057', true ],
            'invalid checksum'          => [ 'SK2020317058', false ],
            'wrong prefix'              => [ 'CZ2020317057', false ],
            'missing prefix'            => [ '2020317057', false ],
            'too short'                 => [ 'SK123', false ],
            'empty'                     => [ '', false ],
        ];
    }

    /**
     * @dataProvider providerVatCountryCode
     */
    public function testGetVatNumberCountryCode( $vat_number, $expected ) {
        $this->assertSame( $expected, woolab_icdic_get_vat_number_country_code( $vat_number ) );
    }

    public static function providerVatCountryCode() {
        return [
            'Czech'                     => [ 'CZ27082440', 'CZ' ],
            'Slovak'                    => [ 'SK2020317057', 'SK' ],
            'Greek EL maps to GR'       => [ 'EL123456789', 'GR' ],
        ];
    }

    public function testAddAfterCompanyBillingOnly() {
        $fields = [
            'billing_first_name' => [ 'label' => 'First name' ],
            'billing_company'    => [ 'label' => 'Company' ],
            'billing_city'       => [ 'label' => 'City' ],
        ];
        $additional = [
            'billing_ic' => [ 'label' => 'Business ID' ],
        ];

        $result = woolab_icdic_add_after_company( $fields, $additional, 'billing' );

        $this->assertSame(
            [ 'billing_first_name', 'billing_company', 'billing_ic', 'billing_city' ],
            array_keys( $result )
        );
        $this->assertSame( [ 'label' => 'Business ID' ], $result['billing_ic'] );
    }

    public function testAddAfterCompanyBoth() {
        $fields = [
            'billing' => [
                'billing_company' => [ 'label' => 'Company' ],
                'billing_city'    => [ 'label' => 'City' ],
            ],
            'shipping' => [
                'shipping_city' => [ 'label' => 'City' ],
            ],
        ];
        $additional = [
            'billing_ic' => [ 'label' => 'Business ID' ],
        ];

        $result = woolab_icdic_add_after_company( $fields, $additional );

        $this->assertSame(
            [ 'billing_company', 'billing_ic', 'billing_city' ],
            array_keys( $result['billing'] )
        );
        $this->assertSame( $fields['shipping'], $result['shipping'] );
    }
}
