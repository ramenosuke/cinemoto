<?php
header('Content-Type: application/json');
$connection = new mysqli('localhost', 'root', '', 'plantitos_cinema');
if ($connection->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
$type = $_GET['type'] ?? '';
$parent = $_GET['parent'] ?? '';
$data = [];
if ($type === 'region') {
    $result = $connection->query("SELECT regCode, regDesc FROM refregion ORDER BY regDesc");
    while ($row = $result->fetch_assoc()) {
        $data[] = ['code' => $row['regCode'], 'name' => $row['regDesc']];
    }
} elseif ($type === 'province' && $parent) {
    $stmt = $connection->prepare("SELECT provCode, provDesc FROM refprovince WHERE regCode = ? ORDER BY provDesc");
    $stmt->bind_param('s', $parent);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = ['code' => $row['provCode'], 'name' => $row['provDesc']];
    }
    $stmt->close();
} elseif ($type === 'city' && $parent) {
    $stmt = $connection->prepare("SELECT citymunCode, citymunDesc FROM refcitymun WHERE provCode = ? ORDER BY citymunDesc");
    $stmt->bind_param('s', $parent);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = ['code' => $row['citymunCode'], 'name' => $row['citymunDesc']];
    }
    $stmt->close();
} elseif ($type === 'barangay' && $parent) {
    $stmt = $connection->prepare("SELECT brgyCode, brgyDesc FROM refbrgy WHERE citymunCode = ? ORDER BY brgyDesc");
    $stmt->bind_param('s', $parent);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = ['code' => $row['brgyCode'], 'name' => $row['brgyDesc']];
    }
    $stmt->close();
}
$connection->close();
echo json_encode($data);
