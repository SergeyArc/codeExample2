<?php


namespace app\modules\export\services;

use Yii;
use yii\helpers\Json;


class AvitoExport implements Export
{
    protected $avitoServiceFactory;
    protected $xmlConstructor;
    protected $apartmentImagesPath;
    protected $objectImagesPath;

    public function __construct($avitoServiceFactory, $xmlConstructor, $apartmentImagesPath, $objectImagesPath) {
        $this->avitoServiceFactory = $avitoServiceFactory;
        $this->xmlConstructor = $xmlConstructor;
        $this->apartmentImagesPath = $apartmentImagesPath;
        $this->objectImagesPath = $objectImagesPath;
    }

    public function export(string $outputFile, array $exportData): bool
    {
        $outputArray = [
            [
                'tag' => 'Ads',
                'attributes' => [
                    'formatVersion' => '3',
                    'target' => 'Avito.ru'
                ],
                'elements' => [],
            ],
        ];

        foreach ($exportData['apartments'] as $apt)
        {
            $currentGP = $exportData['settings'][$apt['section']['building']['id']];
            $buildingDetails = $apt->section->building->getDetails();
            $objectDetails = $apt->section->building->object->getDetails();
            $obID = $apt->section->building->object->id;

            $images = Json::decode($apt->images);
            $imagesObject = Json::decode($objectDetails['images']);

            $imagesArray = [
                'tag' => 'Images', 'elements' => [
                    ['tag' => 'Image', 'attributes' =>
                        [
                            'url' => $apt->plan ? $apt->plan->getUploadUrl('file') : null
                        ]
                    ]
                ]
            ];
            foreach ($images as $image) {
                $imagesArray['elements'][] = [
                    'tag' => 'Image', 'attributes' =>
                    [
                        'url' => Yii::$app->params['app']['uri'] . $this->apartmentImagesPath . $apt->id . '/' . $image['name']
                    ]
                ];
            }
            foreach ($imagesObject as $imageOb) {
                $imagesArray['elements'][] = [
                    'tag' => 'Image', 'attributes' => [
                        'url' => Yii::$app->params['app']['uri'] . $this->objectImagesPath . $obID . '/' . $imageOb['name']
                    ]
                ];
            }

            $additionalData['currentGP'] = $currentGP;
            $additionalData['imagesArray'] = $imagesArray;
            $elements = $this->avitoServiceFactory
                            ->getEncoder($apt->section->building->object->object_type_id)
                            ->encode($apt, $buildingDetails, $objectDetails, $additionalData);

            $outputArray[0]['elements'][] = [
                'tag' => 'Ad',
                'elements' => $elements
            ];
        }

        $this->xmlConstructor->fromArray($outputArray);

        file_put_contents($outputFile, $this->xmlConstructor->toOutput());

        return true;
    }
}