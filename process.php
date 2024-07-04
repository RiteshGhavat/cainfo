<?php
include 'config.php';

$full_name = $_POST['full_name'];
$company_name = $_POST['company_name'];
$address = $_POST['address'];
$city = $_POST['city'];
$state = $_POST['state'];
$pin_code = $_POST['pin_code'];
$mobile_no = $_POST['mobile_no'];
$image = $_FILES['image']['name'];
$target_dir = "uploads/";
$target_file = $target_dir . basename($image);

// Move uploaded file to the uploads directory
if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    $sql = "INSERT INTO records (full_name, company_name, address, city, state, pin_code, mobile_no, image) VALUES ('$full_name', '$company_name' '$address', '$city', '$state', '$pin_code', '$mobile_no', '$image')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "Sorry, there was an error uploading your file.";
}

$conn->close();

header("Location: index.php");
exit();
?>
