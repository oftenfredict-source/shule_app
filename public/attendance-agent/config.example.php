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
  'biometric_image_path' => 'images/biometric.jpg',
  'assets_server' => 'test',
  'servers' => [
    'test' => [
      'label' => 'ShuleXpert Test',
      'api_base' => 'https://test.shulexpert.com/api',
      'web_url' => 'https://test.shulexpert.com/',
    ],
    'live' => [
      'label' => 'ShuleXpert Live',
      'api_base' => 'https://shulexpert.com/api',
      'web_url' => 'https://shulexpert.com/',
    ],
    'local' => [
      'label' => 'Local Development',
      'api_base' => 'http://127.0.0.1/ShuleXpert/public/api',
      'web_url' => 'http://127.0.0.1/ShuleXpert/public/',
    ],
  ],

  'session_name' => 'sx_fingerprint_agent',
  'session_lifetime' => 43200,
];
