<?php


namespace app\modules\export\services;


class CianApartEncoder extends ApartmentEncoder
{
    public function encode($apt, $buildingDetails, $objectDetails, $additionalData = [])
    {
        //сопоставление поля balcony_type
        switch ($apt->balcony_type) {
            case 2:
                $loggiasCount = 0;
                $balconiesCount = 1;
                break;
            case 3:
                $loggiasCount = 1;
                $balconiesCount = 0;
                break;
            case 4:
                $loggiasCount = 0;
                $balconiesCount = 2;
                break;
            case 5:
                $loggiasCount = 2;
                $balconiesCount = 0;
                break;
            default:
                $loggiasCount = 0;
                $balconiesCount = 0;
                break;
        }
        //сопоставление wall_decoration
        $decoration = [
            'без отделки' => 'without',
            'черновая' => 'rough',
            'чистовая' => 'fine',
        ];

        //сопоставление window_view
        $windowsViewType = [
            'во двор' => 'yard',
            'на улицу' => 'street',
            'во двор и на улицу' => 'yardAndStreet',
        ];

        //сопоставление building_type
        $materialType = [
            'панельный' => 'panel',
            'монолитный' => 'monolith',
            'кирпичный' => 'brick',
            'кирпично-монолитный' => '',
            'блочный' => '',
            'деревянный' => '',
        ];

        $elements = [
            ['tag' => 'Category', 'content' => 'newBuildingFlatSale'],
            ['tag' => 'Description', 'content' => $objectDetails['description']],
            ['tag' => 'Address', 'content' => $objectDetails['address']],
            ['tag' => 'ExternalId', 'content' => $apt->id],
            ['tag' => 'FlatRoomsCount', 'content' => $apt->rooms],
            ['tag' => 'IsApartments', 'content' => 'false'],
            ['tag' => 'Phones', 'elements' => [
                ['tag' => 'PhoneSchema', 'elements' => [
                    ['tag' => 'CountryCode', 'content' => '+7'],
                    ['tag' => 'Number', 'content' => "+" . preg_replace('/[^0-9]/', '', $additionalData['currentGP']['fields']['phone']['value'])],
                ]
                ],
            ]
            ],
            ['tag' => 'TotalArea', 'content' => $apt->area],
            ['tag' => 'FloorNumber', 'content' => $apt->floor],
            ['tag' => 'LayoutPhoto', 'elements' => [
                ['tag' => 'FullUrl', 'content' => $apt->plan ? $apt->plan->getUploadUrl('file') : null],
                ['tag' => 'IsDefault', 'content' => 1],
            ]
            ],
            ['tag' => 'JKSchema', 'elements' => [
                ['tag' => 'Id', 'content' => $objectDetails['cian_id']],
                ['tag' => 'Name', 'content' => $objectDetails['name']],
                ['tag' => 'House', 'elements' => [
                    ['tag' => 'Name', 'content' => $apt->section->building['title']],
                    ['tag' => 'Flat', 'elements' => [
                        ['tag' => 'FlatNumber', 'content' => $apt->number],
                        ['tag' => 'SectionNumber', 'content' => $apt->section['title']],
                    ]
                    ],
                ]
                ],
                ['tag' => 'Developer', 'elements' => [
                    ['tag' => 'Id', 'content' => $additionalData['developerCianId']], // ид из базы циан
                    ['tag' => 'Name', 'content' => $objectDetails['dev_name']]
                ]
                ],
            ]
            ],
            $additionalData['imagesArray'],
            $additionalData['roomsArray'],
            // заполнить тег
            ['tag' => 'Building', 'elements' => [
                ['tag' => 'FloorsCount', 'content' => $buildingDetails['floors']],
                ['tag' => 'MaterialType', 'content' => $materialType[$buildingDetails['building_type']]],
                ['tag' => 'Deadline', 'elements' => [
                    ['tag' => 'Quarter', 'content' => $buildingDetails['ready_quarter']],
                    ['tag' => 'Year', 'content' => $buildingDetails['built_year']],
                ]],
            ]
            ],
            ['tag' => 'BargainTerms', 'elements' => [
                ['tag' => 'Price', 'content' => $apt->price],
                // ['tag' => 'SaleType', 'content' => 'alternative'],
                ['tag' => 'currency', 'content' => 'rur'],
            ]
            ],
            ['tag' => 'LoggiasCount', 'content' => $loggiasCount],
            ['tag' => 'BalconiesCount', 'content' => $balconiesCount],
            ['tag' => 'Decoration', 'content' => $decoration[$buildingDetails['wall_decoration']]],
            ['tag' => 'WindowsViewType', 'content' => $windowsViewType[$apt->window_view]],
        ];

        return $elements;
    }
}