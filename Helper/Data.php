<?php

declare(strict_types=1);

namespace Ubermanu\EsImportMap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Dir as ModuleDir;

class Data extends AbstractHelper
{
    /**
     * File name for import map configuration.
     * The file contents should match the import-map spec from the W3C.
     * @see https://wicg.github.io/import-maps/
     */
    const IMPORT_MAP_FILENAME = 'import-map.json';

    public function __construct(
        Context $context,
        protected \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver,
        protected \Ubermanu\EsImportMap\Model\Theme\Dir $themeDir,
        protected \Magento\Framework\Module\ModuleList $moduleList,
        protected \Magento\Framework\Module\Dir $moduleDir,
        protected \Magento\Framework\View\Asset\Repository $assetRepo,
        protected \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
    ) {
        parent::__construct($context);
    }

    /**
     * Generate the import map for the current Magento instance.
     * Includes the import map data for the current theme and all enabled modules.
     *
     * @return array
     */
    public function getMagentoImportMap(): array
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
    protected function getThemeImportMap(): array
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
    protected function getModuleImportMap(string $module): array
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
