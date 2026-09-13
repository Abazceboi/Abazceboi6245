<?php
/**
 * REST API: Fetch Verified WhatsApp Vendors
 * Method: GET
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$vendors = [
    [
        'id' => 'v1',
        'name' => 'Emmanuel Eze',
        'location' => 'Lagos / National (GTBank, OPay, Kuda)',
        'rating' => 5.0,
        'codesSold' => '2,400+ Codes Sold',
        'phone' => '2348012345678',
        'avatarColor' => '#9333EA'
    ],
    [
        'id' => 'v2',
        'name' => 'Fatima Bello',
        'location' => 'Abuja / Northern Region (Access Bank, Palmpay)',
        'rating' => 4.9,
        'codesSold' => '1,850+ Codes Sold',
        'phone' => '2348023456789',
        'avatarColor' => '#7C3AED'
    ],
    [
        'id' => 'v3',
        'name' => 'Tunde Adeyemi',
        'location' => 'Ibadan / South West (Zenith, Moniepoint)',
        'rating' => 4.9,
        'codesSold' => '1,420+ Codes Sold',
        'phone' => '2348034567890',
        'avatarColor' => '#C59B4B'
    ]
];

echo json_encode([
    'success' => true,
    'count' => count($vendors),
    'data' => $vendors
]);
