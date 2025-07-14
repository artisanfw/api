<?php

namespace Artisan\Routing\Entities;

use Artisan\Routing\Exceptions\InternalServerErrorException;

class Config
{
    private static array $config = [];

    private function __construct() { }
    private function __clone(): void { }

    /**
     * @throws InternalServerErrorException
     */
    public static function load(string $configFile): void
    {
        if (!empty(self::$config)) {
            throw new InternalServerErrorException('Configuration file already loaded!');
        }

        if (!file_exists($configFile)) {
            throw new InternalServerErrorException('Configuration file not found!');
        }

        $config = self::loadConfiguration($configFile);
        if (!isset($config['environment'])) {
            $config['environment'] = ApiOptions::ENV_DEVELOPMENT;
        }
        self::$config = $config;

        define('ENVIRONMENT', $config['environment']);
    }

    public static function get(string $key, $fallback = null)
    {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }
        return $fallback;
    }


    private static function loadConfiguration(string $configFile): array
    {
        return ($configFile)
            ? require $configFile
            : [];
    }

}