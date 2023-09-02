<?php


namespace app\modules\export\services;


abstract class CommercialEncoder implements Encoder
{
    abstract public function encode($apt, $buildingDetails, $objectDetails, $additionalData = []);
}