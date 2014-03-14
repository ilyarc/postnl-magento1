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
class TIG_PostNL_Helper_AddressValidation extends TIG_PostNL_Helper_Data
{
    /**
     * XML path to use_postcode_check setting
     */
    const XML_PATH_USE_POSTCODE_CHECK = 'postnl/cif_address/use_postcode_check';

    /**
     * Constants containing XML paths to cif address configuration options
     */
    const XML_PATH_SPLIT_STREET                = 'postnl/cif_address/split_street';
    const XML_PATH_STREETNAME_FIELD            = 'postnl/cif_address/streetname_field';
    const XML_PATH_HOUSENUMBER_FIELD           = 'postnl/cif_address/housenr_field';
    const XML_PATH_SPLIT_HOUSENUMBER           = 'postnl/cif_address/split_housenr';
    const XML_PATH_HOUSENUMBER_EXTENSION_FIELD = 'postnl/cif_address/housenr_extension_field';

    /**
     * XML paths to flags that dtermine which environment allows the postcode check functionality
     */
    const XML_PATH_POSTCODE_CHECK_IN_CHECKOUT    = 'postnl/cif_address/postcode_check_in_checkout';
    const XML_PATH_POSTCODE_CHECK_IN_ADDRESSBOOK = 'postnl/cif_address/postcode_check_in_addressbook';

    /**
     * XML paths that control some features of postcode check
     */
    const XML_PATH_POSTCODE_CHECK_MAX_ATTEMPTS = 'postnl/cif_address/postcode_check_max_attempts';
    const XML_PATH_POSTCODE_CHECK_TIMEOUT     = 'postnl/cif_address/postcode_check_timeout';

    /**
     * Log filename to log all cendris exceptions
     */
    const CENDRIS_EXCEPTION_LOG_FILE = 'TIG_PostNL_Cendris_Exception.log';

    /**
     * Log filename to log cendris calls
     */
    const CENDRIS_DEBUG_LOG_FILE = 'TIG_PostNL_Cendris_Debug.log';

    /**
     * Checks whether the given store uses split address lines.
     *
     * @param int|null $storeId
     *
     * @return boolean
     */
    public function useSplitStreet($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            return true;
        }

