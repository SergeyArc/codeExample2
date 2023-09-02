<?php


namespace app\modules\export\services;


interface ServiceFactory
{
    public const APARTMENT = 1;
    public const COMMERCIAL = 4;

    public function getEncoder(int $type): Encoder;
    public function getApartmentEncoder(): ApartmentEncoder;
    public function getCommercialEncoder(): CommercialEncoder;
}