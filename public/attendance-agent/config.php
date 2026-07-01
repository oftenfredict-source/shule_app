<?php

return [
  'app_name' => 'ShuleXpert Fingerprint Attendance System',
  'powered_by' => 'EmCa Techonologies',
  'default_server' => 'test',
  'ssl_verify' => true,

  // Login / splash image — loaded from ShuleXpert server (not local disk)
  'biometric_image_path' => 'images/biometric.jpg',
  'assets_server' => 'test', // when agent runs on "local", image still from this server key

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
      'api_base' => 'http://192.168.100.70:8003/api',
      'web_url' => 'http://192.168.100.70:8003/',
    ],
  ],
  'session_name' => 'sx_fingerprint_agent',
  'session_lifetime' => 43200,
];
