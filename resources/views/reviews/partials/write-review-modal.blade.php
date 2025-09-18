<!-- ================================================================================================ -->
<!-- 📝 WRITE REVIEW MODAL - Bootstrap 5 Professional Design -->
<!-- ================================================================================================ -->
<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-labelledby="writeReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('review.store', $product) }}" method="POST" enctype="multipart/form-data" id="reviewForm">
                @csrf
                
                <div class="modal-header">
                    <h5 class="modal-title" id="writeReviewModalLabel">
                        <i class="fas fa-star text-warning me-2"></i>Write a Review
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Product Info -->
                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                        <img src="{{ $product->image_url ?? '/images/placeholder.jpg' }}" alt="{{ $product->name }}" 
                             class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        <div>
                            <h6 class="mb-1">{{ $product->name }}</h6>
                            <small class="text-muted">You're reviewing this product</small>
                        </div>
                    </div>

                    <!-- Rating Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Overall Rating <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center mb-2">
                            <div class="rating-stars me-3" data-rating="0">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="far fa-star fa-2x text-warning" data-star="{{ $i }}" style="cursor: pointer;"></i>
                                @endfor
                            </div>
                            <span id="rating-text" class="text-muted">Click to rate</span>
                        </div>
                        <input type="hidden" name="rating" id="rating-input" required>
                        <div class="invalid-feedback" id="rating-error"></div>
                    </div>

                    <!-- Review Title -->
                    <div class="mb-3">
                        <label for="review-title" class="form-label fw-bold">Review Title</label>
                        <input type="text" class="form-control" id="review-title" name="title" 
                               placeholder="Summarize your experience in a few words" maxlength="200">
                        <div class="form-text">Optional - Help others understand your review at a glance</div>
                    </div>

                    <!-- Review Comment -->
                    <div class="mb-3">
                        <label for="review-comment" class="form-label fw-bold">Your Review <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="review-comment" name="comment" rows="5" 
                                  placeholder="Share your experience with this product. What did you like or dislike? How did it meet your expectations?" 
                                  minlength="10" maxlength="2000" required></textarea>
                        <div class="form-text">
                            <span id="comment-count">0</span>/2000 characters (minimum 10)
                        </div>
                    </div>

                    <!-- Product Variant -->
                    <div class="mb-3">
                        <label for="product-variant" class="form-label fw-bold">Product Variant</label>
                        <input type="text" class="form-control" id="product-variant" name="product_variant" 
                               placeholder="e.g., Size M, Red Color, 32GB" maxlength="100">
                        <div class="form-text">Specify the exact variant you purchased (size, color, etc.)</div>
                    </div>

                    <!-- Would Recommend -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Would you recommend this product?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="would_recommend" id="recommend-yes" value="1">
                            <label class="form-check-label" for="recommend-yes">
                                <i class="fas fa-thumbs-up text-success me-1"></i>Yes, I would recommend
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="would_recommend" id="recommend-no" value="0">
                            <label class="form-check-label" for="recommend-no">
                                <i class="fas fa-thumbs-down text-danger me-1"></i>No, I would not recommend
                            </label>
                        </div>
                    </div>

                    <!-- Photo Upload -->
                    <div class="mb-3">
                        <label for="review-photos" class="form-label fw-bold">Add Photos</label>
                        <div class="input-group">
                            <input class="form-control" type="file" id="review-photos" name="photos[]" 
                                   multiple accept="image/*" onchange="previewImages(this)">
                            <span class="input-group-text">
                                <i class="fas fa-camera"></i>
                            </span>
                        </div>
                        <div class="form-text">
                            Upload up to 5 photos (JPG, PNG, GIF - Max 2MB each)
                        </div>
                        
                        <!-- Photo Preview -->
                        <div id="photo-preview" class="mt-3 d-none">
                            <div class="row g-2" id="preview-container"></div>
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb me-2"></i>Review Guidelines</h6>
                        <ul class="mb-0 small">
                            <li>Be honest and helpful to other customers</li>
                            <li>Focus on the product features and your experience</li>
                            <li>Avoid inappropriate language or personal information</li>
                            <li>Include specific details about quality, performance, and value</li>
                        </ul>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit-review">
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
    // 📝 REVIEW FORM SCRIPTS
    // ================================================================================================
    
    // Star Rating System
    const stars = document.querySelectorAll('.rating-stars i');
    const ratingInput = document.getElementById('rating-input');
    const ratingText = document.getElementById('rating-text');
    
    const ratingLabels = {
        1: 'Poor - Disappointing',
        2: 'Fair - Below expectations', 
        3: 'Good - Meets expectations',
        4: 'Very Good - Exceeds expectations',
        5: 'Excellent - Outstanding!'
    };
    
    stars.forEach((star, index) => {
        // Hover effect
        star.addEventListener('mouseenter', function() {
            highlightStars(index + 1);
            ratingText.textContent = ratingLabels[index + 1];
        });
        
        // Click to select
        star.addEventListener('click', function() {
            const rating = index + 1;
            ratingInput.value = rating;
            setRating(rating);
            ratingText.textContent = ratingLabels[rating];
        });
    });
    
    // Reset on mouse leave
    document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
        const currentRating = ratingInput.value;
        if (currentRating) {
            setRating(currentRating);
            ratingText.textContent = ratingLabels[currentRating];
        } else {
            highlightStars(0);
            ratingText.textContent = 'Click to rate';
        }
    });
    
    function highlightStars(count) {
        stars.forEach((star, index) => {
            if (index < count) {
                star.className = 'fas fa-star fa-2x text-warning';
            } else {
                star.className = 'far fa-star fa-2x text-warning';
            }
        });
    }
    
    function setRating(rating) {
        highlightStars(rating);
    }
    
    // Character Counter for Comment
    const commentTextarea = document.getElementById('review-comment');
    const commentCount = document.getElementById('comment-count');
    
    commentTextarea.addEventListener('input', function() {
        const length = this.value.length;
        commentCount.textContent = length;
        
        if (length < 10) {
            commentCount.className = 'text-danger';
        } else if (length > 1800) {
            commentCount.className = 'text-warning';
        } else {
            commentCount.className = 'text-success';
        }
    });
    
    // Form Validation
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        const rating = ratingInput.value;
        const comment = commentTextarea.value;
        
        if (!rating) {
            e.preventDefault();
            document.getElementById('rating-error').textContent = 'Please select a rating';
            document.getElementById('rating-error').style.display = 'block';
            return false;
        }
        
        if (comment.length < 10) {
            e.preventDefault();
            commentTextarea.classList.add('is-invalid');
            return false;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submit-review');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        submitBtn.disabled = true;
    });
});

