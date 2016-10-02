<?php

$path = dirname(__DIR__);
$capacity = array();
/*
  Array
  (
  [0] => FEEDER
  [1] => CAPACITY
  )
 */
foreach (glob($path . '/src/*_capacity.csv') AS $csvFile) {
    $p = pathinfo($csvFile);
    $city = str_replace('capacity', '', $p['filename']);
    $fh = fopen($csvFile, 'r');
    fgetcsv($fh, 2048);
    while ($line = fgetcsv($fh, 2048)) {
        $capacity[$city . $line[0]] = floatval($line[1]);
    }
}

$result = array();

/*

  Array
  (
  [0] => ROWNUM
  [1] => X
  [2] => Y
  [3] => FEEDER
  )
 */
foreach (glob($path . '/src/*_feeder.csv') AS $csvFile) {
    $p = pathinfo($csvFile);
    $city = str_replace('_tr_feeder', '', $p['filename']);
    $fh = fopen($csvFile, 'r');
    fgetcsv($fh, 2048);
    while ($line = fgetcsv($fh, 2048)) {
        if (!isset($result[$city])) {
            $result[$city] = array();
        }
        $key = "" . $line[1] . "," . $line[2];
        if (!isset($result[$city][$key])) {
            //$result[$city][$key] = twd97_to_latlng($line[1], $line[2], '67');
            $result[$city][$key] = array('lat' => $line[1], 'lng' => $line[2]);
            $result[$city][$key]['feeders'] = array(
                $line[3] => $capacity[$city . '_' . $line[3]],
            );
        } else {
            $result[$city][$key]['feeders'][$line[3]] = $capacity[$city . '_' . $line[3]];
        }
    }
}

file_put_contents($path . '/result.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


/*
  from https://gist.github.com/pingyen/1346895
 */

function twd97_to_latlng($x, $y, $m = '97') {
    if ($m === '67') {
        // ref http://blog.changyy.org/2012/11/twd67-twd97-wgs84.html
        $a = 0.00001549;
        $b = 0.000006521;
        $tx = $x;
        $x = $x + 807.8 + $a * $x + $b * $y;
        $y = $y - 248.6 + $a * $y + $b * $tx;
    }
    $a = 6378137.0;
    $b = 6356752.314245;
    $lng0 = 121 * M_PI / 180;
    $k0 = 0.9999;
    $dx = 250000;

    $dy = 0;
    $e = pow((1 - pow($b, 2) / pow($a, 2)), 0.5);

    $x -= $dx;
    $y -= $dy;

    $M = $y / $k0;

    $mu = $M / ($a * (1.0 - pow($e, 2) / 4.0 - 3 * pow($e, 4) / 64.0 - 5 * pow($e, 6) / 256.0));
    $e1 = (1.0 - pow((1.0 - pow($e, 2)), 0.5)) / (1.0 + pow((1.0 - pow($e, 2)), 0.5));

    $J1 = (3 * $e1 / 2 - 27 * pow($e1, 3) / 32.0);
    $J2 = (21 * pow($e1, 2) / 16 - 55 * pow($e1, 4) / 32.0);
    $J3 = (151 * pow($e1, 3) / 96.0);
    $J4 = (1097 * pow($e1, 4) / 512.0);

    $fp = $mu + $J1 * sin(2 * $mu) + $J2 * sin(4 * $mu) + $J3 * sin(6 * $mu) + $J4 * sin(8 * $mu);

    $e2 = pow(($e * $a / $b), 2);
    $C1 = pow($e2 * cos($fp), 2);
    $T1 = pow(tan($fp), 2);
    $R1 = $a * (1 - pow($e, 2)) / pow((1 - pow($e, 2) * pow(sin($fp), 2)), (3.0 / 2.0));
    $N1 = $a / pow((1 - pow($e, 2) * pow(sin($fp), 2)), 0.5);

    $D = $x / ($N1 * $k0);

    $Q1 = $N1 * tan($fp) / $R1;
    $Q2 = (pow($D, 2) / 2.0);
    $Q3 = (5 + 3 * $T1 + 10 * $C1 - 4 * pow($C1, 2) - 9 * $e2) * pow($D, 4) / 24.0;
    $Q4 = (61 + 90 * $T1 + 298 * $C1 + 45 * pow($T1, 2) - 3 * pow($C1, 2) - 252 * $e2) * pow($D, 6) / 720.0;
    $lat = $fp - $Q1 * ($Q2 - $Q3 + $Q4);

    $Q5 = $D;
    $Q6 = (1 + 2 * $T1 + $C1) * pow($D, 3) / 6;
    $Q7 = (5 - 2 * $C1 + 28 * $T1 - 3 * pow($C1, 2) + 8 * $e2 + 24 * pow($T1, 2)) * pow($D, 5) / 120.0;
    $lng = $lng0 + ($Q5 - $Q6 + $Q7) / cos($fp);

    $lat = ($lat * 180) / M_PI;
    $lng = ($lng * 180) / M_PI;

    return array(
        'lat' => $lat,
        'lng' => $lng
    );
}
