<?php
//to display winners
require 'SimpleXLSX.php';

$latestFile = file_exists("latest_winner_file.txt") ? file_get_contents("latest_winner_file.txt") : "";

// Check if file exists and is within the last 30 days
if (!$latestFile || !file_exists($latestFile) || (time() - filemtime($latestFile)) > (30 * 24 * 60 * 60)) {
    echo "<h2 style='text-align:center;color:red;'>🏆 Results Not Yet Released 🏆</h2>";
    exit();
}

// Function to get medal emoji
function getMedal($position) {
    return strpos($position, '1st') !== false ? '🏅' :
           (strpos($position, '2nd') !== false ? '🥈' :
           (strpos($position, '3rd') !== false ? '🥉' : ''));
}

$winners = ["1st" => [], "2nd" => [], "3rd" => []];

if ($xlsx = new SimpleXLSX($latestFile)) {
    $rows = $xlsx->rows();
    array_shift($rows); 
    foreach ($rows as $data) {
        if (count($data) < 5) continue;
        $position = trim($data[4]) ?? '';
        if (strpos($position, '1st') !== false) {
            $winners["1st"][] = $data;
        } elseif (strpos($position, '2nd') !== false) {
            $winners["2nd"][] = $data;
        } elseif (strpos($position, '3rd') !== false) {
            $winners["3rd"][] = $data;
        }
    }
} else {
    echo "<h2 style='text-align:center;color:red;'>Error reading file</h2>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winners List</title>
    <link rel="shortcut icon" href="IMG/FAVICON.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            background-color: #336699;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .winner-section {
            margin-top: 30px;
            padding: 15px;
            border-radius: 8px;
            background-color:rgba(248, 249, 250, 0.);
            text-align: center;
            font-weight: bold;
            font-size: 1.3rem;
        }
        .table {
            margin-top: 15px;
        }
        .table th {
            background-color:rgba(0, 123, 255, 0.6);
            color: white;
        }
  
    </style>
</head>
<body>
    <div class="container mt-3">
        <h2 class="text-center">🏆 Winners List 🏆</h2>
        <?php foreach (["1st", "2nd", "3rd"] as $place): ?>
            <?php if (!empty($winners[$place])): ?>
                <div class="text-center fw-bold fs-4 mt-4"><?php echo $place; ?> Place Winners</div>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>PIN Number</th>
                            <th>Branch</th>
                            <th>Sport</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($winners[$place] as $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data[0] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($data[1] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($data[2] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($data[3] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($data[4] ?? '') . " " . getMedal($data[4] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>
