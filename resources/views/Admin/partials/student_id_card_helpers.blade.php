@php
    if (!function_exists('idCardGetSmartBase64')) {
        function idCardGetSmartBase64($path) {
            if (!$path) return null;
            $path = ltrim($path, '/');
            $possibilities = [
                public_path($path),
                public_path('uploads/' . $path),
                base_path('public/' . $path),
                base_path($path),
            ];
            if (isset($_SERVER['DOCUMENT_ROOT'])) {
                $possibilities[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $path;
            }
            foreach ($possibilities as $fullPath) {
                if (@file_exists($fullPath) && is_file($fullPath)) {
                    try {
                        $data = base64_encode(file_get_contents($fullPath));
                        $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'png';
                        return 'data:image/' . $ext . ';base64,' . $data;
                    } catch (\Exception $e) {}
                }
            }
            return null;
        }
    }

    if (!function_exists('idCardPdfImagePath')) {
        function idCardPdfImagePath($path) {
            if (!$path) return null;
            $path = ltrim($path, '/');
            $possibilities = [
                public_path($path),
                public_path('uploads/' . $path),
                base_path('public/' . $path),
                base_path($path),
            ];
            if (isset($_SERVER['DOCUMENT_ROOT'])) {
                $possibilities[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $path;
            }
            foreach ($possibilities as $fullPath) {
                if (@file_exists($fullPath) && is_file($fullPath)) {
                    $resolved = realpath($fullPath) ?: $fullPath;
                    return str_replace('\\', '/', $resolved);
                }
            }
            return null;
        }
    }

    if (!function_exists('idCardIconPixelSizeForPdf')) {
        function idCardIconPixelSizeForPdf() {
            return 72;
        }
    }
    if (!function_exists('idCardSchoolHeaderName')) {
        function idCardSchoolHeaderName($name) {
            return strtoupper(trim($name));
        }
    }

    if (!function_exists('idCardFormatSchoolNameLines')) {
        function idCardFormatSchoolNameLines($name) {
            $full = idCardSchoolHeaderName($name);
            if (strlen($full) <= 42) {
                return [$full];
            }

            $words = preg_split('/\s+/', trim($name));
            $upper = array_map('strtoupper', $words);
            $count = count($upper);

            if ($count >= 3 && $upper[$count - 2] === 'HIGH' && $upper[$count - 1] === 'SCHOOL') {
                $line1 = trim(implode(' ', array_slice($upper, 0, $count - 2)));
                $line2 = 'HIGH SCHOOL';
                return $line1 !== '' ? [$line1, $line2] : [$line2];
            }

            $mid = (int) ceil($count / 2);
            $line1 = implode(' ', array_slice($upper, 0, $mid));
            $line2 = implode(' ', array_slice($upper, $mid));

            return $line2 !== '' ? [$line1, $line2] : [$line1];
        }
    }

    if (!function_exists('idCardSplitSchoolName')) {
        function idCardSplitSchoolName($name) {
            $lines = idCardFormatSchoolNameLines($name);
            return [$lines[0], $lines[1] ?? ''];
        }
    }

    if (!function_exists('idCardHexToRgb')) {
        function idCardHexToRgb($hex) {
            $hex = ltrim($hex, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }
            return sscanf($hex, '%02x%02x%02x');
        }
    }

    if (!function_exists('idCardIconPixelSize')) {
        function idCardIconPixelSize() {
            return 256;
        }
    }

    if (!function_exists('idCardDrawIdIcon')) {
        function idCardDrawIdIcon($im, $color, $cx, $cy, $s) {
            imagefilledrectangle($im, (int)($cx - 10 * $s), (int)($cy - 8 * $s), (int)($cx + 10 * $s), (int)($cy + 10 * $s), $color);
            imagefilledellipse($im, (int)($cx - 3 * $s), (int)($cy - 1 * $s), (int)(6 * $s), (int)(6 * $s), $color);
            imagefilledrectangle($im, (int)($cx + 2 * $s), (int)($cy + 2 * $s), (int)($cx + 9 * $s), (int)($cy + 3 * $s), $color);
            imagefilledrectangle($im, (int)($cx + 2 * $s), (int)($cy + 5 * $s), (int)($cx + 7 * $s), (int)($cy + 6 * $s), $color);
        }
    }

    if (!function_exists('idCardDrawClassIcon')) {
        function idCardDrawClassIcon($im, $color, $cx, $cy, $s) {
            imagefilledpolygon($im, [
                (int)$cx, (int)($cy - 12 * $s),
                (int)($cx - 14 * $s), (int)($cy - 2 * $s),
                (int)($cx + 14 * $s), (int)($cy - 2 * $s),
            ], $color);
            imagefilledrectangle($im, (int)($cx - 10 * $s), (int)($cy - 2 * $s), (int)($cx + 10 * $s), (int)($cy + 4 * $s), $color);
            imagefilledpolygon($im, [
                (int)($cx - 12 * $s), (int)($cy + 4 * $s),
                (int)($cx - 8 * $s), (int)($cy + 12 * $s),
                (int)($cx - 4 * $s), (int)($cy + 4 * $s),
            ], $color);
            imagefilledpolygon($im, [
                (int)($cx + 12 * $s), (int)($cy + 4 * $s),
                (int)($cx + 8 * $s), (int)($cy + 12 * $s),
                (int)($cx + 4 * $s), (int)($cy + 4 * $s),
            ], $color);
        }
    }

    if (!function_exists('idCardDrawSexIcon')) {
        function idCardDrawSexIcon($im, $color, $cx, $cy, $s) {
            imagefilledellipse($im, (int)$cx, (int)($cy - 6 * $s), (int)(10 * $s), (int)(10 * $s), $color);
            imagefilledpolygon($im, [
                (int)($cx - 12 * $s), (int)($cy + 4 * $s),
                (int)($cx + 12 * $s), (int)($cy + 4 * $s),
                (int)($cx + 8 * $s), (int)($cy + 14 * $s),
                (int)($cx - 8 * $s), (int)($cy + 14 * $s),
            ], $color);
        }
    }

    if (!function_exists('idCardDrawExpireIcon')) {
        function idCardDrawExpireIcon($im, $color, $cx, $cy, $s) {
            imagefilledrectangle($im, (int)($cx - 11 * $s), (int)($cy - 9 * $s), (int)($cx + 11 * $s), (int)($cy + 11 * $s), $color);
            imagefilledrectangle($im, (int)($cx - 9 * $s), (int)($cy - 5 * $s), (int)($cx + 9 * $s), (int)($cy + 9 * $s), $color);
            imagefilledrectangle($im, (int)($cx - 9 * $s), (int)($cy - 9 * $s), (int)($cx + 9 * $s), (int)($cy - 6 * $s), $color);
            imagefilledrectangle($im, (int)($cx - 7 * $s), (int)($cy - 12 * $s), (int)($cx - 5 * $s), (int)($cy - 6 * $s), $color);
            imagefilledrectangle($im, (int)($cx + 5 * $s), (int)($cy - 12 * $s), (int)($cx + 7 * $s), (int)($cy - 6 * $s), $color);
            imagefilledrectangle($im, (int)($cx - 5 * $s), (int)$cy, (int)($cx - 2 * $s), (int)($cy + 3 * $s), $color);
        }
    }

    if (!function_exists('idCardDrawPhoneIcon')) {
        function idCardDrawPhoneIcon($im, $color, $cx, $cy, $s) {
            imagefilledroundedrect($im, (int)($cx - 5 * $s), (int)($cy - 8 * $s), (int)($cx + 5 * $s), (int)($cy + 10 * $s), (int)(2 * $s), $color);
            imagefilledellipse($im, (int)($cx - 1 * $s), (int)($cy + 6 * $s), (int)(2 * $s), (int)(2 * $s), $color);
        }
    }

    if (!function_exists('imagefilledroundedrect')) {
        function imagefilledroundedrect($im, $x1, $y1, $x2, $y2, $radius, $color) {
            imagefilledrectangle($im, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
            imagefilledrectangle($im, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
            imagefilledellipse($im, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
            imagefilledellipse($im, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
            imagefilledellipse($im, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
            imagefilledellipse($im, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        }
    }

    if (!function_exists('idCardIconDataUri')) {
        function idCardIconDataUri($type, $color = '#940000', $size = null) {
            static $cache = [];
            $size = $size ?? idCardIconPixelSize();
            $key = $type . '|' . $color . '|' . $size;
            if (isset($cache[$key])) {
                return $cache[$key];
            }

            if (!function_exists('imagecreatetruecolor')) {
                $cache[$key] = '';
                return $cache[$key];
            }

            [$r, $g, $b] = idCardHexToRgb($color);
            $s = $size / 80;
            $im = imagecreatetruecolor($size, $size);
            if (function_exists('imageantialias')) {
                imageantialias($im, true);
            }
            $white = imagecolorallocate($im, 255, 255, 255);
            $primary = imagecolorallocate($im, $r, $g, $b);
            imagefill($im, 0, 0, $white);

            $cx = (int)($size / 2);
            $cy = (int)($size / 2);

            if ($type === 'pin') {
                imagefilledellipse($im, $cx, (int)($cy - 10 * $s), (int)(20 * $s), (int)(20 * $s), $primary);
                imagefilledellipse($im, $cx, (int)($cy - 10 * $s), (int)(9 * $s), (int)(9 * $s), $white);
                imagefilledpolygon($im, [
                    (int)($cx - 10 * $s), (int)($cy + 2 * $s),
                    (int)($cx + 10 * $s), (int)($cy + 2 * $s),
                    $cx, (int)($cy + 22 * $s),
                ], $primary);
            } elseif ($type === 'phone') {
                imagesetthickness($im, max(1, (int)(4 * $s)));
                imageellipse($im, $cx, $cy, (int)(52 * $s), (int)(52 * $s), $primary);
                imagesetthickness($im, 1);
                idCardDrawPhoneIcon($im, $primary, $cx, $cy, $s);
            } else {
                imagefilledellipse($im, $cx, $cy, (int)(54 * $s), (int)(54 * $s), $primary);
                $drawers = [
                    'id' => 'idCardDrawIdIcon',
                    'class' => 'idCardDrawClassIcon',
                    'sex' => 'idCardDrawSexIcon',
                    'expire' => 'idCardDrawExpireIcon',
                ];
                if (isset($drawers[$type])) {
                    $drawers[$type]($im, $white, $cx, $cy, $s);
                }
            }

            ob_start();
            imagepng($im, null, 0);
            $png = ob_get_clean();
            imagedestroy($im);

            $cache[$key] = 'data:image/png;base64,' . base64_encode($png);
            return $cache[$key];
        }
    }
@endphp
