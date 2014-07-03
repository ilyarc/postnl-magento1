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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Payment_Order_Creditmemo_Total_CodFeeTax extends Mage_Sales_Model_Order_Creditmemo_Total_Tax
{
    /**
     * Get the COD fee tax total amount.
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     *
     * @return $this
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $feeTax     = $creditmemo->getPostnlCodFeeTax();
        $baseFeeTax = $creditmemo->getBasePostnlCodFeeTax();

        /**
         * If a creditmemo already has a fee tax, we only need to update the totals.
         */
        if ($feeTax && $baseFeeTax) {
            $creditmemo->setPostnlCodFeeTax($feeTax)
                       ->setBasePostnlCodFeeTax($baseFeeTax)
                       ->setTaxAmount($creditmemo->getTaxAmount() + $feeTax)
                       ->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseFeeTax)
                       ->setGrandTotal($creditmemo->getGrandTotal() + $feeTax)
                       ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFeeTax);

            $order->setPostnlCodFeeTaxRefunded($order->getPostnlCodFeeTaxRefunded() + $feeTax)
                  ->setBasePostnlCodFeeTaxRefunded($order->getBasePostnlCodFeeTaxRefunded() + $baseFeeTax);

            return $this;
        }

        /**
         * If the creditmemo has a fee, but no fee tax, we need to calculate the fee tax.
         */
        $fee     = $creditmemo->getPostnlCodFee();
        $baseFee = $creditmemo->getBasePostnlCodFee();

        if ($fee && $baseFee) {
            /**
             * First we need to determine what percentage of the fee is being refunded. We need to refund the same
             * percentage of fee tax.
             */
            $totalBaseFee = $order->getBasePostnlCodFee();
            $ratio        = $baseFee / $totalBaseFee;

            /**
             * Calculate the fee and base fee tax based on the same ratio.
             */
            $totalBaseFeeTax = $order->getBasePostnlCodFeeTax();
            $baseFeeTax      = $totalBaseFeeTax * $ratio;

            $totalFeeTax = $order->getPostnlCodFeeTax();
            $feeTax      = $totalFeeTax * $ratio;

            $store = $creditmemo->getStore();

            /**
             * Calculate the total tax being refunded and round these numbers.
             */
            $roundedTotalFeeTax = $store->roundPrice($order->getPostnlCodFeeTaxRefunded())
                                + $store->roundPrice($feeTax);
            $roundedTotalBaseFeeTax = $store->roundPrice($order->getBasePostnlCodFeeTaxRefunded())
                                    + $store->roundPrice($baseFeeTax);

            /**
             * If we are attempting to refund more than is possible, we need to modify the fee amounts to compensate for
             * the rounding error.
             */
            if ($roundedTotalFeeTax > $order->getPostnlCodFeeTax()) {
                $feeTax -= 0.0001;
            }

            if ($roundedTotalBaseFeeTax > $order->getBasePostnlCodFeeTax()) {
                $baseFeeTax -= 0.0001;
            }

            /**
             * Update the creditmemo totals.
             */
            $creditmemo->setPostnlCodFeeTax($feeTax)
                       ->setBasePostnlCodFeeTax($baseFeeTax)
                       ->setTaxAmount($creditmemo->getTaxAmount() + $feeTax)
                       ->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseFeeTax)
                       ->setGrandTotal($creditmemo->getGrandTotal() + $feeTax)
                       ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFeeTax);

            $order->setPostnlCodFeeTaxRefunded($order->getPostnlCodFeeTaxRefunded() + $feeTax)
                  ->setBasePostnlCodFeeTaxRefunded($order->getBasePostnlCodFeeTaxRefunded() + $baseFeeTax);

            return $this;
        }

        return $this;
    }
}