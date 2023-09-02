<?php


namespace app\modules\export\services;


abstract class ApartmentEncoder implements Encoder
{
    abstract public function encode($apt, $buildingDetails, $objectDetails, $additionalData = []);
}