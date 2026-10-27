<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$dbDir = __DIR__ . '/../database';
if (!file_exists($dbDir)) {
    mkdir($dbDir, 0777, true);
}

$settingsFile = $dbDir . '/settings.json';

$defaultSettings = [
    'categories' => [
        'Bagger',
        'Fahrzeuge',
        'Kleinmaschinen',
        'Abbruch',
        'Transport & Anhänger'
    ],
    'categorySpecs' => [
        'Bagger' => ['gewicht', 'grabtiefe', 'breite', 'leistung'],
        'Fahrzeuge' => ['nutzlast', 'muldeninhalt', 'breite', 'antrieb'],
        'Kleinmaschinen' => ['gewicht', 'fliehkraft', 'arbeitsbreite', 'kraftstoff'],
        'Abbruch' => ['einsatzgewicht', 'oelmenge', 'arbeitsdruck', 'traegergeraet'],
        'Transport & Anhänger' => ['nutzlast', 'gesamtgewicht', 'ladeflaeche', 'kupplung']
    ],
    'paymentConfig' => [
        'bank' => [
            'enabled' => true,
            'bankName' => 'Thurgauer Kantonalbank (TKB)',
            'accountHolder' => 'Rent A Bau AG',
            'iban' => 'CH93 0078 2000 1234 5678 9',
            'qrIban' => 'CH44 3199 9123 4567 8901 2'
        ],
        'twint' => [
            'enabled' => true,
            'phone' => '+41 79 123 45 67',
            'label' => 'Rent A Bau TWINT Pay'
        ],
        'mastercard' => [
            'enabled' => true,
            'label' => 'Mastercard / Visa / Kreditkarte'
        ],
        'cash' => [
            'enabled' => true,
            'label' => 'Barzahlung bei Abholung / Übergabe',
            'allowShortNoticeDays' => 3
        ]
    ],
    'deliveryConfig' => [
        'baseLocation' => 'Romanshorn (TG)',
        'baseFee' => 40,
        'tiers' => [
            ['maxKm' => 15, 'ratePerKm' => 4.00],
            ['maxKm' => 40, 'ratePerKm' => 3.00],
            ['maxKm' => 100, 'ratePerKm' => 2.20],
            ['maxKm' => 999, 'ratePerKm' => 1.80]
        ],
        'transportClasses' => [
            'leicht' => ['name' => 'Leichttransport (Anhänger / Bus)', 'multiplier' => 1.0],
            'mittel' => ['name' => 'Mitteltransport (3.5t Anhänger / Transporter)', 'multiplier' => 1.4],
            'schwer' => ['name' => 'Schwertransport (LKW / Tieflader)', 'multiplier' => 2.2]
        ]
    ],
    'discounts' => [
        ['minDays' => 3, 'percent' => 10],
        ['minDays' => 7, 'percent' => 15],
        ['minDays' => 14, 'percent' => 25]
    ],
    'machines' => [
        'minibagger-18' => [
            'id' => 'minibagger-18',
            'name' => 'Minibagger 1.8 t',
            'category' => 'Bagger',
            'description' => 'Kompakt, kräftig und ideal für Gartenbau, Leitungsgräben und enge Zufahrten.',
            'badge' => 'Beliebt',
            'hourlyPrice' => 45,
            'maxHoursPerDay' => 5,
            'weekendPrice' => 390,
            'deposit' => 800,
            'status' => 'online',
            'image' => 'brand/rent-a-bau-hero.png',
            'images' => ['brand/rent-a-bau-hero.png'],
            'transportClass' => 'mittel',
            'loadUnloadFee' => 40,
            'relatedIds' => ['raddumper-15', 'ruettelplatte-500'],
            'accessories' => [
                ['name' => 'Löffelpaket (30cm, 50cm, Grabensohle)', 'price' => 0],
                ['name' => 'Hydraulischer Abbruchhammer', 'price' => 40],
                ['name' => 'Maschinen-Transportanhänger 3.5t', 'price' => 50]
            ],
            'specs' => [
                'gewicht' => "1'800 kg",
                'grabtiefe' => '2.40 m',
                'breite' => "990 - 1'300 mm",
                'leistung' => '11.8 kW (16 PS)'
            ]
        ],
        'raddumper-15' => [
            'id' => 'raddumper-15',
            'name' => 'Raddumper 1.5 t',
            'category' => 'Fahrzeuge',
            'description' => 'Für Aushub, Kies und Materialtransport auf kleinen und mittleren Baustellen.',
            'badge' => '',
            'hourlyPrice' => 35,
            'maxHoursPerDay' => 5,
            'weekendPrice' => 310,
            'deposit' => 600,
            'status' => 'online',
            'image' => 'brand/slider-transport.webp',
            'images' => ['brand/slider-transport.webp'],
            'transportClass' => 'mittel',
            'loadUnloadFee' => 35,
            'relatedIds' => ['minibagger-18', 'ruettelplatte-500'],
            'accessories' => [
                ['name' => 'Auffahrrampen Alu 3.5t', 'price' => 20],
                ['name' => 'Transportanhänger 3.5t', 'price' => 50]
            ],
            'specs' => [
                'nutzlast' => "1'500 kg",
                'muldeninhalt' => '800 Liter',
                'breite' => "1'200 mm",
                'antrieb' => 'Allrad 4x4'
            ]
        ],
        'ruettelplatte-500' => [
            'id' => 'ruettelplatte-500',
            'name' => 'Rüttelplatte 500 kg',
            'category' => 'Kleinmaschinen',
            'description' => 'Leistungsstarke reversierbare Platte für Fundationen, Plätze und Zufahrten.',
            'badge' => '',
            'hourlyPrice' => 25,
            'maxHoursPerDay' => 5,
            'weekendPrice' => 220,
            'deposit' => 400,
            'status' => 'online',
            'image' => 'brand/slider-verdichtung.webp',
            'images' => ['brand/slider-verdichtung.webp'],
            'transportClass' => 'leicht',
            'loadUnloadFee' => 20,
            'relatedIds' => ['minibagger-18', 'raddumper-15'],
            'accessories' => [
                ['name' => 'Vulkanmatte für Pflastersteine', 'price' => 15]
            ],
            'specs' => [
                'gewicht' => '480 kg',
                'fliehkraft' => '60 kN',
                'arbeitsbreite' => '650 mm',
                'kraftstoff' => 'Diesel'
            ]
        ]
    ],
    'blockedDates' => []
];

