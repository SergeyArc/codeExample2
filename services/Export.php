<?php


namespace app\modules\export\services;


interface Export
{
    public function export(string $outputFile, array $exportData): bool;
}