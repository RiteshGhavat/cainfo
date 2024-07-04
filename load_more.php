<?php
include 'config.php';

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = 5;

$sql = "SELECT * FROM entries LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    echo json_encode($entries);
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
?>
