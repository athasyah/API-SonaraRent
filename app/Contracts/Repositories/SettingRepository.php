<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\SettingInterface;
use App\Models\Setting;

class SettingRepository extends BaseRepository implements SettingInterface
{
    public function __construct(Setting $setting)
    {
        $this->model = $setting;
    }

    public function get()
    {
        return $this->model->all();
    }

    public function show(mixed $id)
    {
        return $this->model->find($id);
    }

    public function store(array $data)
    {
        return $this->model->create($data);
    }

    public function update(mixed $id, array $data): mixed
    {
        return $this->model->find($id)->update($data);
    }

    public function delete(mixed $id)
    {
        return $this->model->find($id)->delete();
    }

    public function getByKey(string $key)
    {
        return $this->model->where('key', $key)->first();
    }

    public function updateBatch(array $settings)
    {
        foreach ($settings as $item) {
            $this->model->updateOrCreate(
                ['key' => $item['key']],
                ['value' => $item['value']]
            );
        }
        return true;
    }
}
