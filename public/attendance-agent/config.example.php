<?php

/**
 * Copy to config.php and adjust if needed.
 */
return [
  'app_name' => 'ShuleXpert Fingerprint Attendance System',
  'powered_by' => 'EmCa Techonologies',

  // Default server key: test | live | local
  'default_server' => 'test',
  'ssl_verify' => true,
  'ca_bundle' => null,
  'biometric_image_path' => 'images/biometric.jpg',
  'assets_server' => 'test',
  'servers' => [
    'live' => [
      'label' => 'Live',
      'api_base' => 'https://shulexpert.com/api',
      'web_url' => 'https://shulexpert.com/',
    ],
    'test' => [
      'label' => 'Test',
      'api_base' => 'https://test.shulexpert.com/api',
      'web_url' => 'https://test.shulexpert.com/',
    ],
    'local' => [
      'label' => 'Local Development',
      'api_base' => 'http://127.0.0.1:8003/api',
      'web_url' => 'http://127.0.0.1:8003/',
    ],
  ],

  'session_name' => 'sx_fingerprint_agent',
  'session_lifetime' => 43200,

  // Web path where this folder is served (no trailing slash). Leave empty for auto-detect.
  'web_path' => 'attendance-agent',
];
