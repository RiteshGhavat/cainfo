<?php
include 'config.php'; 

// Initialize variables
$entries = [];
$uploadDir = 'uploads/';
$mobileError = '';
$search = '';
$sort = '';

// Handle form submission
if (isset($_POST['submit'])) {
    $fullName = strtoupper($_POST['fullName']);
    $companyName = $_POST['companyName']; // Added company name field
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $pinCode = $_POST['pinCode'];
    $mobile = $_POST['mobile'];
    $image = $_FILES['image']['name'];

    // Server-side validation for mobile number
    if (strlen($mobile) != 10 || !ctype_digit($mobile)) {
        $mobileError = "Invalid mobile number. It should contain exactly 10 digits.";
    } else {
        // Check if the uploads directory exists, if not create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imagePath = $uploadDir . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);

        // Insert data into database
        $stmt = $conn->prepare("INSERT INTO entries (full_name, company_name, address, city, state, pin_code, mobile, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssss", $fullName, $companyName, $address, $city, $state, $pinCode, $mobile, $imagePath);
            $stmt->execute();
            $stmt->close();

            // Redirect to prevent form resubmission
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
}

// Handle search
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Handle sort
if (isset($_GET['sort'])) {
    $sort = $_GET['sort'];
}

// Retrieve data from database
$sql = "SELECT * FROM entries";

// Apply search filter
if (!empty($search)) {
    $searchTerm = "%$search%";
    $sql .= " WHERE full_name LIKE ? OR company_name LIKE ? OR address LIKE ? OR city LIKE ? OR state LIKE ? OR pin_code LIKE ? OR mobile LIKE ?";
}

// Apply sort
if (!empty($sort)) {
    $sql .= " ORDER BY $sort";
}

// Limit records
$sql .= " LIMIT 5";

// Prepare and execute SQL statement
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($search)) {
        $stmt->bind_param("sssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }
    } else {
        echo "Error fetching results: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaInfo</title>
    <link rel="icon" href="assest/logo2.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f0f0f0;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group button {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #218838;
        }
        .card {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .header, .footer {
            text-align: center;
            padding: 10px 0;
            background-color: grey;
            color: #fff;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .logo {
            width: 100px;
        }
        .load-more {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 20px auto;
        }
        .load-more:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            .container {
                width: 90%;
            }
            .form-group input, .form-group button {
                width: 100%;
                padding: 10px;
            }
        }
    </style>
    <script>
        let offset = 5;
        function loadMore() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `load_more.php?offset=${offset}`, true);
            xhr.onload = function() {
                if (this.status === 200) {
                    const newEntries = JSON.parse(this.responseText);
                    const container = document.getElementById('entries-container');
                    newEntries.forEach(entry => {
                        const card = document.createElement('div');
                        card.classList.add('card');
                        card.innerHTML = `
                            <h2>${entry.full_name}</h2>
                            <p><strong>Address:</strong> ${entry.address}</p>
                            <p><strong>City:</strong> ${entry.city}</p>
                            <p><strong>State:</strong> ${entry.state}</p>
                            <p><strong>Pin Code:</strong> ${entry.pin_code}</p>
                            <p><strong>Mobile:</strong> ${entry.mobile}</p>
                            <img src="${entry.image_path}" alt="Image">
                            <p><a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(entry.address)}" target="_blank">View on Google Maps</a></p>
                        `;
                        container.appendChild(card);
                    });
                    offset += 5;
                }
            };
            xhr.send();
        }

        window.addEventListener('DOMContentLoaded', (event) => {
            const mobileInput = document.getElementById('mobile');
            // Add event listener to ensure only 10 digits are allowed
            mobileInput.addEventListener('input', (event) => {
                const inputValue = event.target.value.replace(/\D/g, ''); // Remove non-digit characters
                const formattedValue = inputValue.substring(0, 10); // Limit to 10 digits
                event.target.value = formattedValue;
            });
        });
    </script>
</head>
<body>
    <div class="header">
        <img src="assest/logo2.png" alt="Logo" class="logo">
    </div>
    <div class="container">
        <h1>Add Information</h1>
        <form action="index.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="fullName" required>
            </div>
            <div class="form-group">
                <label for="companyName">Company Name</label> <!-- Added company name field -->
                <input type="text" id="companyName" name="companyName" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" required>
            </div>
            <div class="form-group">
                <label for="state">State</label>
                <input type="text" id="state" name="state" required>
            </div>
            <div class="form-group">
                <label for="pinCode">Pin Code</label>
                <input type="text" id="pinCode" name="pinCode" required>
            </div>
            <div class="form-group">
                <label for="mobile">Mobile No</label>
                <input type="text" id="mobile" name="mobile" required>
                <span style="color: red;"><?php echo $mobileError; ?></span> <!-- Show error message here -->
            </div>
            <div class="form-group">
                <label for="image">Upload Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <div class="form-group">
                <button type="submit" name="submit">Add</button>
            </div>
        </form>

        <h1>Search Information</h1>
        <form action="index.php" method="GET">
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" value="<?php echo $search; ?>">
                <button type="submit">Search</button>
            </div>
        </form>

        <h1>Sort Information</h1>
        <form action="index.php" method="GET">
            <div class="form-group">
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    <option value="">Select</option>
                    <option value="full_name" <?php if ($sort == 'full_name') echo 'selected'; ?>>Full Name</option>
                    <option value="address" <?php if ($sort == 'address') echo 'selected'; ?>>Address</option>
                    <option value="city" <?php if ($sort == 'city') echo 'selected'; ?>>City</option>
                    <option value="state" <?php if ($sort == 'state') echo 'selected'; ?>>State</option>
                    <option value="pin_code" <?php if ($sort == 'pin_code') echo 'selected'; ?>>Pin Code</option>
                    <option value="mobile" <?php if ($sort == 'mobile') echo 'selected'; ?>>Mobile</option>
                </select>
                <button type="submit">Sort</button>
            </div>
        </form>

        <div id="entries-container">
            <?php if (!empty($entries)): ?>
                <?php foreach ($entries as $entry): ?>
                    <div class="card">
                        <h2><?php echo htmlspecialchars($entry['full_name']); ?></h2>
                        <p><strong>Company Name:</strong> <?php echo htmlspecialchars($entry['company_name']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($entry['address']); ?></p>
                        <p><strong>City:</strong> <?php echo htmlspecialchars($entry['city']); ?></p>
                        <p><strong>State:</strong> <?php echo htmlspecialchars($entry['state']); ?></p>
                        <p><strong>Pin Code:</strong> <?php echo htmlspecialchars($entry['pin_code']); ?></p>
                        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($entry['mobile']); ?></p>
                        <img src="<?php echo htmlspecialchars($entry['image_path']); ?>" alt="Image">
                        <p><a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($entry['address']); ?>" target="_blank">View on Google Maps</a></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No records found</p>
            <?php endif; ?>
        </div>
        <!-- Load more button -->
        <button class="load-more" onclick="loadMore()">Load More</button>
    </div>
    <div class="footer">
        <p>&copy; 2024 CPSLLPINDIA. Developed by Ritesh.</p>
    </div>
</body>
</html>
