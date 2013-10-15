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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Log filename to log all non-specific PostNL exceptions
     * 
     * @var string
     */
    const POSTNL_EXCEPTION_LOG_FILE = 'TIG_PostNL_Exception.log';
    
    /**
     * xml path to postnl general active/inactive setting
     * 
     * @var string
     */
    const XML_PATH_EXTENSION_ACTIVE = 'postnl/general/active';
    
    /**
     * Determines if the extension has been activated
     * 
     * @return bool
     */
    public function isEnabled()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        
        $enabled = Mage::getStoreCOnfig(self::XML_PATH_EXTENSION_ACTIVE, $storeId);
        
        return (bool) $enabled;
    }
    
    /**
     * Returns path to specified directory for specified module.
     * 
     * Based on Mage_Core_Model_Config::getModuleDir()
     * 
     * @param string $dir The directory in question
     * @param string $module The module for which the directory is needed
     * 
     * @return string
     * 
     * @see Mage_Core_Model_Config::getModuleDir()
     */
    public function getModuleDir($dir, $moduleName = 'TIG_PostNL')
    {
        $config = Mage::app()->getConfig();
        
        $codePool = (string)$config->getModuleConfig($moduleName)->codePool;
        $path = $config->getOptions()->getCodeDir()
              . DS
              . $codePool
              . DS
              . uc_words($moduleName, DS);

        $path .= DS . $dir;

        $path = str_replace('/', DS, $path);
        
        return $path;
    }
    
    /**
     * Logs a PostNL Exception. Based on Mage::logException
     * 
     * @param Exception $exception
     * 
     * @return TIG_PostNL_Helper_Data
     * 
     * @see Mage::logException
     */
    public function logException($exception)
    {
        Mage::log("\n" . $exception->__toString(), Zend_Log::ERR, self::POSTNL_EXCEPTION_LOG_FILE);
        
        return $this;
    }
}
