<?php


namespace app\modules\export\services;


class AvitoCommEncoder extends CommercialEncoder
{
    public function encode($apt, $buildingDetails, $objectDetails, $additionalData = [])
    {
        //сопоставление wall_decoration
        $wallDecoration = [
            'без отделки' => 'Без отделки',
            'черновая' => 'Без отделки',
            'чистовая' => 'Чистовая',
            'офисная' => 'Офисная'
        ];

        $elements = [
            ['tag' => 'Id', 'content' => $apt->id],
            ['tag' => 'Description', 'content' => $objectDetails['description_avito'] ?: $objectDetails['description']],
            ['tag' => 'Address', 'content' => $objectDetails['address']],
            ['tag' => 'Category', 'content' => 'Коммерческая недвижимость'],
            ['tag' => 'Price', 'content' => (int)$apt->price],
            ['tag' => 'OperationType', 'content' => 'Продам'],
            ['tag' => 'ObjectType', 'content' => (isset($apt->commercialType)) ? $apt->commercialType->title : "Помещение свободного назначения"],
            ['tag' => 'PropertyRights', 'content' => 'Собственник'],
            ['tag' => 'Entrance', 'content' => (isset($apt->entranceType)) ? $apt->entranceType->title : "С улицы"],
            ['tag' => 'Floor', 'content' => $apt->floor],
            ['tag' => 'Layout', 'content' => (isset($apt->layoutType)) ? $apt->layoutType->title : "Открытая"],
            ['tag' => 'Square', 'content' => $apt->area],
            ['tag' => 'Decoration', 'content' => $wallDecoration[$buildingDetails['wall_decoration']] ?: 'Без отделки'],
            ['tag' => 'ParkingType', 'content' => (isset($apt->parkingType)) ? $apt->parkingType->title : "Нет"],
            ['tag' => 'TransactionType', 'content' => 'Продажа'],
            $additionalData['imagesArray'],
            ['tag' => 'ContactPhone', 'content' => "+" . preg_replace('/[^0-9]/', '', $additionalData['currentGP']['fields']['phone']['value'])]
        ];

        return $elements;
    }
}