<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Unit_Model_DeliveryOptions_System_Config_Backend_ValidateFeeTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee
     */
    protected function _getInstance()
    {
        return Mage::getModel('postnl_deliveryoptions/system_config_backend_validateFee');
    }

    /**
     * @test
     */
    public function validateFeeShouldBeCallable()
    {
        $instance = $this->_getInstance();
        $this->assertInstanceOf('TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee', $instance);

        $isCallable = is_callable(array($instance, 'validateFee'));
        $this->assertTrue($isCallable);
    }

    /**
     * @return array
     */
    public function validateFeeprovider()
    {
        return array(
            array(
                'fee'          => 1,
                'includingTax' => true,
                'expected'     => true,
            ),
            array(
                'fee'          => 1,
                'includingTax' => false,
                'expected'     => true,
            ),
            array(
                'fee'          => 2,
                'includingTax' => true,
                'expected'     => true,
            ),
            array(
                'fee'          => 2,
                'includingTax' => false,
                'expected'     => false,
            ),
            array(
                'fee'          => 3,
                'includingTax' => true,
                'expected'     => false,
            ),
            array(
                'fee'          => 2.000000001,
                'includingTax' => true,
                'expected'     => false,
            ),
            array(
                'fee'          => 1.999999999,
                'includingTax' => false,
                'expected'     => false,
            ),
            array(
                'fee'          => -1,
                'includingTax' => true,
                'expected'     => false,
            ),
            array(
                'fee'          => 'test',
                'includingTax' => true,
                'expected'     => false,
            ),
            array(
                'fee'          => '',
                'includingTax' => true,
                'expected'     => false,
            ),
        );
    }
}

class TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFeeFake extends TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee
{
    public function beforeSave()
    {
        $this->_beforeSave();
    }
}
