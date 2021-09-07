<?php
namespace Qbus\SubsiteGenerator\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * ConfigurationService
 *
 * @author Benjamin Franzke <bfr@qbus.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ConfigurationService implements SingletonInterface
{
    /**
     * Current configuration
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * Build up the configuration
     */
    public function __construct()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['subsite_generator'])) {
            $extensionConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('subsite_generator');
            if (is_array($extensionConfig)) {
                $this->configuration = $extensionConfig;
            }
        }
    }

    /**
     * Get the configuration
     *
     * @param string $key
     *
     * @return null|mixed
     */
    public function get($key)
    {
        $result = null;
        if (isset($this->configuration[$key])) {
            $result = $this->configuration[$key];
        }

        return $result;
    }
}
