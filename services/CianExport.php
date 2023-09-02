<?php


namespace app\modules\export\services;

use Yii;
use yii\helpers\Json;


class CianExport implements Export
{
    protected $cianServiceFactory;
    protected $xmlConstructor;
    protected $apartmentImagesPath;
    protected $objectImagesPath;
    protected $developerCianId;

    public function __construct($cianServiceFactory, $xmlConstructor, $apartmentImagesPath, $objectImagesPath, $developerCianId) {
        $this->cianServiceFactory = $cianServiceFactory;
        $this->xmlConstructor = $xmlConstructor;
        $this->apartmentImagesPath = $apartmentImagesPath;
        $this->objectImagesPath = $objectImagesPath;
        $this->developerCianId = $developerCianId;
    }

    public function export(string $outputFile, array $exportData): bool
    {
        $outputArray = [
            [
                'tag' => 'feed',
                'elements' => [
                    ['tag' => 'feed_version', 'content' => '2']
                ],
            ],
        ];

        foreach ($exportData['apartments'] as $apt)
        {
            $currentGP = $exportData['settings'][$apt['section']['building']['id']];
            $buildingDetails = $apt->section->building->getDetails();
            $objectDetails = $apt->section->building->object->getDetails();
            $oID = $apt->section->building->object->id;

            $images = Json::decode($apt->images);
            $imagesObject = Json::decode($objectDetails['images']);
            $imagesArray = [
                'tag' => 'Photos', 'elements' => []
            ];
            foreach ($images as $image) {
                $imagesArray['elements'][] = [
                    'tag' => 'PhotoSchema', 'elements' => [
                        ['tag' => 'FullUrl', 'content' => Yii::$app->params['app']['uri'] . $this->apartmentImagesPath . $apt->id . '/' . $image['name']],
                        ['tag' => 'IsDefault', 'content' => 'true']
                    ]
                ];
            }
            foreach ($imagesObject as $imageOb) {
                $imagesArray['elements'][] = [
                    'tag' => 'PhotoSchema', 'elements' => [
                        ['tag' => 'FullUrl', 'content' => Yii::$app->params['app']['uri'] . $this->objectImagesPath . $oID . '/' . $imageOb['name']],
                        ['tag' => 'IsDefault', 'content' => 'false']
                    ]
                ];
            }

            $roomsArray = [
                'tag' => 'RoomDefinitions', 'elements' => []
            ];
            $roomsArea = Json::decode($apt->rooms_area);
            if (!empty($roomsArea)) {
                foreach ($roomsArea as $roomArea) {
                    if (!empty($roomArea)) {
                        $roomsArray['elements'][] = [
                            'tag' => 'Room', 'elements' => [
                                ['tag' => 'Area', 'content' => $roomArea]
                            ]
                        ];
                    }
                }
            }

            $additionalData['currentGP'] = $currentGP;
            $additionalData['imagesArray'] = $imagesArray;
            $additionalData['roomsArray'] = $roomsArray;
            $additionalData['developerCianId'] = $this->developerCianId;
            $elements = $this->cianServiceFactory
                            ->getEncoder($apt->section->building->object->object_type_id)
                            ->encode($apt, $buildingDetails, $objectDetails, $additionalData);

            $outputArray[0]['elements'][] = [
                'tag' => 'object',
                'elements' => $elements
            ];
        }

        $this->xmlConstructor->fromArray($outputArray);

        file_put_contents($outputFile, $this->xmlConstructor->toOutput());

        return true;
    }
}