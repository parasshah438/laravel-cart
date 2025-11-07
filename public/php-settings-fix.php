<?php
// Temporarily increase PHP limits for image optimization testing
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '12M');
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120);
ini_set('max_input_time', 120);

// Display current settings
echo "<h2>Updated PHP Settings for Image Optimization</h2>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr style='background-color: #f0f0f0;'><th>Setting</th><th>Value</th><th>Status</th></tr>";

$settings = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
];

foreach ($settings as $key => $value) {
    $status = '✅ OK';
    if ($key === 'upload_max_filesize' || $key === 'post_max_size') {
        $mb_value = (int)$value;
        if ($mb_value < 10) $status = '❌ Too Low';
    }
    echo "<tr><td><strong>{$key}</strong></td><td>{$value}</td><td>{$status}</td></tr>";
}
echo "</table>";

echo "<h2>Test 4MB Image Upload</h2>";
echo '<p>With these settings, you should be able to upload files up to 10MB.</p>';
echo '<a href="../quick-test.php" style="display: inline-block; background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Upload Test</a>';
?>

<style>
body { font-family: Arial, sans-serif; margin: 40px; max-width: 800px; }
table { width: 100%; margin: 20px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
</style>