function getSettings($file, $defaults) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $defaults;
    }
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    return is_array($data) ? array_merge($defaults, $data) : $defaults;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && $method !== 'OPTIONS') {
    requireAdmin();
}

if ($method === 'GET') {
    echo json_encode(getSettings($settingsFile, $defaultSettings));
    exit();
}

if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    $payload = json_decode($input, true);
    
    if (!$payload) {
        http_response_code(400);
        echo json_encode(['error' => 'Ungültiges JSON-Format']);
        exit();
    }

    $current = getSettings($settingsFile, $defaultSettings);
    $beforeSettings = $current;

    if (isset($payload['categories'])) {
        $current['categories'] = array_values(array_unique($payload['categories']));
    }
    if (isset($payload['categorySpecs'])) {
        $current['categorySpecs'] = $payload['categorySpecs'];
    }
    if (isset($payload['paymentConfig'])) {
        $current['paymentConfig'] = $payload['paymentConfig'];
    }
    if (isset($payload['deliveryConfig'])) {
        $current['deliveryConfig'] = $payload['deliveryConfig'];
    }
    if (isset($payload['discounts'])) {
        $current['discounts'] = $payload['discounts'];
    }
    if (isset($payload['machines'])) {
        foreach ($payload['machines'] as $mId => $mDetails) {
            if (!isset($current['machines'][$mId])) {
                $current['machines'][$mId] = $mDetails;
            } else {
                $current['machines'][$mId] = array_merge($current['machines'][$mId], $mDetails);
            }
        }
    }
    if (isset($payload['blockedDates'])) {
        $current['blockedDates'] = $payload['blockedDates'];
    }

    file_put_contents($settingsFile, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    auditLogSafe('update', 'settings', 'settings', null, 'Admin-Einstellungen aktualisiert', $beforeSettings, $current);

    echo json_encode([
        'success' => true,
        'settings' => $current
    ]);
    exit();
}

if ($method === 'DELETE') {
    $input = file_get_contents('php://input');
    $payload = json_decode($input, true);
    if (isset($payload['deleteMachineId'])) {
        $current = getSettings($settingsFile, $defaultSettings);
        $beforeSettings = $current;
        $mId = $payload['deleteMachineId'];
        if (isset($current['machines'][$mId])) {
            unset($current['machines'][$mId]);
            file_put_contents($settingsFile, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
            auditLogSafe('delete', 'machines', 'machine', (string) $mId, 'Maschine aus den Einstellungen gelöscht', $beforeSettings, $current);
        }
        echo json_encode(['success' => true, 'settings' => $current]);
        exit();
    }
}

http_response_code(405);
echo json_encode(['error' => 'Methode nicht erlaubt']);
