<?php
// Expand site.php common keys
$enExtra = [
    'about_us' => 'About Us',
    'our_team' => 'Our Team',
    'download_app' => 'Download App',
    'resources' => 'Resources',
    'office' => 'Office',
    'footer_tagline' => 'Complete school management for preschool, primary, secondary and advanced schools worldwide.',
    'details' => 'Details',
    'exams_results' => 'Exams & Results',
    'meet_the_team' => 'Meet the Team',
    'work_with_us' => 'Work With Us',
    'browse_modules' => 'Browse modules',
    'read_faq' => 'Read FAQ',
    'view_full_pricing' => 'View full pricing',
    'register_school_short' => 'Register school',
];
$swExtra = [
    'about_us' => 'Kuhusu Sisi',
    'our_team' => 'Timu Yetu',
    'download_app' => 'Pakua App',
    'resources' => 'Rasilimali',
    'office' => 'Ofisi',
    'footer_tagline' => 'Usimamizi kamili wa shule kwa preschool, primary, secondary na advanced duniani.',
    'details' => 'Maelezo',
    'exams_results' => 'Mitihani na Matokeo',
    'meet_the_team' => 'Kutana na Timu',
    'work_with_us' => 'Fanya Kazi Nasi',
    'browse_modules' => 'Angalia moduli',
    'read_faq' => 'Soma Maswali',
    'view_full_pricing' => 'Angalia bei kamili',
    'register_school_short' => 'Sajili shule',
];

function injectKeys($path, $extra) {
    $arr = include $path;
    foreach ($extra as $k=>$v) { $arr[$k]=$v; }
    $out = "<?php\n\nreturn [\n";
    foreach ($arr as $k=>$v) {
        $out .= "    '".addcslashes($k,"'\\")."' => '".addcslashes($v,"'\\")."',\n";
    }
    $out .= "];\n";
    file_put_contents($path, $out);
}
injectKeys('lang/en/site.php', $enExtra);
injectKeys('lang/sw/site.php', $swExtra);
echo "site.php updated\n";
