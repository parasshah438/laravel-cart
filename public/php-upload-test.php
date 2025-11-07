<?php
// Quick PHP settings check
echo "<h2>Current PHP Upload Settings</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>max_execution_time</td><td>" . ini_get('max_execution_time') . "</td></tr>";
echo "<tr><td>max_input_time</td><td>" . ini_get('max_input_time') . "</td></tr>";
echo "<tr><td>file_uploads</td><td>" . (ini_get('file_uploads') ? 'On' : 'Off') . "</td></tr>";
echo "</table>";

echo "<h2>File Upload Test</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo "<h3>Upload Results:</h3>";
    echo "<pre>";
    var_dump($_FILES);
    echo "</pre>";
    
    if ($_FILES['test_file']['error'] !== UPLOAD_ERR_OK) {
        echo "<p style='color: red;'>Upload Error Code: " . $_FILES['test_file']['error'] . "</p>";
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        echo "<p style='color: red;'>Error: " . ($errors[$_FILES['test_file']['error']] ?? 'Unknown error') . "</p>";
    } else {
        echo "<p style='color: green;'>✅ File uploaded successfully!</p>";
        echo "<p>Size: " . number_format($_FILES['test_file']['size'] / 1024 / 1024, 2) . " MB</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <h3>Test File Upload (up to 10MB)</h3>
    <input type="file" name="test_file" accept="image/*">
    <button type="submit">Test Upload</button>
</form>