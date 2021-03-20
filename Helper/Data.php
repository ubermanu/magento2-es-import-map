<?php

namespace Ubermanu\EsImportMap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Class Data
 * @package Ubermanu\EsImportMap\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var ModuleList
     */
    protected $moduleList;

    /**
     * @var AssetRepository
     */
    protected $assetRepo;

    /**
     * Data constructor.
     * @param Context $context
     * @param ModuleList $moduleList
     * @param AssetRepository $assetRepo
     */
    public function __construct(
        Context $context,
        ModuleList $moduleList,
        AssetRepository $assetRepo
    ) {
        parent::__construct($context);
        $this->moduleList = $moduleList;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Generate the import map for the current enabled modules.
     *
     * @return array
     */
    public function getModulesImportMap()
    {
        $map = [];
        foreach ($this->moduleList->getNames() as $module) {
            $map[$module . '/'] = $this->getViewModuleUrl($module) . '/';
        }
        return $map;
    }

    /**
     * Get the public url of a module.
     *
     * @param string $module
     * @param array $params
     * @return false|string
     */
    public function getViewModuleUrl($module, $params = [])
    {
        try {
            $params = array_merge([
                '_secure' => $this->_getRequest()->isSecure(),
                'module' => $module
            ], $params);
            return $this->assetRepo->getUrlWithParams('/', $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return false;
        }
    }
}
