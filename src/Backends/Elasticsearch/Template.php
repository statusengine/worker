<?php
/**
 * Statusengine UI
 * Copyright (C) 2017  Daniel Ziegler
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Statusengine\Elasticsearch;


use Statusengine\Config;

class Template {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var string
     */
    private $index;

    /**
     * Template constructor.
     * @param Config $Config
     * @param string $index
     */
    public function __construct(Config $Config, $index) {
        $this->Config = $Config;
        $this->index = $index;
    }

    /**
     * @return array
     */
    public function getTemplate() {
        $tempalteConfig = $this->Config->getElasticsearchTemplate();
        return [
            'name' => $tempalteConfig['name'],
            'create' => false,
            'body' => [
                'template' => sprintf('%s*', $this->index),
                'settings' => [
                    'number_of_shards' => $tempalteConfig['number_of_shards'],
                    'number_of_replicas' => $tempalteConfig['number_of_replicas'],
                    'refresh_interval' => $tempalteConfig['refresh_interval'],
                    'codec' => $tempalteConfig['codec'],
                    'mapper.dynamic' => false

                ],
                'mappings' => [
                    '_default_' => [
                        '_all' => [
                            'enabled' => $tempalteConfig['enable_all']
                        ],
                        '_source' => [
                            'enabled' => $tempalteConfig['enable_source']
                        ]
                    ],
                    'metric' => [
                        'properties' => [
                            '@timestamp' => [
                                'type' => 'date'
                            ],
                            'value' => [
                                'type' => 'double',
                                'index' => false
                            ],
                            'hostname' => [
                                'type' => 'keyword'
                            ],
                            'service_description' => [
                                'type' => 'keyword'
                            ],
                            'metric' => [
                                'type' => 'keyword'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

}