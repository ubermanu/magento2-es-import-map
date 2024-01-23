<?php

declare(strict_types=1);

namespace Ubermanu\EsImportMap\Model\Theme;

use Magento\Framework\Component\ComponentRegistrar;

class Dir
{
    /**#@+
     * Directories within themes
     */
    public const THEME_ETC_DIR = 'etc';
    public const THEME_I18N_DIR = 'i18n';
    public const THEME_WEB_DIR = 'web';
    /**#@-*/

    private const ALLOWED_DIR_TYPES = [
        self::THEME_ETC_DIR => true,
        self::THEME_I18N_DIR => true,
        self::THEME_WEB_DIR => true
    ];

    public function __construct(
        protected \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
    ) {
    }

    /**
     * Retrieve full path to a directory of certain type within a theme.
     *
     * @param string $themeName
     * @param string $type
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getDir(string $themeName, string $type = ''): string
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
