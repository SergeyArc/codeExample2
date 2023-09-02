<?php


namespace app\modules\export\services;

use Yii;
use yii\helpers\Json;


class YandexExport implements Export
{
    protected $yandexServiceFactory;
    protected $xmlConstructor;
    protected $apartmentImagesPath;
    protected $objectImagesPath;
    protected $apartmentTypeStudio;

    public function __construct($yandexServiceFactory, $xmlConstructor, $apartmentImagesPath, $objectImagesPath, $apartmentTypeStudio) {
        $this->yandexServiceFactory = $yandexServiceFactory;
        $this->xmlConstructor = $xmlConstructor;
        $this->apartmentImagesPath = $apartmentImagesPath;
        $this->objectImagesPath = $objectImagesPath;
        $this->apartmentTypeStudio = $apartmentTypeStudio;
    }

    public function export(string $outputFile, array $exportData): bool
    {
        $outputArray = [
            [
                'tag' => 'realty-feed',
                'attributes' => [
                    'xmlns' => 'http://webmaster.yandex.ru/schemas/feed/realty/2010-06'
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

            $imagesArray = [];
            if ($apt->plan) {
                $imagesArray[] = ['tag' => 'image', 'content' => $apt->plan->getUploadUrl('file')];
            }
            foreach ($images as $image) {
                $imagesArray[] = ['tag' => 'image', 'content' => Yii::$app->params['app']['uri'] . $this->apartmentImagesPath . $apt->id . '/' . $image['name']];
            }
            foreach ($imagesObject as $imageOb) {
                $imagesArray[] = ['tag' => 'image', 'content' => Yii::$app->params['app']['uri'] . $this->objectImagesPath . $obID . '/' . $imageOb['name']];
            }

            $additionalData['currentGP'] = $currentGP;
            $additionalData['apartmentTypeStudio'] = $this->apartmentTypeStudio;
            $elements = $this->yandexServiceFactory
                            ->getEncoder($apt->section->building->object->object_type_id)
                            ->encode($apt, $buildingDetails, $objectDetails, $additionalData);

            $outputArray[0]['elements'][] = [
                'tag' => 'offer',
                'attributes' => ['internal-id' => $apt->id],
                'elements' => array_merge($elements, $imagesArray)
            ];
        }

        $this->xmlConstructor->fromArray($outputArray);

        file_put_contents($outputFile, $this->xmlConstructor->toOutput());

        return true;
    }
}