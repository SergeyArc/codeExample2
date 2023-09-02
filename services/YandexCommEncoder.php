<?php


namespace app\modules\export\services;

use Yii;
use yii\helpers\Json;

class YandexCommEncoder extends CommercialEncoder
{
    public function encode($apt, $buildingDetails, $objectDetails, $additionalData = [])
    {
        //сопоставление commercial-type
        $type = [
            'Помещение свободного назначения' => 'free purpose',
            'Производственное помещение' => 'manufacturing',
            'Офисное помещение' => 'office',
            'Торговое помещение' => 'retail',
            'Складское помещение' => 'warehouse',
        ];

        $commercialType = 'free purpose';
        if (isset($apt->commercialType) && isset($type[$apt->commercialType->title])) {
            $commercialType = $type[$apt->commercialType->title];
        }

        $elements = [
            ['tag' => 'type', 'content' => 'продажа'],
            ['tag' => 'category', 'content' => 'коммерческая'],
            ['tag' => 'commercial-type', 'content' => $commercialType],
            ['tag' => 'creation-date', 'content' => date(\DateTime::ATOM)],
            ['tag' => 'location', 'elements' =>
                [
                    ['tag' => 'country', 'content' => $objectDetails['country']],
                    ['tag' => 'region', 'content' => $objectDetails['region']],
                    ['tag' => 'locality-name', 'content' => $objectDetails['city']],
                    ['tag' => 'address', 'content' => $buildingDetails['address']],
                ]
            ],
            ['tag' => 'sales-agent', 'elements' =>
                [
                    ['tag' => 'category', 'content' => 'застройщик'],
                    ['tag' => 'organization', 'content' => $objectDetails['dev_name']],
                    ['tag' => 'phone', 'content' => "+" . preg_replace('/[^0-9]/', '', $additionalData['currentGP']['fields']['phone']['value'])],
                ]
            ],
            ['tag' => 'price', 'elements' =>
                [
                    ['tag' => 'value', 'content' => (int)$apt->price],
                    ['tag' => 'currency', 'content' => 'RUR'],
                ]
            ],
            ['tag' => 'area', 'elements' =>
                [
                    ['tag' => 'value', 'content' => $apt->area],
                    ['tag' => 'unit', 'content' => 'кв. м'],
                ]
            ],
            ['tag' => 'floor', 'content' => $apt->floor],
            ['tag' => 'floors-total', 'content' => $buildingDetails['floors']],
            ['tag' => 'yandex-building-id', 'content' => $objectDetails['yandex_id']],
            ['tag' => 'yandex-house-id', 'content' => $buildingDetails['yandex_id']]
        ];

        return $elements;
    }
}