<?php
// Apply PHP settings for large uploads
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '12M');
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120);
ini_set('max_input_time', 120);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Image Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 600px; }
        .upload-area { border: 2px dashed #ddd; padding: 20px; text-align: center; margin: 20px 0; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .result { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px; }
        .error { background: #ffebee; color: #c62828; }
        .success { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quick Upload Test</h1>
        <p><strong>Current PHP Settings:</strong></p>
        <ul>
            <li>upload_max_filesize: <?php echo ini_get('upload_max_filesize'); ?></li>
            <li>post_max_size: <?php echo ini_get('post_max_size'); ?></li>
            <li>memory_limit: <?php echo ini_get('memory_limit'); ?></li>
            <li>max_execution_time: <?php echo ini_get('max_execution_time'); ?></li>
        </ul>
        
        <form id="quick-upload-form" enctype="multipart/form-data">
            <div class="upload-area">
                <input type="file" name="test_image" id="test_image" accept="image/*" required>
                <p>Select an image file to test optimization</p>
            </div>
            <button type="submit" class="btn">Test Upload & Optimize</button>
        </form>
        
        <div id="result"></div>
    </div>

    <script>
    document.getElementById('quick-upload-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        const fileInput = document.getElementById('test_image');
        const file = fileInput.files[0];
        
        if (!file) {
            document.getElementById('result').innerHTML = '<div class="result error">Please select a file</div>';
            return;
        }
        
        // Show file info
        document.getElementById('result').innerHTML = `
            <div class="result">
                <p><strong>Uploading:</strong> ${file.name}</p>
                <p><strong>Size:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                <p><em>Processing...</em></p>
            </div>
        `;
        
        formData.append('test_image', file);
        
        try {
            const response = await fetch('/test-image-upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
                }
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', [...response.headers.entries()]);
            
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            
            // Check if response is JSON
            let result;
            try {
                result = JSON.parse(responseText);
            } catch(e) {
                console.error('Failed to parse JSON:', e);
                throw new Error(`Server returned non-JSON response: ${responseText.substring(0, 200)}...`);
            }
            
            if (result.success) {
                document.getElementById('result').innerHTML = `
                    <div class="result success">
                        <h3>✅ Upload Successful!</h3>
                        <p><strong>Original Size:</strong> ${result.original_size}</p>
                        <p><strong>Optimized Size:</strong> ${result.optimized_size}</p>
                        <p><strong>Compression:</strong> ${result.compression_ratio}%</p>
                        <p><strong>Files Created:</strong></p>
                        <ul>${result.files.map(file => typeof file === 'object' ? Object.values(file).map(f => `<li>${f}</li>`).join('') : `<li>${file}</li>`).join('')}</ul>
                    </div>
                `;
            } else {
                document.getElementById('result').innerHTML = `
                    <div class="result error">
                        <h3>❌ Upload Failed</h3>
                        <p>${result.message}</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Upload error:', error);
            document.getElementById('result').innerHTML = `
                <div class="result error">
                    <h3>❌ Upload Error</h3>
                    <p>${error.message}</p>
                </div>
            `;
        }
    });
    </script>
</body>
</html>