// Image Preview Function
function previewImages(input) {
    const previewDiv = document.getElementById('photo-preview');
    const container = document.getElementById('preview-container');
    
    // Clear previous previews
    container.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        previewDiv.classList.remove('d-none');
        
        // Limit to 5 files
        const files = Array.from(input.files).slice(0, 5);
        
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const colDiv = document.createElement('div');
                    colDiv.className = 'col-auto';
                    colDiv.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-thumbnail" 
                                 style="width: 80px; height: 80px; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                    style="transform: translate(50%, -50%); border-radius: 50%; width: 25px; height: 25px; padding: 0;"
                                    onclick="removePreviewImage(this, ${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(colDiv);
                };
                reader.readAsDataURL(file);
            }
        });
        
        if (files.length >= 5) {
            input.setAttribute('disabled', 'disabled');
        }
    } else {
        previewDiv.classList.add('d-none');
    }
}

function removePreviewImage(button, index) {
    // Remove the preview
    button.closest('.col-auto').remove();
    
    // Re-enable file input if disabled
    const fileInput = document.getElementById('review-photos');
    fileInput.removeAttribute('disabled');
    
    // Note: Removing files from FileList is complex, so we'll handle this differently
    // For now, we'll just remove the visual preview
    const container = document.getElementById('preview-container');
    if (container.children.length === 0) {
        document.getElementById('photo-preview').classList.add('d-none');
    }
}
</script>