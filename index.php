<?php
include('connection.php'); // Database connection file
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_no = $_POST['roll_no'];

    // Step 1: Check if the roll number exists in student_data
    $query = "SELECT * FROM student_data WHERE roll_no = '$roll_no'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // Step 2: Check if the student is already assigned to a desk
        $check_assigned = "SELECT * FROM sitting_arrangement WHERE roll_no = '$roll_no'";
        $assigned_result = $conn->query($check_assigned);

        if ($assigned_result->num_rows == 0) {
            // Step 3: Assign desk based on roll number (odd or even)
            if ($roll_no % 2 == 0) {
                // Find the next available even-numbered desk
                $desk_query = "SELECT desk_number FROM desks 
                                WHERE desk_number % 2 = 0 
                                AND desk_number NOT IN (SELECT desk_number FROM sitting_arrangement) 
                                LIMIT 1";
            } else {
                // Find the next available odd-numbered desk
                $desk_query = "SELECT desk_number FROM desks 
                                WHERE desk_number % 2 <> 0 
                                AND desk_number NOT IN (SELECT desk_number FROM sitting_arrangement) 
                                LIMIT 1";
            }

            $desk_result = $conn->query($desk_query);

            if ($desk_result->num_rows > 0) {
                $desk_row = $desk_result->fetch_assoc();
                $desk_number = $desk_row['desk_number'];

                // Assign the desk to the student
                $assign_query = "INSERT INTO sitting_arrangement (roll_no, desk_number) 
                                VALUES ('$roll_no', '$desk_number')";
                if ($conn->query($assign_query)) {
                    echo "<p class='alert alert-success'>Student assigned to desk $desk_number!</p>";
                } else {
                    echo "<p class='alert alert-danger'>Error assigning desk!</p>";
                }
            } else {
                echo "<p class='alert alert-warning'>No desks available!</p>";
            }
        } else {
            echo "<p class='alert alert-danger'>Student already assigned to a desk.</p>";
        }
    } else {
        echo "<p class='alert alert-danger'>Student not found.</p>";
    }
}

// Fetch all desk assignments for displaying the seating arrangement
$seating_query = "SELECT sa.desk_number, s.name 
                  FROM sitting_arrangement sa 
                  JOIN student_data s ON sa.roll_no = s.roll_no";
$seating_result = $conn->query($seating_query);

$seating_arrangement = [];
if ($seating_result->num_rows > 0) {
    while ($row = $seating_result->fetch_assoc()) {
        $seating_arrangement[$row['desk_number']] = $row['name'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seating Arrangement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .desk {
            width: 100px;
            height: 100px;
            border: 2px solid black;
            margin: 10px;
            text-align: center;
            line-height: 100px;
            font-size: 1.2em;
            display: inline-block;
        }
        .assigned {
            background-color: lightgreen;
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <h1 class="text-center">Seating Arrangement</h1>

        <!-- Form to enter roll number -->
        <form action="" method="POST" class="mb-3">
            <div class="mb-3">
                <label for="roll_no" class="form-label">Enter Roll Number:</label>
                <input type="number" name="roll_no" id="roll_no" class="form-control" placeholder="Roll Number" required>
            </div>
            <button type="submit" class="btn btn-primary">Assign Seat</button>
        </form>

        <!-- Display Desk Grid -->
        <h2 class="text-center">Classroom Seating</h2>
        <div class="d-flex justify-content-center flex-wrap">
        <?php
            // Define number of desks and rows/columns
            $total_desks = 20; // Total desks in the class
            $rows = 5;         // Number of rows
            $cols = ceil($total_desks / $rows);  // Calculate the number of columns

            // Create desk grid column-wise
            for ($row = 1; $row <= $rows; $row++) {
                for ($col = 1; $col <= $cols; $col++) {
                    // Calculate the desk number based on column-first arrangement
                    $desk = ($col - 1) * $rows + $row;

                    if ($desk <= $total_desks) {
                        $roll_number = isset($seating_arrangement[$desk]) ? $seating_arrangement[$desk] : '';

                        // Desk element
                        echo "<div class='desk " . ($roll_number ? 'assigned' : '') . "'>";
                        echo $roll_number ? "$roll_number" : "Desk $desk"; // Display roll number if assigned
                        echo "</div>";
                    }
                }
                // Add a line break after each row
                echo "<div class='w-100'></div>";
            }
            ?>
        </div>
    </div>
</body>
</html>  