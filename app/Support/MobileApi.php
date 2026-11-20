<?php

namespace App\Support;

class MobileApi
{
    public static function active(): bool
    {
        return (bool) request()->attributes->get('mobile_api');
    }

    public static function noteViewUrl(int $noteID): string
    {
        return self::active()
            ? url('api/v1/parent/files/notes/'.$noteID.'?inline=1')
            : route('subject_notes.view', $noteID);
    }

    public static function noteDownloadUrl(int $noteID): string
    {
        return self::active()
            ? url('api/v1/parent/files/notes/'.$noteID)
            : route('subject_notes.download', $noteID);
    }

    public static function packageViewUrl(int $packageID): string
    {
        return self::active()
            ? url('api/v1/parent/files/home-packages/'.$packageID.'?inline=1')
            : route('home_packages.view', $packageID);
    }

    public static function packageDownloadUrl(int $packageID): string
    {
        return self::active()
            ? url('api/v1/parent/files/home-packages/'.$packageID)
            : route('home_packages.download', $packageID);
    }
}
