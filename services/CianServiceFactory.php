<?php


namespace app\modules\export\services;


class CianServiceFactory implements ServiceFactory
{
    public function getEncoder(int $type): Encoder
    {
        switch ($type)
        {
            case self::APARTMENT:
                return $this->getApartmentEncoder();
            case self::COMMERCIAL:
                return $this->getCommercialEncoder();
        }
    }

    public function getApartmentEncoder(): ApartmentEncoder
    {
        return new CianApartEncoder();
    }

    public function getCommercialEncoder(): CommercialEncoder
    {
        return new CianCommEncoder();
    }
}