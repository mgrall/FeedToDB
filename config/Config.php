<?php

namespace Mgrall\FeedToDb\config;

class Config {
    /**
     * Stores all configuration settings.
     * Add more configurations as needed.
     */
    protected static array $settings = [
        'database' => [
            'sqlite01' => [
                'type' => 'sqlite',
                'dsn' => 'sqlite:' . __DIR__ . '/../storage/database/data.db',
                'schema' => [
                    'table_name' => 'catalog',
                    'columns' => 'entity_id INTEGER,
                        CategoryName TEXT,
                        sku TEXT,
                        name TEXT,
                        description TEXT,
                        shortdesc TEXT,
                        price REAL,
                        link TEXT,
                        image TEXT,
                        Brand TEXT,
                        Rating INTEGER,
                        CaffeineType TEXT,
                        Count INTEGER,
                        Flavored TEXT,
                        Seasonal TEXT,
                        Instock TEXT,
                        Facebook INTEGER,
                        IsKCup INTEGER'],
            ],
        ],
        'data_source' => [
            'feed.xml' => [
                'type' => 'xml_feed',
                'path' => '/data/feed.xml',
            ],
        ],
        'logger' => [
            'logger01' => [
                'type' => 'FileFeedLogger',
                'path' => '/storage/logs/app.log',
            ],
        ],
    ];

    /**
     * Retrieves a configuration setting by key with optional subkeys.
     *
     * @param string $key The main configuration key.
     * @param string|null $subkey Optional subkey for deeper configuration settings.
     * @return array|array[]|null Configuration setting or null if not found.
     */
    public static function get(string $key, string $subkey = null): ?array
    {
        if ($subkey === null) {
            return self::$settings[$key] ?? null;
        }
        return self::$settings[$key][$subkey] ?? null;
    }
}