<?php


namespace app\modules\export\services;


class YandexApartEncoder extends ApartmentEncoder
{
    public function encode($apt, $buildingDetails, $objectDetails, $additionalData = [])
    {
        $elements = [
            ['tag' => 'creation-date', 'content' => date(\DateTime::ATOM)],
            ['tag' => 'last-update-date', 'content' => date(\DateTime::ATOM)],
            ['tag' => 'type', 'content' => 'продажа'],
            ['tag' => 'property-type', 'content' => 'жилая'],
            ['tag' => 'category', 'content' => 'квартира'],
            ['tag' => 'new-flat', 'content' => '1'],
            ['tag' => 'deal-status', 'content' => 'продажа от застройщика'],
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
            ['tag' => 'floors-total', 'content' => $buildingDetails['floors']],
            ['tag' => 'building-name', 'content' => $objectDetails['name']],
            ['tag' => 'yandex-building-id', 'content' => $objectDetails['yandex_id']],
            ['tag' => 'yandex-house-id', 'content' => $buildingDetails['yandex_id']],
            ['tag' => 'building-section', 'content' => $apt->section->building->title],
            ['tag' => 'building-state', 'content' => $buildingDetails['building_state']],
            ['tag' => 'ready-quarter', 'content' => $buildingDetails['ready_quarter']],
            ['tag' => 'built-year', 'content' => $buildingDetails['built_year']],
            ['tag' => 'building-type', 'content' => $buildingDetails['building_type']],
            ['tag' => 'floor', 'content' => $apt->floor],
            ['tag' => 'area', 'elements' =>
                [
                    ['tag' => 'value', 'content' => $apt->area],
                    ['tag' => 'unit', 'content' => 'кв. м'],
                ]
            ],
            ['tag' => 'price', 'elements' =>
                [
                    ['tag' => 'value', 'content' => (int)$apt->price],
                    ['tag' => 'currency', 'content' => 'RUR'],
                ]
            ],
        ];

        if ($apt->type == $additionalData['apartmentTypeStudio']) {
            $elements[] = ['tag' => 'studio', 'content' => true];
        } else {
            $elements[] = ['tag' => 'rooms', 'content' => ($apt->rooms == 0 ? 1 : $apt->rooms)];
        }

        return $elements;
    }
}