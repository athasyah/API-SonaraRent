<?php

namespace App\Services;

use App\Traits\UploadTrait;


class UserService
{
    use UploadTrait;

    public function mappingDataUser(array $data)
    {
        $result = [
            'name' => $data['name'],
            'email' => $data['email'],
            'number_phone' => $data['number_phone'] ?? null,
            'password' => bcrypt($data['password']),
            'email_otp' => $data['email_otp'] ?? null,
            'otp_expires_at' => $data['otp_expires_at'] ?? null,
            'email_verified_at' => $data['email_verified_at'] ?? null,
        ];

        if (isset($data['image'])) {
            $result['image'] = $this->upload('users', $data['image']);
        }

        return $result;
    }

    public function generate(string $userId): array
    {
        $otp = rand(100000, 999999);

        return [
            'user_id' => $userId,
            'otp' => bcrypt($otp),
            'expired_at' => now()->addMinutes(10),
            'plain_otp' => $otp // hanya untuk email
        ];
    }

    public function isExpired($otp): bool
    {
        return now()->greaterThan($otp->expired_at);
    }

    public function createUser(array $data)
    {
        $result = [
            'name' => $data['name'],
            'email' => $data['email'],
            'number_phone' => $data['number_phone'] ?? null,
            'password' => bcrypt('staffpassword'),
            'email_otp' => $data['email_otp'] ?? null,
            'otp_expires_at' => $data['otp_expires_at'] ?? null,
            'email_verified_at' => '2001-01-01 01:01:01',
        ];

        return $result;
    }

    public function editUser(array $data)
    {
        $result = [
            'name' => $data['name'],
            'email' => $data['email'],
            'number_phone' => $data['number_phone'] ?? null,
        ];

        return $result;
    }

    public function updateUser(array $data)
    {
        $result = [
            'name' => $data['name'] ?? null,
            'number_phone' => $data['number_phone'] ?? null,
        ];

        if (isset($data['image'])) {
            $result['image'] = $this->upload('users', $data['image']);
        }

        return $result;
    }
}
