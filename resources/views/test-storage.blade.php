<!-- Test Image Display -->
<div style="padding: 20px; background: #f8f9fa; margin: 20px; border-radius: 8px;">
    <h4>Storage Image Test</h4>
    
    @php
        // Test if storage link exists
        $storageExists = file_exists(public_path('storage'));
        $samplePath = 'reviews/6/gmq0XiJEIOa50TvI3tNbryXeL6DoTERNmM6ul6Qf.jpg';
        $fullPath = storage_path('app/public/' . $samplePath);
        $fileExists = file_exists($fullPath);
        $publicPath = public_path('storage/' . $samplePath);
        $publicExists = file_exists($publicPath);
    @endphp
    
    <div class="row">
        <div class="col-md-6">
            <h6>Storage Status:</h6>
            <ul>
                <li>Storage Symlink: {{ $storageExists ? '✅ Exists' : '❌ Missing' }}</li>
                <li>Original File: {{ $fileExists ? '✅ Exists' : '❌ Missing' }}</li>
                <li>Public Access: {{ $publicExists ? '✅ Accessible' : '❌ Not accessible' }}</li>
            </ul>
            
            <h6>Generated URLs:</h6>
            <ul>
                <li><strong>asset():</strong> {{ asset('storage/' . $samplePath) }}</li>
                <li><strong>Storage::url():</strong> {{ Storage::url($samplePath) }}</li>
            </ul>
        </div>
        
        <div class="col-md-6">
            <h6>Test Images:</h6>
            
            <p><strong>Using asset() helper:</strong></p>
            <img src="{{ asset('storage/' . $samplePath) }}" 
                 alt="Test Image" 
                 style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #ccc;"
                 onerror="this.style.border='2px solid red'; this.alt='Image not found';">
            
            <p><strong>Using Storage::url():</strong></p>
            <img src="{{ Storage::url($samplePath) }}" 
                 alt="Test Image" 
                 style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #ccc;"
                 onerror="this.style.border='2px solid red'; this.alt='Image not found';">
        </div>
    </div>
</div>