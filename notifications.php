<?php
header('Content-Type: application/json');

$winners_folder = "winners/";
$files = glob($winners_folder . "*.xlsx"); 

if (!empty($files)) {
    $latest_file = max($files); 
 $notifications = [
        ["title" => "🏆 Sports Winners Announced!", "link" => "display.php"]
    ];
} else {
    $notifications = [
        ["title" => "📢 Sports Results Not Yet Announced!", "link" => "#"]
    ];
}
echo json_encode($notifications);
?>
