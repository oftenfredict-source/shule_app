<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FingerprintAttendanceSetup extends Model
{
    protected $table = 'fingerprint_attendance_setups';

    protected $primaryKey = 'setupID';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'version',
        'description',
        'environment',
        'file_path',
        'original_filename',
        'file_size',
        'status',
        'uploaded_by',
    ];

    public const ENV_LOCAL = 'local';

    public const ENV_SUBDOMAIN = 'subdomain';

    public const ENV_MAIN_DOMAIN = 'main_domain';

    public const ENV_LIVE_CPANEL = 'live_cpanel';

    public static function environmentOptions(): array
    {
        return [
            self::ENV_LOCAL => 'Local (Laragon / Dev)',
            self::ENV_SUBDOMAIN => 'Subdomain (e.g. test.shulexpert.com)',
            self::ENV_MAIN_DOMAIN => 'Main Domain',
            self::ENV_LIVE_CPANEL => 'Live cPanel',
        ];
    }

    public function environmentLabel(): string
    {
        return self::environmentOptions()[$this->environment] ?? $this->environment;
    }
}
