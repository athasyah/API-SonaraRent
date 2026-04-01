<?php

namespace App\Contracts\Interfaces;

interface SettingInterface extends BaseInterface
{
     public function getByKey(string $key);
     public function updateBatch(array $settings);
}
