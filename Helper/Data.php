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
     * File name for import map configuration.
     * The file contents should match the import-map spec from the W3C.
     * @see https://wicg.github.io/import-maps/
     */
    const IMPORT_MAP_FILENAME = 'import-map.json';

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
        $map = array_merge_recursive($map, $this->getThemeImportMap());

        foreach ($this->moduleList->getNames() as $module) {
            $map = array_merge_recursive($map, $this->getModuleImportMap($module));
        }

        if (empty($map['scopes'])) {
            unset($map['scopes']);
        }

        return $map;
    }

    /**
     * Get the custom import map for the current theme.
     *
     * @return array
     */
    public function getThemeImportMap()
    {
        try {
            $file = $this->assetRepo->createAsset(self::IMPORT_MAP_FILENAME);
            return $this->jsonSerializer->unserialize($file->getContent());
        } catch (LocalizedException | NotFoundException $e) {
            return [];
        }
    }

    /**
     * Get the custom import map for a module.
     *
     * @param string $module
     * @return array
     */
    public function getModuleImportMap($module)
    {
        try {
            $file = $this->assetRepo->createAsset($module . '::' . self::IMPORT_MAP_FILENAME);
            return $this->jsonSerializer->unserialize($file->getContent());
        } catch (LocalizedException | NotFoundException $e) {
            return [];
        }
    }
}
