<?php

namespace KybernautIcDic\Test;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use WP_Mock;

class AresTest extends TestCase {

    /**
     * Test if the function returns an array with and error message when ICO not set.
     * @return void 
     * @throws ExpectationFailedException 
     */
    public function testIcoNotSet() {
            
        $this->assertEquals(
            array( 'error' => __('Business ID not set.', 'kybernaut-messenger')),
            woolab_icdic_ares()
        );

    }

    public function testTooShort() {

        $this->assertEquals(
            array( 'error' => __('Business ID must be a number and 8 digits long.', 'kybernaut-messenger')),
            woolab_icdic_ares(123)
        );

    }

    public function testTooLong() {

        $this->assertEquals(
            array( 'error' => __('Business ID must be a number and 8 digits long.', 'kybernaut-messenger')),
            woolab_icdic_ares(123456789)
        );

    }

    public function testWithLetters() {

        $this->assertEquals(
            array( 'error' => __('Business ID must be a number and 8 digits long.', 'kybernaut-messenger')),
            woolab_icdic_ares('1234567a')
        );

    }

    /**
     * Test if the function returns an array with and error message when WP error.
     * @return void 
     * @throws ExpectationFailedException 
     */
    public function testWpError() {


        WP_Mock::userFunction('wp_remote_get', [
            'times' => 1,
            'return' => false
        ]);

        WP_Mock::userFunction('is_wp_error', [
            'times' => 1,
            'return' => true
        ]);

        $this->assertEquals(
            array( 'error' => __('An error occured while connecting to ARES, try it again later.', 'kybernaut-messenger')),
            woolab_icdic_ares(88922961)
        );

    }

    /**
	 * @dataProvider subjectDataParsedProperlyProvider
     */
    public function testLiveTestSubjectDataParsedProperly($ico, $returnedData) {

        WP_Mock::userFunction('is_wp_error', [
            'times' => 1,
            'return' => false
        ]);

        WP_Mock::userFunction('wp_remote_retrieve_response_code', [
            'times' => 1,
            'return' => 200
        ]);

        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'times' => 1,
            'return_arg' => 0,
        ]);

        $this->assertEquals($returnedData, woolab_icdic_ares($ico));

    }

    public function test404StatusCode() {
            
            WP_Mock::userFunction('is_wp_error', [
                'times' => 1,
                'return' => false
            ]);
    
            WP_Mock::userFunction('wp_remote_retrieve_response_code', [
                'times' => 1,
                'return' => 404
            ]);
    
            $this->assertEquals(
                array( 'error' => __('Entity doesn\'t exist in ARES.', 'kybernaut-messenger')),
                woolab_icdic_ares(88922961)
            );
    }

    public function testOtherStatusCode() {
            
        WP_Mock::userFunction('is_wp_error', [
            'times' => 1,
            'return' => false
        ]);

        WP_Mock::userFunction('wp_remote_retrieve_response_code', [
            'times' => 1,
            'return' => 500
        ]);

        $this->assertEquals(
            array( 'error' => __('ARES is not responding', 'kybernaut-messenger')),
            woolab_icdic_ares(88922961)
        );
    }

    public static function subjectDataParsedProperlyProvider() {

        return [
            '88922961 (podnikatel, praha)' => [
                88922961,
                [
                   'error' => false,
                    'spolecnost' => 'Karolína Vyskočilová',
                    'ico' => '88922961',
                    'dic' => 'CZ8956190067',
                    'adresa' => 'Za Pohořelcem 672/15',
                    'psc' => 16900,
                    'mesto' => 'Praha 6',
                ]
            ],
            '45211515 (vesnice bez ulice)' => [
                45211515,
                [
                    'error' => false,
                    'spolecnost' => 'Základní škola Valašská Polanka, okres Vsetín',
                    'ico' => '45211515',
                    'dic' => '',
                    'adresa' => 'Valašská Polanka 301',
                    'psc' => 75611,
                    'mesto' => 'Valašská Polanka',
                ]
            ],
            ['16415345 (orientacni cislo, město bez pojmenované části)' =>
                16415345,
                [
                    'error' => false,
                    'spolecnost' => 'Roman Korecký',
                    'ico' => '16415345',
                    'dic' => 'CZ6207141193',
                    'adresa' => 'Ruprechtická 319/16a',
                    'psc' => 46001,
                    'mesto' => 'Liberec',
                ]
            ],
            ['27082440 (firma, praha)' =>
                27082440,
                [
                    'error' => false,
                    'spolecnost' => 'Alza.cz a.s.',
                    'ico' => '27082440',
                    'dic' => 'CZ27082440',
                    'adresa' => 'Jankovcova 1522/53',
                    'psc' => 17000,
                    'mesto' => 'Praha 7',
                ]
            ],
            ['02491427 (firma, pisek)' =>
                '02491427',
                [
                    'error' => false,
                    'spolecnost' => 'Západočeská společnost, o.s.',
                    'ico' => '02491427',
                    'dic' => '',
                    'adresa' => 'Budovcova 105/4',
                    'psc' => 39701,
                    'mesto' => 'Písek',
                ]
            ],
            ['26219981 (firma, brno)' =>
                '26219981',
                [
                    'error' => false,
                    'spolecnost' => 'JUMO Měření a regulace s.r.o.',
                    'ico' => '26219981',
                    'dic' => 'CZ26219981',
                    'adresa' => 'Křídlovická 943/24a',
                    'psc' => 60300,
                    'mesto' => 'Brno',
                ]
            ],
        ];

    }

}