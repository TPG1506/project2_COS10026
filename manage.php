<?php
require_once "settings.php"; // Include database settings

// Establish connection
$dbconn = @mysqli_connect($host, $user, $pwd, $sql_db);

if (!$dbconn) {
    die("<p class='error-message'>Unable to connect to the database: " . mysqli_connect_error() . "</p>");
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Delete EOIs for a specific job reference
    if (isset($_POST['delete_job_ref'])) {
        $job_ref = mysqli_real_escape_string($dbconn, $_POST['job_ref_to_delete']);
        $delete_query = "DELETE FROM eoi WHERE JobReferenceNumber = '$job_ref'";
        if (mysqli_query($dbconn, $delete_query)) {
            echo "<p class='success-message'>Successfully deleted EOIs with job reference: " . htmlspecialchars($job_ref) . "</p>";
        } else {
            echo "<p class='error-message'>Error deleting records: " . mysqli_error($dbconn) . "</p>";
        }
    }
    
    // Update EOI status
    if (isset($_POST['update_status'])) {
        $eoi_id = mysqli_real_escape_string($dbconn, $_POST['eoi_id']);
        $new_status = mysqli_real_escape_string($dbconn, $_POST['new_status']);
        $update_query = "UPDATE eoi SET STATUS = '$new_status' WHERE EOINumber = '$eoi_id'";
        if (mysqli_query($dbconn, $update_query)) {
            echo "<p class='success-message'>Successfully updated status for EOI #" . htmlspecialchars($eoi_id) . "</p>";
        } else {
            echo "<p class='error-message'>Error updating status: " . mysqli_error($dbconn) . "</p>";
        }
    }
}

// Determine query based on filter parameters
$query = "SELECT * FROM eoi";
$where_clauses = [];

// Filter by job reference if provided
if (isset($_GET['job_ref']) && !empty($_GET['job_ref'])) {
    $job_ref = mysqli_real_escape_string($dbconn, $_GET['job_ref']);
    $where_clauses[] = "JobReferenceNumber = '$job_ref'";
}

// Filter by first name if provided
if (isset($_GET['first_name']) && !empty($_GET['first_name'])) {
    $first_name = mysqli_real_escape_string($dbconn, $_GET['first_name']);
    $where_clauses[] = "first_name LIKE '%$first_name%'";
}

// Filter by last name if provided
if (isset($_GET['last_name']) && !empty($_GET['last_name'])) {
    $last_name = mysqli_real_escape_string($dbconn, $_GET['last_name']);
    $where_clauses[] = "last_name LIKE '%$last_name%'";
}

