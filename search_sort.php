<?php
include 'config.php';

$search = $_POST['search'] ?? '';
$sort = $_POST['sort'] ?? 'full_name';

$sql = "SELECT * FROM records WHERE full_name LIKE '%$search%' OR address LIKE '%$search%' OR city LIKE '%$search%' OR state LIKE '%$search%' OR pin_code LIKE '%$search%' ORDER BY $sort LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div class='record'>";
        echo "<h3>" . $row["full_name"] . "</h3>";
        echo "<p>" . $row["address"] . ", " . $row["city"] . ", " . $row["state"] . " (" . $row["pin_code"] . ")</p>";
        echo "<p>Mobile: " . $row["mobile_no"] . "</p>";
        echo "<img src='uploads/" . $row["image"] . "' alt='Image' class='image-box'>";
        echo "<p><a href='https://www.google.com/maps/search/?api=1&query=" . urlencode($row["address"] . ", " . $row["city"] . ", " . $row["state"] . " " . $row["pin_code"]) . "' target='_blank'>View on Google Maps</a></p>";
        echo "</div>";
    }
} else {
    echo "No records found";
}

$conn->close();
?>
