<?php


namespace app\modules\export\services;

use Yii;
use yii\helpers\Json;


class DomExport implements Export
{
    protected $domServiceFactory;
    protected $xmlConstructor;
    protected $apartmentImagesPath;
    protected $objectImagesPath;
    protected $developerDomclickId;

    public function __construct($domServiceFactory, $xmlConstructor, $apartmentImagesPath, $objectImagesPath, $developerDomclickId) {
        $this->domServiceFactory = $domServiceFactory;
        $this->xmlConstructor = $xmlConstructor;
        $this->apartmentImagesPath = $apartmentImagesPath;
        $this->objectImagesPath = $objectImagesPath;
        $this->developerDomclickId = $developerDomclickId;
    }

    public function export(string $outputFile, array $exportData): bool
    {
        $objectsArray = [];

        foreach ($exportData['apartments'] as $apt) {

            $currentGP = $exportData['settings'][$apt['section']['building']['id']];

            $buildingDetails = $apt->section->building->getDetails();
            $objectDetails = $apt->section->building->object->getDetails();

            //Yii::info(['$objectDetails' => $objectDetails], 'export');

            if (isset($oID)) { $oID_old = $oID; }

            $oID = $apt->section->building->object->id;
            $bID = $apt->section->building->id;

            if (isset($oID_old) && $oID != $oID_old) {
                unset($buildingsArray);
                unset($aptsArray);
            }

            $roomsArea = Json::decode($apt->rooms_area);
            $roomsAreaArray = [
                'tag' => 'rooms_area',
                'elements' => []
            ];

            if (empty($roomsArea)) {
                $roomsAreaArray['elements'][] = [
                    ['tag' => 'area', 'content' => '0']
                ];
            } else {
                foreach ($roomsArea as $roomArea) {
                    $roomsAreaArray['elements'][] = [
                        ['tag' => 'area', 'content' => $roomArea]
                    ];
                }
            }

            //сопоставление balcony_type
            $balconyOrLoggia = [
                1 => 'Нет',
                2 => 'Балкон',
                3 => 'Лоджия',
                4 => 'Балкон',
                5 => 'Лоджия',
            ];

            $aptsArray[] = [
                'tag' => 'flat',
                'elements' => [
                    ['tag' => 'flat_id', 'content' => $apt->id],
                    ['tag' => 'apartment', 'content' => $apt->number],
                    ['tag' => 'floor', 'content' => $apt->floor],
                    ['tag' => 'room', 'content' => $apt->rooms],
                    ['tag' => 'price', 'content' => $apt->price],
                    ['tag' => 'area', 'content' => $apt->area],
                    ['tag' => 'plan', 'content' => $apt->plan ? $apt->plan->getUploadUrl('file') : null],
                    ['tag' => 'balcony', 'content' => ($apt->balcony_type == 4 || $apt->balcony_type == 5) ? 'больше 2' : $balconyOrLoggia[$apt->balcony_type]],
                    ['tag' => 'kitchen_area', 'content' => $apt->area_kitchen],
                    ['tag' => 'living_area', 'content' => $apt->living_area],
                    ['tag' => 'window_view', 'content' => $apt->window_view],
                    ['tag' => 'bathroom', 'content' => ($apt->bathroom_type == 'два' || $apt->bathroom_type == 'три') ? 'более 2' : $apt->bathroom_type],
                    ['tag' => 'renovation', 'content' => ($buildingDetails['wall_decoration'] == 'нет' ? 'нет' : 'да')],
                    $roomsAreaArray
                ]
            ];

            $buildingArray = [
                'tag' => 'building',
                'elements' => [
                    ['tag' => 'id', 'content' => $buildingDetails['domclick_id']],
                    ['tag' => 'name', 'content' => $apt->section->building->title],
                    ['tag' => 'fz_214', 'content' => $buildingDetails['fz_214']],
                    ['tag' => 'floors', 'content' => $buildingDetails['floors']],
                    ['tag' => 'floors_ready', 'content' => $buildingDetails['floors_ready']],
                    ['tag' => 'building_state', 'content' => $buildingDetails['building_state']],
                    ['tag' => 'built_year', 'content' => $buildingDetails['built_year']],
                    ['tag' => 'ready_quarter', 'content' => $buildingDetails['ready_quarter']],
                    ['tag' => 'building_phase', 'content' => $buildingDetails['building_phase']],
                    ['tag' => 'building_type', 'content' => $buildingDetails['building_type']],
                    ['tag' => 'image', 'content' => ''],
                    ['tag' => 'flats', 'elements' => $aptsArray],
                ]
            ];

            $buildingsArray[$bID] = $buildingArray;

            $objectArray = [
                ['tag' => 'id', 'content' => trim($objectDetails['domclick_id'])],
                ['tag' => 'name', 'content' => $apt->section->building->object->title],
                ['tag' => 'latitude', 'content' => $objectDetails['latitude']],
                ['tag' => 'longitude', 'content' => trim($objectDetails['longitude'])],
                ['tag' => 'address', 'content' => $objectDetails['address']],
                ['tag' => 'built_year', 'content' => $objectDetails['built_year']],
                ['tag' => 'ready_quarter', 'content' => $objectDetails['ready_quarter']],
                ['tag' => 'description_main', 'elements' => [
                    ['tag' => 'title', 'content' => $objectDetails['description_title']],
                    ['tag' => 'text', 'content' => $objectDetails['description']]
                ]],
                ['tag' => 'description_secondary', 'content' => ''],
            ];


            $objectArray['images'] = [
                'tag' => 'images',
                'elements' => []
            ];

            $images = Json::decode($objectDetails['images']);

            foreach ($images as $image) {
                $objectArray['images']['elements'][] = [
                    'tag' => 'image', 'content' => Yii::$app->params['app']['uri'] . $this->objectImagesPath . $oID . '/' . $image['name']
                ];
            }

            $objectArray['profits_main'] = [];
            $utp = Json::decode($objectDetails['utp']);
            foreach ($utp as $utpItem) {
                $objectArray['profits_main'][] = [
                    'tag' => 'profit_main',
                    'elements' => [
                        ['tag' => 'title', 'content' => $utpItem['title']],
                        ['tag' => 'text', 'content' => $utpItem['description']],
                        ['tag' => 'image', 'content' => $utpItem['link']],
                    ]
                ];
            }

            $objectArray['buildings'] = [
                'tag' => 'buildings',
                'elements' => $buildingsArray ?? []
            ];

            $objectArray['developer'] = [
                'tag' => 'developer',
                'elements' => [
                    ['tag' => 'id', 'content' => $this->developerDomclickId],
                    ['tag' => 'name', 'content' => $objectDetails['dev_name']],
                    ['tag' => 'phone', 'content' => "+" . preg_replace('/[^0-9]/', '', $currentGP['fields']['phone']['value'])],
                    ['tag' => 'site', 'content' => Yii::$app->params['app']['uri']],
                    ['tag' => 'logo', 'content' => Yii::$app->params['app']['uri'] . 'images/logo.png'],
                ]
            ];

            $objectArray['sales_info'] = [
                'tag' => 'sales_info',
                'elements' => [
                    ['tag' => 'sales_phone', 'content' => "+" . preg_replace('/[^0-9]/', '', $currentGP['fields']['phone']['value'])],
                    ['tag' => 'responsible_officer_phone', 'content' => "+" . preg_replace('/[^0-9]/', '', $currentGP['fields']['phone']['value'])],
                    ['tag' => 'sales_latitude', 'content' => '57.146291'],
                    ['tag' => 'sales_longitude', 'content' => '65.556031'],
                    ['tag' => 'timezone', 'content' => '+5'],

                ]
            ];


            $objectsArray[$oID] = [
                'tag' => 'complex',
                'elements' => $objectArray,
            ];
        }

        $outputArray = [
            [
                'tag' => 'complexes',
                'elements' => $objectsArray,
            ],
        ];

        $this->xmlConstructor->fromArray($outputArray);

        file_put_contents($outputFile, $this->xmlConstructor->toOutput());

        return true;
    }
}