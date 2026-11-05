<?php
header('Content-Type: application/json; charset=utf-8');

$secretKey = 'туттожеключесть';
$key = $_GET['key'] ?? '';

if ($key !== $secretKey) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'access denied']);
    exit;
}

$file = __DIR__ . '/zayavki.json';

if (!file_exists($file)) {
    file_put_contents($file, json_encode([], JSON_UNESCAPED_UNICODE));
}

$zayavki = json_decode(file_get_contents($file), true) ?: [];

$action = $_GET['action'] ?? '';

if ($action === 'add') {
    $new = [
        'id' => uniqid(),
        'fio' => $_POST['fio'] ?? '',
        'email' => $_POST['email'] ?? '',
        'number' => $_POST['number'] ?? '',
        'city' => $_POST['city'] ?? '',
        'NameOfLLC' => $_POST['NameOfLLC'] ?? '',
        'RequestedService' => $_POST['RequestedService'] ?? '',
        'NumberOfEmployees' => (int)($_POST['NumberOfEmployees'] ?? 0),
        'SystemTax' => $_POST['SystemTax'] ?? '',
        'created_at' => date('Y-m-d H:i:s'),
        'status' => 'new'
    ];

    $zayavki[] = $new;
    file_put_contents($file, json_encode($zayavki, JSON_UNESCAPED_UNICODE));

    echo json_encode(['success' => true, 'id' => $new['id']]);
    exit;
}

if ($action === 'list') {
    $newZayavki = array_values(array_filter($zayavki, function($z) {
        return $z['status'] === 'new';
    }));

    echo json_encode($newZayavki, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'mark') {
    $id = $_GET['id'] ?? '';

    foreach ($zayavki as $i => $z) {
        if ($z['id'] === $id) {
            $zayavki[$i]['status'] = 'received';
        }
    }

    file_put_contents($file, json_encode($zayavki, JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false]); 
?> 