// Construct the WHERE clause if any filters are applied
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Execute the query
$result = mysqli_query($dbconn, $query);
$query_error = "";
if (!$result) {
    $query_error = "Database error: " . mysqli_error($dbconn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles/style.css" />
</head>
<body class="managepage">
    <?php require_once("menu.inc"); ?>
    <div id="page-container">
        <h1 class="page-title">EOI Management System</h1>
        
        <!-- Filter Form -->
        <div id="filter-container" class="form-container">
            <h3 class="section-title">Filter EOIs</h3>
            <form id="filter-form" method="GET" action="">
                <div class="form-group">
                    <label for="job_ref" class="form-label">Job Reference:</label>
                    <div class="form-input">
                        <select id="job_ref" name="job_ref" class="form-select">
                            <option value="" disabled selected>Select a job reference</option>
                            <option value="GD123" <?php echo (isset($_GET['job_ref']) && $_GET['job_ref'] == 'GD123') ? 'selected' : ''; ?>>Game Developer: GD123</option>
                            <option value="GA456" <?php echo (isset($_GET['job_ref']) && $_GET['job_ref'] == 'GA456') ? 'selected' : ''; ?>>Game Artist: GA456</option>
                            <option value="SD789" <?php echo (isset($_GET['job_ref']) && $_GET['job_ref'] == 'SD789') ? 'selected' : ''; ?>>Sound Designer: SD789</option>
                            <option value="GT101" <?php echo (isset($_GET['job_ref']) && $_GET['job_ref'] == 'GT101') ? 'selected' : ''; ?>>Game Tester: GT101</option>
                            <option value="GW202" <?php echo (isset($_GET['job_ref']) && $_GET['job_ref'] == 'GW202') ? 'selected' : ''; ?>>Game Writer: GW202</option>
                            <option value="IX303" <?php echo (isset($_GET['job_ref']) && $_GET['job_ref'] == 'IX303') ? 'selected' : ''; ?>>UI/UX Designer: IX303</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="first_name" class="form-label">First Name:</label>
                    <input type="text" id="first_name" name="first_name" class="form-input" value="<?php echo isset($_GET['first_name']) ? htmlspecialchars($_GET['first_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" class="form-input" value="<?php echo isset($_GET['last_name']) ? htmlspecialchars($_GET['last_name']) : ''; ?>">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="manage.php"><button type="button" class="btn btn-secondary">Reset</button></a>
                </div>
            </form>
        </div>
        
        <!-- Delete EOIs Form -->
        <div id="delete-container" class="form-container">
            <h3 class="section-title">Delete EOIs by Job Reference</h3>
            <form id="delete-form" method="POST" action="" onsubmit="return confirm('Are you sure you want to delete all EOIs with this job reference? This action cannot be undone.');">
                <div class="form-group">
                    <label for="job_ref_to_delete" class="form-label">Job Reference:</label>
                    <select id="job_ref_to_delete" name="job_ref_to_delete" class="form-select" required>
                        <option value="" disabled selected>Select a job reference</option>
                        <option value="GD123">Game Developer: GD123</option>
                        <option value="GA456">Game Artist: GA456</option>
                        <option value="SD789">Sound Designer: SD789</option>
                        <option value="GT101">Game Tester: GT101</option>
                        <option value="GW202">Game Writer: GW202</option>
                        <option value="IX303">UI/UX Designer: IX303</option>
                    </select>
                </div>
                <div class="form-buttons">
                    <button type="submit" name="delete_job_ref" class="btn btn-danger">Delete EOIs</button>
                </div>
            </form>
        </div>
                
        <!-- Display EOIs -->
        <div id="eoi-results">
            <h2 class="section-title">Expressions of Interest</h2>
            <?php
            if ($query_error) {
                echo "<p class='error-message'>$query_error</p>";
            } else if ($result && mysqli_num_rows($result) > 0) {
                // Get column names
                $first_row = mysqli_fetch_assoc($result);
                mysqli_data_seek($result, 0); // Reset result pointer
                
                echo "<table id='eoi-table' class='data-table'><tr class='table-header'>";
                
                // Table headers
                foreach ($first_row as $column => $value) { 
                    echo "<th class='table-heading'>" . htmlspecialchars($column) . "</th>";
                }
                echo "<th class='table-heading'>Actions</th></tr>";
                
                // Display data
                $row_counter = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $row_class = ($row_counter % 2 == 0) ? 'even-row' : 'odd-row';
                    echo "<tr class='$row_class'>";
                    foreach ($row as $key => $value) {
                        echo "<td class='table-cell'>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "<td class='action-cell'>
                        <form method='POST' action='' class='status-form'>
                            <input type='hidden' name='eoi_id' value='" . $row['EOINumber'] . "'>
                            <select name='new_status' class='status-select'>
                                <option value='New' " . ($row['STATUS'] == 'New' ? 'selected' : '') . ">New</option>
                                <option value='Current' " . ($row['STATUS'] == 'Current' ? 'selected' : '') . ">Current</option>
                                <option value='Final' " . ($row['STATUS'] == 'Final' ? 'selected' : '') . ">Final</option>
                            </select>
                            <button type='submit' name='update_status' class='btn btn-update'>Update Status</button>
                        </form>
                    </td>";
                    echo "</tr>";
                    $row_counter++;
                }
                
                echo "</table>";
                
                // Display count of results
                mysqli_data_seek($result, 0);
                $count = mysqli_num_rows($result);
                echo "<p class='result-count'><strong>$count</strong> EOI(s) found</p>";
            } else {
                echo "<p class='no-results'>No EOIs found matching your criteria.</p>";
            }
            
            // Close connection
            if ($result && is_object($result)) {
                mysqli_free_result($result);
            }
            mysqli_close($dbconn);
            ?>
        </div>
    </div>
    <?php require_once("footer.inc"); ?>
</body>
</html>