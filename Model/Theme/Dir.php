<?php

namespace Ubermanu\EsImportMap\Model\Theme;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

class Dir
{
    /**#@+
     * Directories within themes
     */
    const THEME_ETC_DIR = 'etc';
    const THEME_I18N_DIR = 'i18n';
    const THEME_WEB_DIR = 'web';
    /**#@-*/

    private const ALLOWED_DIR_TYPES = [
        self::THEME_ETC_DIR => true,
        self::THEME_I18N_DIR => true,
        self::THEME_WEB_DIR => true
    ];

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    public function __construct(
        ComponentRegistrarInterface $componentRegistrar
    ) {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Retrieve full path to a directory of certain type within a theme
     *
     * @param string $themeName Fully-qualified theme name
     * @param string $type Type of theme's directory to retrieve
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getDir($themeName, $type = '')
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $themeName);

        if (empty($type) && !isset($path)) {
            throw new \InvalidArgumentException("Theme '$themeName' is not correctly registered.");
        }

        if ($type) {
            if (!isset(self::ALLOWED_DIR_TYPES[$type])) {
                throw new \InvalidArgumentException("Directory type '{$type}' is not recognized.");
            }
            $path .= '/' . $type;
        }

        return $path;
    }
}
