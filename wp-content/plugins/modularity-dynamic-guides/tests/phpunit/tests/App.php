<?php

namespace ModularityDynamicGuides\Tests;

use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;
use ModularityDynamicGuides\App;

/**
 * Class AppTest
 */
class AppTest extends TestCase
{
    /**
     * Does not send validation error
    */
    public function testValidateOutcomesSendsNoValidationError()
    {
        // When
        $instance = new App();
        $this->mockGlobalPostAcf([
            'field_65b8b17f91cdf' => 'test'
        ]);
        WP_Mock::userFunction('acf_add_validation_error')
        ->times(0);

        $result = $instance->validateOutcomes();
        $this->assertTrue(true);
    }

    /**
     * Does send validation error if missing outcomes
    */
    public function testValidateOutcomesSendsValidationError()
    {
        // When
        $instance = new App();
        $this->mockGlobalPostAcf($this->errorMsg());
        WP_Mock::userFunction('acf_add_validation_error')
        ->once()
        ->with('acf[field_65b8b17f91cdf]', '<b style="font-size: 1.1rem;">Missing outcomes:</b><ol><li style="font-size: 0.9rem;"><b>test:</b> no</li></ol>');

        $result = $instance->validateOutcomes();
        $this->assertTrue(true);
    }

    private function mockGlobalPostAcf($acfTestValue) {
        $_POST = [];
        $_POST['acf'] = $acfTestValue;
    }

    private function errorMsg() {
        return [
            'field_65b8b17f91cdf' => [
                 'row-0' => [
                     'field_65b8ee0ac6cd8' => [
                         '0' => 'yes',
                     ],
                 ],
             ],
             'field_65b78add784cd' => [
                 'row-0' => [
                     'field_65b7993d1aba6' => 'test',
                     'field_65b78b84784ce' => [
                         'row-0' => [
                             'field_65b78b92784cf' => 'yes',
                         ],
                         'row-1' => [
                             'field_65b78b92784cf' => 'no',
                         ],
                     ],
                 ],
             ],
             'field_65ba49cbdb950' => '{\"0\":{\"test\":\"yes\"}}'
         ];
    }
}
