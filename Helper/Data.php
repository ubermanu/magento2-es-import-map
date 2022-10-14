<?php

namespace Ubermanu\EsImportMap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Dir as ModuleDir;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Design\Theme\ResolverInterface as ThemeResolver;
use Ubermanu\EsImportMap\Model\Theme\Dir as ThemeDir;

class Data extends AbstractHelper
{
    /**
     * File name for import map configuration.
     * The file contents should match the import-map spec from the W3C.
     * @see https://wicg.github.io/import-maps/
     */
    const IMPORT_MAP_FILENAME = 'import-map.json';

    /**
     * @var ThemeResolver
     */
    protected $themeResolver;

    /**
     * @var ThemeDir
     */
    protected $themeDir;

    /**
     * @var ModuleList
     */
    protected $moduleList;

    /**
     * @var ModuleDir
     */
    protected $moduleDir;

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
        ThemeResolver $themeResolver,
        ThemeDir $themeDir,
        ModuleList $moduleList,
        ModuleDir $moduleDir,
        AssetRepository $assetRepo,
        JsonSerializer $jsonSerializer
    ) {
        parent::__construct($context);
        $this->themeResolver = $themeResolver;
        $this->themeDir = $themeDir;
        $this->moduleList = $moduleList;
        $this->moduleDir = $moduleDir;
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

        // Add the import map for each enabled module
        foreach ($this->moduleList->getNames() as $module) {
            $map = array_merge_recursive($map, $this->getModuleImportMap($module));
        }

        // Add the import map for the current theme (and submodules)
        $map = array_merge_recursive($map, $this->getThemeImportMap());

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
        $theme = $this->themeResolver->get();
        $themePath = $this->themeDir->getDir($theme->getFullPath());

        $files = [
            $themePath . '/' . self::IMPORT_MAP_FILENAME,
        ];

        foreach ($this->moduleList->getNames() as $module) {
            $files[] = $themePath . '/' . $module . '/' . self::IMPORT_MAP_FILENAME;
        }

        $map = [];

        foreach ($files as $file) {
            if (file_exists($file)) {
                $map = array_merge_recursive($map, $this->jsonSerializer->unserialize(file_get_contents($file)));
            }
        }

        return $map;
    }

    /**
     * Get the custom import map for a module.
     *
     * @param string $module
     * @return array
     */
    public function getModuleImportMap($module)
    {
        $theme = $this->themeResolver->get();
        $path = $this->moduleDir->getDir($module, ModuleDir::MODULE_VIEW_DIR);

        // FIXME: Shouldn't the `area` file override the `base` file?
        $files = [
            $path . '/base/' . self::IMPORT_MAP_FILENAME,
            $path . '/' . $theme->getArea() . '/' . self::IMPORT_MAP_FILENAME,
        ];

        $map = [];

        foreach ($files as $file) {
            if (file_exists($file)) {
                $map = array_merge_recursive($map, $this->jsonSerializer->unserialize(file_get_contents($file)));
            }
        }

        return $map;
    }
}