        $useSplitStreet = Mage::getStoreConfigFlag(self::XML_PATH_SPLIT_STREET, $storeId);
        return $useSplitStreet;
    }

    /**
     * Checks whether the given store uses split housenumber values.
     *
     * @param int|null $storeId
     *
     * @return boolean
     */
    public function useSplitHousenumber($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            return true;
        }

        $useSplitStreet = Mage::getStoreConfigFlag(self::XML_PATH_SPLIT_HOUSENUMBER, $storeId);
        return $useSplitStreet;
    }

    /**
     * Gets the address field number used for the streetname field.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getStreetnameField($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            return 1;
        }

        $streetnameField = (int) Mage::getStoreConfig(self::XML_PATH_STREETNAME_FIELD, $storeId);
        return $streetnameField;
    }

    /**
     * Gets the address field number used for the housenumber field.
     *
     * @param int|null $storeId
     *
     * @return int
     */

    public function getHousenumberField($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            return 2;
        }

        $housenumberField = (int) Mage::getStoreConfig(self::XML_PATH_HOUSENUMBER_FIELD, $storeId);
        return $housenumberField;
    }

    /**
     * Gets the address field number used for the housenumber extension field.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getHousenumberExtensionField($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            return 3;
        }

        $housenumberExtensionField = (int) Mage::getStoreConfig(self::XML_PATH_HOUSENUMBER_EXTENSION_FIELD, $storeId);
        return $housenumberExtensionField;
    }

    /**
     * Gets the number of seconds before postcode check times out.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getPostcodeCheckTimeoutDelay($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $timeout = (int) Mage::getStoreConfig(self::XML_PATH_POSTCODE_CHECK_TIMEOUT, $storeId);
        return $timeout;
    }

    /**
     * Gets the number of times a customer may attempt to enter their postcode and housenumber before postcode check disables
     * itself.
     *
     * @param int|null $storeId
     *
     * @return string|int
     */
    public function getPostcodeCheckMaxAttempts($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $maxAttempts = (int) Mage::getStoreConfig(self::XML_PATH_POSTCODE_CHECK_MAX_ATTEMPTS, $storeId);
        if (!$maxAttempts) {
            return 'false';
        }

        return $maxAttempts;
    }

    /**
     * Check if the Postcode Check is active.
     *
     * @param int|null $storeId
     *
     * @return boolean
     */
    public function isPostcodeCheckActive($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $usePostcodeCheck = Mage::getStoreConfigFlag(self::XML_PATH_USE_POSTCODE_CHECK, $storeId);
        return $usePostcodeCheck;
    }

    /**
     * Checks if the Postcode Check is enabled and ready for use.
     *
     * @param int|null $storeId
     *
     * @param bool     $environment
     *
     * @return boolean
     */
    public function isPostcodeCheckEnabled($storeId = null, $environment = false)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $isPostnlEnabled = $this->isEnabled($storeId, false);
        if (!$isPostnlEnabled) {
            return false;
        }

        $isPostcodeCheckActive = $this->isPostcodeCheckActive($storeId);
        if (!$isPostcodeCheckActive) {
            return false;
        }

        /**
         * Check to see if the postcode check functionality is allowed for the specified environment.
         */
        $environmentAllowed = false;
        switch ($environment) {
            case 'checkout':
                $environmentAllowed = Mage::getStoreConfigFlag(self::XML_PATH_POSTCODE_CHECK_IN_CHECKOUT, $storeId);
                break;
            case 'addressbook':
                $environmentAllowed = Mage::getStoreConfigFlag(self::XML_PATH_POSTCODE_CHECK_IN_ADDRESSBOOK, $storeId);
                break;
            case false:
                $environmentAllowed = true;
                break;
            //no default
        }

        return $environmentAllowed;
    }

    /**
     * Logs a cendris request and response for debug purposes.
     *
     * @param SoapClient $client
     *
     * @return TIG_PostNL_Helper_Webservices
     *
     * @see Mage::log()
     */
    public function logCendrisCall($client)
    {
        if (!$this->isLoggingEnabled()) {
            return $this;
        }

        $this->createLogDir();

        $requestXml = $this->formatXml($client->getLastRequest());
        $responseXML = $this->formatXml($client->getLastResponse());

        $logMessage = 'Request sent:'
                    . PHP_EOL
                    . $requestXml
                    . PHP_EOL
                    . 'Response received:'
                    . PHP_EOL
                    . $responseXML;

        $file = self::POSTNL_LOG_DIRECTORY . DS . self::CENDRIS_DEBUG_LOG_FILE;
        $this->log($logMessage, Zend_Log::DEBUG, $file);

        return $this;
    }

    /**
     * Logs a cendris exception in the database and/or a log file
     *
     * @param Mage_Core_Exception|TIG_PostNL_Exception $exception
     * @param SoapClient|boolean $client
     *
     * @return TIG_PostNL_Helper_Webservices
     *
     * @see Mage::logException()
     */
    public function logCendrisException($exception, $client = false)
    {
        if (!$this->isExceptionLoggingEnabled()) {
            return $this;
        }

        $logMessage = PHP_EOL . $exception->__toString();

        if ($client) {
            $requestXml = $this->formatXml($client->getLastRequest());
            $responseXML = $this->formatXml($client->getLastResponse());

            $logMessage .= PHP_EOL
                         . 'Request sent:'
                         . PHP_EOL
                         . $requestXml
                         . PHP_EOL
                         . 'Response received:'
                         . PHP_EOL
                         . $responseXML;
        }

        $file = self::POSTNL_LOG_DIRECTORY . DS . self::CENDRIS_EXCEPTION_LOG_FILE;
        $this->log($logMessage, Zend_Log::ERR, $file, false, true);

        return $this;
    }
}
