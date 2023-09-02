<?php


namespace app\modules\export\services;


class CianCommEncoder extends CommercialEncoder
{
    public function encode($apt, $buildingDetails, $objectDetails, $additionalData = [])
    {
        //сопоставление wall_decoration
        $decoration = [
            'без отделки' => 'majorRepairsRequired',
            'черновая' => 'majorRepairsRequired',
            'чистовая' => 'typical',
        ];

        $elements = [
            ['tag' => 'Category', 'content' => (isset($apt->commercialType)) ? $apt->commercialType->title : "freeAppointmentObjectSale"],
            ['tag' => 'ExternalId', 'content' => $apt->id],
            ['tag' => 'Description', 'content' => $objectDetails['description']],
            ['tag' => 'Address', 'content' => $objectDetails['address']],
            ['tag' => 'Phones', 'elements' =>
                [
                    ['tag' => 'PhoneSchema', 'elements' =>
                        [
                            ['tag' => 'CountryCode', 'content' => '+7'],
                            ['tag' => 'Number', 'content' => "+" . preg_replace('/[^0-9]/', '', $additionalData['currentGP']['fields']['phone']['value'])],
                        ]
                    ],
                ]
            ],
            ['tag' => 'TotalArea', 'content' => $apt->area],
            ['tag' => 'FloorNumber', 'content' => $apt->floor],
            ['tag' => 'ConditionType', 'content' => $decoration[$buildingDetails['wall_decoration']]],
            ['tag' => 'LayoutPhoto', 'elements' => [
                ['tag' => 'FullUrl', 'content' => $apt->plan ? $apt->plan->getUploadUrl('file') : null],
                ['tag' => 'IsDefault', 'content' => 1],
            ]
            ],
            ['tag' => 'JKSchema', 'elements' =>
                [
                    ['tag' => 'Id', 'content' => $objectDetails['cian_id']],
                    ['tag' => 'Name', 'content' => $objectDetails['name']],
                    ['tag' => 'House', 'elements' =>
                        [
                            ['tag' => 'Name', 'content' => $apt->section->building['title']],
                        ]
                    ]
                ]
            ],
            ['tag' => 'Building', 'elements' =>
                [
                    ['tag' => 'FloorsCount', 'content' => $buildingDetails['floors']],
                    ['tag' => 'Developer', 'content' => $objectDetails['dev_name']]
                ]
            ],
            $additionalData['imagesArray'],
            ['tag' => 'BargainTerms', 'elements' =>
                [
                    ['tag' => 'Price', 'content' => $apt->price],
                    ['tag' => 'currency', 'content' => 'rur'],
                ]
            ]
        ];

        return $elements;
    }
}