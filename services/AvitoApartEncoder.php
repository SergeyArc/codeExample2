<?php


namespace app\modules\export\services;


class AvitoApartEncoder extends ApartmentEncoder
{
    public function encode($apt, $buildingDetails, $objectDetails, $additionalData = [])
    {
        //сопоставление building_type
        $houseType = [
            'панельный' => 'Панельный',
            'монолитный' => 'Монолитный',
            'кирпичный' => 'Кирпичный',
            'кирпично-монолитный' => 'Монолитно-кирпичный',
            'блочный' => 'Блочный',
            'деревянный' => 'Деревянный',
        ];
        //сопоставление balcony_type
        $balconyOrLoggia = [
            0 => 'нет',
            1 => 'Балкон + Лоджия',
            2 => 'Балкон',
            3 => 'Лоджия',
            4 => 'два балкона',
            5 => 'две лоджии',
        ];
        //сопоставление room_type
        $roomType = [
            0 => 'нет',
            1 => 'Смежные',
            2 => 'Изолированные',
        ];
        //сопоставление window_view
        $viewFromWindows = [
            'во двор' => 'Во двор',
            'на улицу' => 'На улицу',
            'во двор и на улицу' => 'На улицу | Во двор',
        ];
        //сопоставление bathroom_type
        $bathroom = [
            'совмещенный' => 'Совмещенный',
            'раздельный' => 'Раздельный',
            'два' => 'Несколько',
            'три' => 'Несколько',
        ];
        //сопоставление wall_decoration
        $wallDecoration = [
            'без отделки' => 'Без отделки',
            'черновая' => 'Предчистовая',
            'чистовая' => 'Чистовая'
        ];

        $elements = [
            ['tag' => 'Id', 'content' => $apt->id],
            ['tag' => 'Category', 'content' => 'Квартиры'],
            ['tag' => 'PropertyRights', 'content' => 'Застройщик'],
            ['tag' => 'OperationType', 'content' => 'Продам'],
            ['tag' => 'Description', 'content' => $objectDetails['description_avito'] ?: $objectDetails['description']],
            ['tag' => 'Address', 'content' => ''], //$objectDetails['address']],
            ['tag' => 'Price', 'content' => (int)$apt->price],
            ['tag' => 'CompanyName', 'content' => $objectDetails['dev_name']],
            ['tag' => 'AllowEmail', 'content' => 'Нет'],
            ['tag' => 'ContactPhone', 'content' => "+" . preg_replace('/[^0-9]/', '', $additionalData['currentGP']['fields']['phone']['value'])],
            $additionalData['imagesArray'],
            ['tag' => 'Rooms', 'content' => $apt->rooms],
            ['tag' => 'Square', 'content' => $apt->area],
            ['tag' => 'Floor', 'content' => $apt->floor],
            ['tag' => 'Floors', 'content' => $apt->section['floors']], //$buildingDetails['floors']],
            ['tag' => 'MarketType', 'content' => 'Новостройка'],
            ['tag' => 'Status', 'content' => 'Квартира'],
            ['tag' => 'NewDevelopmentId', 'content' => $buildingDetails['avito_id'] ?: $objectDetails['avito_id']],
            ['tag' => 'Decoration', 'content' => $wallDecoration[$buildingDetails['wall_decoration']]],
            ['tag' => 'KitchenSpace', 'content' => $apt->area_kitchen],
            //  ['tag' => 'LivingSpace', 'content' => $apt->living_area],
            ['tag' => 'HouseType', 'content' => $houseType[$buildingDetails['building_type']]],
            ['tag' => 'ApartmentNumber', 'content' => $apt->number],
            ['tag' => 'ViewFromWindows', 'content' => $viewFromWindows[$apt->window_view]],
            ['tag' => 'Bathroom', 'content' => $bathroom[$apt->bathroom_type]],
        ];

        switch ($apt->room_type) {
            case 1:
                $elements[] = ['tag' => 'RoomType',
                    'elements' => [
                        ['tag' => 'Option', 'content' => $roomType[$apt->room_type]],
                    ]
                ];
                break;
            case 2:
                $elements[] = ['tag' => 'RoomType',
                    'elements' => [
                        ['tag' => 'Option', 'content' => $roomType[$apt->room_type]],
                    ]
                ];
                break;
            default:
                $elements[] = ['tag' => 'RoomType', 'content' => ''];
                break;
        }

        switch ($apt->balcony_type) {
            case 1:
                $elements[] = ['tag' => 'BalconyOrLoggiaMulti',
                    'elements' => [
                        ['tag' => 'Option', 'content' => $balconyOrLoggia[2]],
                        ['tag' => 'Option', 'content' => $balconyOrLoggia[3]],
                    ]
                ];
                break;
            case 2:
                $elements[] = ['tag' => 'BalconyOrLoggia', 'content' => $balconyOrLoggia[$apt->balcony_type]];
                break;
            case 3:
                $elements[] = ['tag' => 'BalconyOrLoggia', 'content' => $balconyOrLoggia[$apt->balcony_type]];
                break;
            default:
                $elements[] = ['tag' => 'BalconyOrLoggia', 'content' => ''];
                break;
        }

        return $elements;
    }
}