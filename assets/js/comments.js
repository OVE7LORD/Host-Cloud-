// Comments functionality
$(document).ready(function() {
    // Handle comment creation
    $('#comment-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = form.serialize();
        
        $.ajax({
            url: 'handlers/post_comment.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadComments();
                    form.trigger('reset');
                } else {
                    alert('Error: ' + (response.message || 'Failed to post comment'));
                }
            },
            error: function() {
                alert('Error: Could not connect to server');
            }
        });
    });

    // Handle comment edit
    $(document).on('click', '.edit-comment', function() {
        const commentId = $(this).data('comment-id');
        const commentContent = $(this).closest('.comment').find('.comment-content').text().trim();
        
        const editForm = `
            <form class="edit-comment-form">
                <input type="hidden" name="comment_id" value="${commentId}">
                <textarea name="content" class="form-control" rows="3" required>${commentContent}</textarea>
                <button type="submit" class="btn btn-primary btn-sm mt-2">Update</button>
                <button type="button" class="btn btn-secondary btn-sm mt-2 cancel-edit">Cancel</button>
            </form>
        `;
        
        $(this).closest('.comment').find('.comment-content').html(editForm);
    });

    // Handle edit form submission
    $(document).on('submit', '.edit-comment-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = form.serialize();
        
        $.ajax({
            url: 'handlers/update_comment.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadComments();
                } else {
                    alert('Error: ' + (response.message || 'Failed to update comment'));
                }
            },
            error: function() {
                alert('Error: Could not connect to server');
            }
        });
    });

    // Handle edit cancel
    $(document).on('click', '.cancel-edit', function() {
        loadComments();
    });

    // Handle delete
    $(document).on('click', '.delete-comment', function() {
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }
        
        const commentId = $(this).data('comment-id');
        
        $.ajax({
            url: 'handlers/delete_comment.php',
            method: 'POST',
            data: { comment_id: commentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadComments();
                } else {
                    alert('Error: ' + (response.message || 'Failed to delete comment'));
                }
            },
            error: function() {
                alert('Error: Could not connect to server');
            }
        });
    });

    // Load comments function
    function loadComments() {
        const postId = $('.comments-container').data('post-id');
        if (!postId) {
            console.error('Post ID not found');
            return;
        }

        $.ajax({
            url: 'handlers/get-comments.php',
            method: 'GET',
            data: { post_id: postId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.comments-container').html(response.html || 'No comments yet.');
                } else {
                    console.error('Error loading comments:', response.message || 'Unknown error');
                    $('.comments-container').html('Error loading comments. Please try again later.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                $('.comments-container').html('Error loading comments. Please try again later.');
            }
        });
    }

    // Initial load
    loadComments();
});
