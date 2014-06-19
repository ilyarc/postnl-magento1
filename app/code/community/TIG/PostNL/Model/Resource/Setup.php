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
class TIG_PostNL_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    /**
     * Cron expression and cron model definitions for shipping_status cron
     */
    const SHIPPING_STATUS_CRON_STRING_PATH = 'crontab/jobs/postnl_update_shipping_status/schedule/cron_expr';
    const SHIPPING_STATUS_CRON_MODEL_PATH  = 'crontab/jobs/postnl_update_shipping_status/run/model';

    /**
     * Cron expression and cron model definitions for statistics update cron
     */
    const UPDATE_STATISTICS_CRON_STRING_PATH = 'crontab/jobs/postnl_update_statistics/schedule/cron_expr';
    const UPDATE_STATISTICS_CRON_MODEL_PATH  = 'crontab/jobs/postnl_update_statistics/run/model';

    /**
     * XML path to the supporttab_expanded setting
     */
    const EXPAND_SUPPORT_PATH = 'postnl/support/expanded';

    /**
     * Test data.
     */
    const DEFAULT_TEST_PASSWORD = 'z9A4LpFd53Z';
    const DEFAULT_WEBSHOP_ID    = '853f9d2a4c5242f097daeaf61637609c';

    /**
     * Xpaths for test data.
     */
    const XPATH_TEST_PASSWORD = 'postnl/cif/test_password';
    const XPATH_WEBSHOP_ID    = 'postnl/cif/webshop_id';

    /**
     * Xpath to supported options configuration setting
     */
    const XPATH_SUPPORTED_PRODUCT_OPTIONS = 'postnl/cif_product_options/supported_product_options';

    /**
     * Minimum server memory required by the PostNL extension in bytes.
     */
    const MIN_SERVER_MEMORY = 268435456; //256MB

    /**
     * callAfterApplyAllUpdates flag. Causes applyAfterUpdates() to be called.
     *
     * @var boolean
     */
    protected $_callAfterApplyAllUpdates = true;

    /**
     * Module version as stored in the db at the time of the update
     *
     * @var string
     */
    protected $_dbVer;

    /**
     * Module version as specified in the module's configuration at the time of the update
     *
     * @var string
     */
    protected $_configVer;

    /**
     * Array of available cif webservice settings.
     *
     * @var array
     */
    protected $_cifWebserviceVersionSettings = array(
        'cif_version_shippingstatus',
        'cif_version_confirming',
        'cif_version_labelling',
        'cif_version_barcode',
        'cif_version_checkout',
        'cif_version_deliverydate',
        'cif_version_timeframe',
        'cif_version_location',
    );

    /**
     * Set the stored DB version to the specified value
     *
     * @param string $dbVer
     *
     * @return $this
     */
    public function setDbVer($dbVer)
    {
        $this->_dbVer = $dbVer;

        return $this;
    }

    /**
     * Set the stored config version to the specified value
     *
     * @param string $configVer
     *
     * @return $this
     */
    public function setConfigVer($configVer)
    {
        $this->_configVer = $configVer;

        return $this;
    }

    /**
     * Get the stored DB version
     *
     * @return string
     */
    public function getDbVer()
    {
        return $this->_dbVer;
    }

    /**
     * get the stored config version
     *
     * @return string
     */
    public function getConfigVer()
    {
        return $this->_configVer;
    }

    /**
     * Gets an array of cif webservice version settings.
     *
     * @return array
     */
    public function getCifWebserviceVersionSettings()
    {
        return $this->_cifWebserviceVersionSettings;
    }

    /**
     * Store the applied update versions
     *
     * @return parent::applyUpdates()
     */
    public function applyUpdates()
    {
        /**
         * @var Mage_Core_Model_Resource_Resource $resource
         * @var Varien_Simplexml_Element $moduleConfig
         */
        $resource = $this->_getResource();
        $moduleConfig = $this->_moduleConfig;

        $dbVer = $resource->getDbVersion($this->_resourceName);
        $configVer = (string) $moduleConfig->version;

        $this->setDbVer($dbVer);
        $this->setConfigVer($configVer);

        return parent::applyUpdates();
    }

    /**
     * Check if the PostNL module has been updated. If so, add an admin notification to the inbox.
     *
     * @return $this
     */
    public function afterApplyAllUpdates()
    {
        $dbVer = $this->getDbVer();
        $configVer = $this->getConfigVer();

        $this->_checkVersionCompatibility();
        $this->_checkMemoryRequirement();

        if (version_compare($configVer, $dbVer) != self::VERSION_COMPARE_GREATER) {
            return $this;
        }

        $helper = Mage::helper('postnl');

        $inbox = Mage::getModel('postnl/inbox');
        if ($dbVer) {
            $message = '[POSTNL-0083] ' . $helper->__(
                'PostNL extension has been successfully updated to version v%s.',
                $configVer
            );
            $url = 'http://kb.totalinternetgroup.nl/topic/31921907';
        } else {
            $message = '[POSTNL-0156] ' . $helper->__(
                'The PostNL extension v%s has been successfully installed.',
                $configVer
            );
            $url = '';
        }

        $inbox->addNotice($message, $message, $url, true)
              ->save();

        return $this;
    }

    /**
     * Generate a random cron expression for the status update cron for this merchant and store it in the database
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function generateShippingStatusCronExpr()
    {
        /**
         * Generate semi-random values for the cron expression
         */
        $cronMorningHour   = mt_rand(10, 12);
        $cronMorningHour  += Mage::getModel('core/date')->getGmtOffset('hours');

        $cronAfternoonHour = $cronMorningHour + 4; //4 hours after the morning update
        $cronMinute        = mt_rand(0, 59);

        /**
         * Generate a cron expr that runs on a specified minute on a specified hour between 10 and 12 AM, and between 14
         * and 16 PM.
         */
        $cronExpr = "{$cronMinute} {$cronMorningHour},{$cronAfternoonHour} * * *";

        /**
         * Store the cron expression in core_config_data
         */
        try {
            Mage::getModel('core/config_data')
                ->load(self::SHIPPING_STATUS_CRON_STRING_PATH, 'path')
                ->setValue($cronExpr)
                ->setPath(self::SHIPPING_STATUS_CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::SHIPPING_STATUS_CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::SHIPPING_STATUS_CRON_MODEL_PATH))
                ->setPath(self::SHIPPING_STATUS_CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to save shipping_status cron expression: %s', $cronExpr),
                'POSTNL-0084',
                $e
            );
        }

        return $this;
    }

    /**
     * Generates a semi-random cron expression for the update statistics cron. This is done to spread out the number of
     * calls across each day.
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function generateUpdateStatisticsCronExpr()
    {
        /**
         * Generate random values for the cron expression
         */
        $cronMorningHour   = mt_rand(0, 11);
        $cronAfternoonHour = $cronMorningHour + 12; //half a day after the morning update
        $cronMinute        = mt_rand(0, 59);

        /**
         * Generate a cron expr that runs on a specified minute on a specified hour twice per day.
         */
        $cronExpr = "{$cronMinute} {$cronMorningHour},{$cronAfternoonHour} * * *";

        /**
         * Store the cron expression in core_config_data
         */
        try {
            Mage::getModel('core/config_data')
                ->load(self::UPDATE_STATISTICS_CRON_STRING_PATH, 'path')
                ->setValue($cronExpr)
                ->setPath(self::UPDATE_STATISTICS_CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::UPDATE_STATISTICS_CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::UPDATE_STATISTICS_CRON_MODEL_PATH))
                ->setPath(self::UPDATE_STATISTICS_CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to save update_statistics cron expression: %s', $cronExpr),
                'POSTNL-0085',
                $e
            );
        }

        return $this;
    }

    /**
     * Checks the store's config to see if the extension is compatible with the installed Magento version. If not, a
     * message will be added to Mage_AdminNotification.
     *
     * @return $this
     */
    public function _checkVersionCompatibility()
    {
        if (Mage::registry('postnl_version_compatibility_checked')) {
            return $this;
        }

        $helper = Mage::helper('postnl');
        if ($helper->isEnterprise()) {
            $edition = 'enterprise';
        } else {
            $edition = 'community';
        }

        $supportedVersions = Mage::getConfig()->getNode('tig/compatibility/postnl/' . $edition);
        if ($supportedVersions === false) {
            $message = '[POSTNL-0086] '
                     . $helper->__(
                         'The PostNL extension is not compatible with your Magento version! This may cause unexpected '
                         . 'behaviour.'
                     );

            $inbox = Mage::getModel('postnl/inbox');
            $inbox->addCritical(
                      $message,
                      $message,
                      'http://kb.totalinternetgroup.nl/topic/31925577',
                      true
                  )
                  ->save();

            Mage::register('postnl_version_compatibility_checked', true);
            return $this;
        }

        $supportedVersions = (string) $supportedVersions;
        $supportedVersionArray = explode(',', $supportedVersions);

        $installedMagentoVersionInfo = Mage::getVersionInfo();
        $installedMagentoVersion = $installedMagentoVersionInfo['major'] . '.' . $installedMagentoVersionInfo['minor'];

        if (!in_array($installedMagentoVersion, $supportedVersionArray)) {
            $message = '[POSTNL-0086] '
                     . $helper->__(
                         'The PostNL extension is not compatible with your Magento version! This may cause unexpected '
                         . 'behaviour.'
                     );

            $inbox = Mage::getModel('postnl/inbox');
            $inbox->addCritical(
                      $message,
                      $message,
                      'http://kb.totalinternetgroup.nl/topic/31925577',
                      true
                  )
                  ->save();

            Mage::register('postnl_version_compatibility_checked', true);
            return $this;
        }


        Mage::register('postnl_version_compatibility_checked', true);
        return $this;
    }

    /**
     * Make sure that the server meets Magento's (and PostNL's) memory requirements.
     *
     * @return $this
     * @throws Exception
     */
    protected function _checkMemoryRequirement()
    {
        if (Mage::registry('postnl_memory_requirement_checked')) {
            return $this;
        }

        $helper = Mage::helper('postnl');

        if ($helper->getMemoryLimit() < self::MIN_SERVER_MEMORY) {
            $message = '[POSTNL-0175] '
                . $helper->__(
                    'The server\'s memory limit is less than %1$dMB. The PostNL extension requires at least %1$dMB to' .
                    ' function properly. Using the PostNL extension on servers with less memory than this may cause' .
                    ' unexpected errors.',
                    self::MIN_SERVER_MEMORY / 1024 / 1024
                );

            $inbox = Mage::getModel('postnl/inbox');
            $inbox->addCritical(
                $message,
                $message,
                '',
                true
            )->save();
        }

        Mage::register('postnl_memory_requirement_checked', true);
        return $this;
    }

    /**
     * Makes sure the PostNL support tab is expanded the first time an admin visits the PostNL system/config/edit page.
     *
     * @return $this
     */
    public function expandSupportTab()
    {
        $configState = array(
            'postnl_support' => 1,
        );

        /**
         * Get all admin users and save the PostNL support tab's state as being expanded for each one.
         *
         * This has the same effect as having every admin log in, go to system/config/edit/section/postnl and manually
         * click on the 'Version & Support' tab before saving the section.
         */
        $adminUsers = Mage::getResourceModel('admin/user_collection');
        foreach ($adminUsers as $adminUser) {
            $this->_saveState($adminUser, $configState);
        }

        return $this;
    }

    /**
     * Save state of configuration field sets.
     * Modified version of the Mage_Adminhtml_System_ConfigController::_saveState() method.
     *
     * @param Mage_Admin_Model_User $adminUser
     * @param array $configState
     *
     * @return $this
     *
     * @see Mage_Adminhtml_System_ConfigController::_saveState()
     */
    protected function _saveState(Mage_Admin_Model_User $adminUser, $configState = array())
    {
        if (!is_array($configState)) {
            return $this;
        }

        $extra = $adminUser->getExtra();
        if (!is_array($extra)) {
            $extra = array();
        }

        if (!isset($extra['configState'])) {
            $extra['configState'] = array();
        }

        foreach ($configState as $fieldset => $state) {
            $extra['configState'][$fieldset] = $state;
        }

        try {
            $adminUser->saveExtra($extra);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
        }

        return $this;
    }

    /**
     * Resets a specific webservice version settings to the defaults specified in config.xml. If no parameter is
     * provided, all settings will be reset.
     *
     * Please note that you will have to clear the caches after this method is used. Since this should be used during an
     * upgrade of the extension, you should be doing that anyway.
     *
     * @param boolean|array $webservices
     *
     * @return TIG_PostNL_Model_Resource_Setup
     */
    public function resetWebserviceVersions($webservices = false)
    {
        if ($webservices === false) {
            $webservices = $this->getCifWebserviceVersionSettings();
        }

        foreach ($webservices as &$service) {
            if (strpos($service, 'postnl/advanced/') === false) {
                $service = 'postnl/advanced/' . $service;
            }
        }

        $this->resetConfig($webservices);

        return $this;
    }

    /**
     * Resets an array of config fields to factory defaults. This is done by actually deleting the config value from the
     * core_config_data table.
     * Because Magento overwrites the default values in the config.xml files with those from the db, if we remove the
     * values from the db, the default values from the config.xml files will again be used.
     *
     * @param array|string $configFields
     *
     * @return TIG_PostNL_Model_Resource_Setup
     */
    public function resetConfig($configFields)
    {
        if (!is_array($configFields)) {
            $configFields = array($configFields);
        }

        $config = Mage::getConfig();
        foreach ($configFields as $setting) {
            $config->deleteConfig($setting);
        }

        return $this;
    }

    /**
     * Saves the default test password in the database as an encrypted string.
     *
     * @return $this
     */
    public function installTestPassword()
    {
        $testPassword = self::DEFAULT_TEST_PASSWORD;
        $encryptedPassword = Mage::helper('core')->encrypt($testPassword);

        /**
         * @var Mage_Core_Model_Config_Data $config
         */
        $config = Mage::getModel('core/config_data')
                      ->load(self::XPATH_TEST_PASSWORD, 'path');

        $config->setValue($encryptedPassword)
               ->setPath(self::XPATH_TEST_PASSWORD)
               ->save();

        return $this;
    }

    /**
     * Saves the default test webshop ID as an encrypted string.
     *
     * @return $this
     */
    public function installWebshopId()
    {
        $testWebshopId = self::DEFAULT_WEBSHOP_ID;
        $encryptedWebshopId = Mage::helper('core')->encrypt($testWebshopId);

        /**
         * @var Mage_Core_Model_Config_Data $config
         */
        $config = Mage::getModel('core/config_data')
                      ->load(self::XPATH_WEBSHOP_ID, 'path');

        $config->setValue($encryptedWebshopId)
               ->setPath(self::XPATH_WEBSHOP_ID)
               ->save();

        return $this;
    }

    /**
     * Moves a config value from one place to another, by copying it's value. If the $removeOldValue parameter is true,
     * we also remove the old value.
     *
     * @param string  $fromXpath
     * @param string  $toXpath
     * @param boolean $removeOldValue
     *
     * @return $this
     */
    public function moveConfigSetting($fromXpath, $toXpath, $removeOldValue = true)
    {
        /**
         * First loop through all stores.
         *
         * @var Mage_Core_Model_Store $store
         */
        $stores = Mage::app()->getStores();
        $scope  = 'store';
        foreach ($stores as $store) {
            $scopeId = $store->getId();

            $this->moveConfigSettingForScope($fromXpath, $toXpath, $scope, $scopeId, $removeOldValue);
        }

        /**
         * Now loop through all websites.
         *
         * @var Mage_Core_Model_Website $website
         */
        $websites = Mage::app()->getWebsites();
        $scope    = 'website';
        foreach ($websites as $website) {
            $scopeId = $website->getId();

            $this->moveConfigSettingForScope($fromXpath, $toXpath, $scope, $scopeId, $removeOldValue);
        }

        /**
         * Finally, try to move the config setting for the default scope.
         */
        $this->moveConfigSettingForScope($fromXpath, $toXpath, 'default', 0, $removeOldValue);

        return $this;
    }

    /**
     * Move a config setting for a specified scope.
     *
     * @param string  $fromXpath
     * @param string  $toXpath
     * @param string  $scope
     * @param int     $scopeId
     * @param boolean $removeOldValue
     *
     * @return $this
     */
    public function moveConfigSettingForScope($fromXpath, $toXpath, $scope = 'default', $scopeId = 0,
                                                 $removeOldValue = true)
    {
        $config = Mage::getConfig();

        $node = $config->getNode($fromXpath, $scope, $scopeId);

        /**
         * If the node is not set for the default scope, there is nothing left to do.
         */
        if ($node === false) {
            return $this;
        }

        /**
         * Get the string representation of the value.
         */
        $currentValue = $node->__toString();

        /**
         * Save the value to the new xpath for the scope from which we got the old value.
         */
        $config->saveConfig($toXpath, $currentValue, $scope, $scopeId);

        /**
         * Optionally remove the value from the old xpath.
         */
        if ($removeOldValue) {
            $config->deleteConfig($fromXpath, $scope, $scopeId);
        }

        return $this;
    }

    /**
     * Adds new supported product codes.
     *
     * @param array|string|int $codes
     *
     * @return $this
     */
    public function addSupportedProductCode($codes)
    {
        if (!is_array($codes)) {
            $codes = array((string) $codes);
        }

        /**
         * Get the currently supported product codes.
         */
        $adminStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $supportedProductCodes = Mage::getStoreConfig(self::XPATH_SUPPORTED_PRODUCT_OPTIONS, $adminStoreId);

        /**
         * If no supported product codes are set, it means the default option is used, which should already contain the
         * new codes.
         */
        if ($supportedProductCodes === null) {
            return $this;
        }

        $supportedCodesArray = explode(',', $supportedProductCodes);

        /**
         * Add the new codes to the existing codes by merging both arrays and then getting only the unique values.
         * Finally we implode the array, so that we can store it in the core_config_data table.
         */
        $mergedCodes = array_merge($supportedCodesArray, $codes);
        $uniqueCodes = array_unique($mergedCodes);
        $newCodes    = implode(',', $uniqueCodes);

        /**
         * Save the supported product codes.
         */
        Mage::getConfig()->saveConfig(self::XPATH_SUPPORTED_PRODUCT_OPTIONS, $newCodes, 'default', 0);

        return $this;
    }

    /**
     * Clears the config cache. This should be called after changes have been made to the shop's configuration.
     *
     * @return $this
     */
    public function clearConfigCache()
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        return $this;
    }

    /**
     * Sets the shipment type of every PostNL shipment. Before 1.3.0 the shipment type was determined on the fly. Since
     * 1.3.0 it is instead set once in the PostNL Shipment table. This method updates the table for all PostNL shipments
     * that do not yet have a shipment type.
     *
     * @return $this
     */
    public function setShipmentType()
    {
        $transactionSave = Mage::getResourceModel('core/transaction');

        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        foreach ($postnlShipmentCollection as $shipment) {
            /**
             * The getShipmentType() method will calculate and set the shipment type if none is available.
             */
            $shipment->getShipmentType();
            $transactionSave->addObject($shipment);
        }

        $transactionSave->save();

        return $this;
    }
}
