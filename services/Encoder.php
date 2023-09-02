<?php


namespace app\modules\export\services;


interface Encoder
{
    public function encode($apt, $buildingDetails, $objectDetails, $additionalData = []);
}