<?php

namespace Ubermanu\EsImportMap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Asset\File\NotFoundException;
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

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    public function __construct(
        Context $context,
        ModuleList $moduleList,
        AssetRepository $assetRepo,
        JsonSerializer $jsonSerializer
    ) {
        parent::__construct($context);
        $this->moduleList = $moduleList;
        $this->assetRepo = $assetRepo;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Generate the import map for the current enabled modules.
     *
     * @return array
     */
    public function getMagentoImportMap()
    {
        $map = [];

        // Set the import root to target the theme asset dir
        $map['imports']['/'] = $this->assetRepo->getUrl('') . '/';

        // Inject the import-map.json file from the modules
        foreach ($this->moduleList->getNames() as $module) {
            $map = array_merge_recursive($map, $this->getViewModuleImportMap($module));
        }

        if (empty($map['scopes'])) {
            unset($map['scopes']);
        }

        return $map;
    }

    /**
     * Get the custom import map for a module.
     * The file contents should match the import-map spec from the W3C.
     *
     * @param string $module
     * @return array
     */
    public function getViewModuleImportMap($module)
    {
        try {
            $file = $this->assetRepo->createAsset($module . '::import-map.json');
            return $this->jsonSerializer->unserialize($file->getContent());
        } catch (LocalizedException | NotFoundException $e) {
            return [];
        }
    }
}
