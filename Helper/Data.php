<?php

namespace Ubermanu\EsImportMap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\View\Asset\Repository as AssetRepository;

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
    public function getMagentoImportMap()
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
     * @return string
     */
    public function getViewModuleUrl($module, $params = [])
    {
        $params = array_merge([
            '_secure' => $this->_getRequest()->isSecure(),
            'module' => $module
        ], $params);

        return $this->assetRepo->getUrlWithParams('/', $params);
    }
}
