<?php

return [
  'app_name' => 'ShuleXpert Fingerprint Attendance System',
  'powered_by' => 'EmCa Techonologies',
  'default_server' => 'test',
  'ssl_verify' => true,
  'ca_bundle' => null,

  // Login / splash image — loaded from ShuleXpert server (not local disk)
  'biometric_image_path' => 'images/biometric.jpg',
  'assets_server' => 'test', // when agent runs on "local", image still from this server key

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
      'label' => 'Local',
      'api_base' => 'http://192.168.100.70/api',
      'web_url' => 'http://192.168.100.70/',
    ],
  ],
  'session_name' => 'sx_fingerprint_agent',
  'session_lifetime' => 43200,
  'web_path' => 'attendance-agent',
];
