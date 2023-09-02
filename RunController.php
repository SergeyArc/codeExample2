<?php

namespace app\modules\export\commands;

use Yii;
use yii\console\Controller;


use app\modules\export\services\{
    AvitoExport,
    AvitoServiceFactory,
    CianExport,
    CianServiceFactory,
    YandexExport,
    YandexServiceFactory,
    DomExport,
    DomServiceFactory
};

use app\modules\export\models\{
    ExportCard
};


class RunController extends Controller
{
    const XML_LIFETIME = 5; // min
    const XML_FILES_ALIAS = "@webroot/export/";
    const DEVELOPER_DOMCLICK_ID = '';
    const DEVELOPER_CIAN_ID = '';
    const APARTMENT_TYPE_STUDIO = 482;
    const APARTMENT_IMAGES_PATH = "uploads/images/apartment/";
    const OBJECT_IMAGES_PATH = "uploads/images/object/";


    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $path = Yii::getAlias(self::XML_FILES_ALIAS);
        return true;
    }

    public function actionCron()
    {
        $date = date('Y-m-d H:i:s', strtotime("- " . self::XML_LIFETIME . " minutes"));
        $date = date('Y-m-d H:i:s', strtotime($date . "+ 3 hours"));
        $cards = ExportCard::find()
            ->where([
                'and',
                ['=', 'active', 1],
                ['<', 'updated', $date]
            ])
            ->all();

        if (!$cards) {
            return;
        }

        foreach ($cards as $card) {

            $card->updated = date('Y-m-d H:i:s', strtotime("+ 3 hours"));
            $card->save();

            $outputFile = Yii::getAlias(self::XML_FILES_ALIAS . $card->hash . ".xml");
            $exportData = $this->getExportData($card);
            $xml = new \bupy7\xml\constructor\XmlConstructor();

            switch ($card->type->code) {
                case 'domclick':
                    $encoder = new DomServiceFactory();
                    $export = new DomExport($encoder, $xml, self::APARTMENT_IMAGES_PATH, self::OBJECT_IMAGES_PATH, self::DEVELOPER_DOMCLICK_ID);
                    echo ( $export->export($outputFile, $exportData) ) ? "DONE!\n" : "incomplete!\n";
                    break;
                case 'cian':
                    $encoder = new CianServiceFactory();
                    $export = new CianExport($encoder, $xml, self::APARTMENT_IMAGES_PATH, self::OBJECT_IMAGES_PATH, self::DEVELOPER_CIAN_ID);
                    echo ( $export->export($outputFile, $exportData) ) ? "DONE!\n" : "incomplete!\n";
                    break;
                case 'yandex':
                    $encoder = new YandexServiceFactory();
                    $export = new YandexExport($encoder, $xml, self::APARTMENT_IMAGES_PATH, self::OBJECT_IMAGES_PATH, self::APARTMENT_TYPE_STUDIO);
                    echo ( $export->export($outputFile, $exportData) ) ? "DONE!\n" : "incomplete!\n";
                    break;
                case 'avito':
                    $encoder = new AvitoServiceFactory();
                    $export = new AvitoExport($encoder, $xml, self::APARTMENT_IMAGES_PATH, self::OBJECT_IMAGES_PATH);
                    echo ( $export->export($outputFile, $exportData) ) ? "DONE!\n" : "incomplete!\n";
                    break;
            }
        }
    }

}
