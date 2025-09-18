<!-- ================================================================================================ -->
<!-- 📝 WRITE REVIEW MODAL - Bootstrap 5 Professional Design -->
<!-- ================================================================================================ -->

<!-- Write Review Modal -->
<div class="modal fade" id="writeReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-star text-warning me-2"></i>Write a Review for {{ $product->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="{{ route('review.store', $product) }}" method="POST" enctype="multipart/form-data" id="reviewForm">
                @csrf
                <div class="modal-body">
                    <!-- Product Info -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <img src="{{ $product->image_url ?? '/images/placeholder.jpg' }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <small class="text-muted">${{ number_format($product->price, 2) }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Overall Rating <span class="text-danger">*</span></label>
                        <div class="rating-input d-flex align-items-center">
                            <div class="star-rating-input me-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="far fa-star rating-star" data-rating="{{ $i }}" style="font-size: 2rem; cursor: pointer; color: #ddd;"></i>
                                @endfor
                            </div>
                            <span id="ratingText" class="text-muted">Click stars to rate</span>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" required>
                    </div>

                    <!-- Review Title -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Review Title</label>
                        <input type="text" class="form-control" name="title" 
                               placeholder="Summarize your review in a few words..." maxlength="200">
                        <div class="form-text">Optional: Give your review a helpful title</div>
                    </div>

                    <!-- Review Comment -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Your Review <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="comment" rows="4" required 
                                  placeholder="Tell others about your experience with this product..." 
                                  minlength="10" maxlength="2000"></textarea>
                        <div class="form-text">Minimum 10 characters, maximum 2000 characters</div>
                    </div>

                    <!-- Product Variant -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Variant</label>
                        <input type="text" class="form-control" name="product_variant" 
                               placeholder="e.g., Size: Large, Color: Blue" maxlength="100">
                        <div class="form-text">Optional: Specify the variant you purchased</div>
                    </div>

                    <!-- Would Recommend -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Would you recommend this product?</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="would_recommend" value="1" id="recommendYes">
                                <label class="form-check-label text-success" for="recommendYes">
                                    <i class="fas fa-thumbs-up me-1"></i>Yes, I recommend it
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="would_recommend" value="0" id="recommendNo">
                                <label class="form-check-label text-warning" for="recommendNo">
                                    <i class="fas fa-thumbs-down me-1"></i>No, I don't recommend it
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Photos -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Add Photos</label>
                        <input type="file" class="form-control" name="photos[]" multiple accept="image/*" 
                               id="photoInput" onchange="previewPhotos(this)">
                        <div class="form-text">Optional: Add up to 5 photos (JPG, PNG, GIF - Max 2MB each)</div>
                        
                        <!-- Photo Preview -->
                        <div id="photoPreview" class="mt-2 d-none">
                            <div class="row g-2" id="photoPreviewContainer"></div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitReviewBtn">
                        <i class="fas fa-paper-plane me-2"></i>Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ================================================================================================
    // 📝 RATING SYSTEM
    // ================================================================================================
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('ratingInput');
    const ratingText = document.getElementById('ratingText');
    
    const ratingTexts = {
        1: '⭐ Poor',
        2: '⭐⭐ Fair', 
        3: '⭐⭐⭐ Good',
        4: '⭐⭐⭐⭐ Very Good',
        5: '⭐⭐⭐⭐⭐ Excellent'
    };

    stars.forEach(star => {
        // Hover effect
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            highlightStars(rating);
        });

        // Click to select
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            selectRating(rating);
        });
    });

    // Reset on mouse leave
    document.querySelector('.star-rating-input').addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingInput.value) || 0;
        highlightStars(currentRating);
    });

    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.className = 'fas fa-star rating-star';
                star.style.color = '#ffc107';
            } else {
                star.className = 'far fa-star rating-star';
                star.style.color = '#ddd';
            }
        });
    }

    function selectRating(rating) {
        ratingInput.value = rating;
        ratingText.textContent = ratingTexts[rating];
        ratingText.className = 'text-warning fw-bold';
        highlightStars(rating);
    }

    // ================================================================================================
    // 📝 PHOTO PREVIEW
    // ================================================================================================
    window.previewPhotos = function(input) {
        const preview = document.getElementById('photoPreview');
        const container = document.getElementById('photoPreviewContainer');
        
        container.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            if (input.files.length > 5) {
                alert('Maximum 5 photos allowed');
                input.value = '';
                preview.classList.add('d-none');
                return;
            }
            
            preview.classList.remove('d-none');
            
            Array.from(input.files).forEach((file, index) => {
                if (file.size > 2 * 1024 * 1024) {
                    alert(`File ${file.name} is too large. Maximum 2MB per file.`);
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'col-auto';
                    div.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" alt="Preview" 
                                 class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                    style="transform: translate(50%, -50%); padding: 2px 6px;"
                                    onclick="removePhoto(this, ${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        } else {
            preview.classList.add('d-none');
        }
    };

    window.removePhoto = function(button, index) {
        const input = document.getElementById('photoInput');
        const dt = new DataTransfer();
        
        Array.from(input.files).forEach((file, i) => {
            if (i !== index) dt.items.add(file);
        });
        
        input.files = dt.files;
        button.closest('.col-auto').remove();
        
        if (input.files.length === 0) {
            document.getElementById('photoPreview').classList.add('d-none');
        }
    };

    // ================================================================================================
    // 📝 FORM VALIDATION
    // ================================================================================================
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        const rating = document.getElementById('ratingInput').value;
        const comment = document.querySelector('textarea[name="comment"]').value;
        
        if (!rating) {
            e.preventDefault();
            alert('Please select a rating');
            return false;
        }
        
        if (comment.length < 10) {
            e.preventDefault();
            alert('Review must be at least 10 characters long');
            return false;
        }
        
        // Disable submit button to prevent double submission
        document.getElementById('submitReviewBtn').disabled = true;
        document.getElementById('submitReviewBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    });
});
</script>