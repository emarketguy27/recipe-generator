jQuery(document).ready(function($) {
    $('#recipe-generator-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $results = $('#recipe-results');
        var $loading = $('.rg-loading');
        var $submitBtn = $form.find('.rg-submit');
        
        // Show loading state
        $submitBtn.prop('disabled', true);
        $loading.show();
        $results.hide().empty().removeClass('show');
        
        // Prepare data
        var formData = {
            action: 'recipe_generator_generate_recipe',
            _wpnonce: $form.find('input[name="_wpnonce"]').val(),
            servings: $form.find('#rg-servings').val(),
            include: $form.find('#rg-include').val(),
            exclude: $form.find('#rg-exclude').val(),
            dietary: $form.find('input[name="dietary[]"]:checked').map(function() {
                return this.value;
            }).get()
        };
        
        // Make the request
        $.post(recipeGeneratorFrontendVars.ajaxurl, formData, function(response) {
            if (response.success) {
                $results.html(response.data.html).addClass('show').show();
                $('#recipe-actions').show();
                
                // Smooth scroll to results with proper positioning
                $('html, body').stop().animate({
                    scrollTop: $results.offset().top - 150
                }, 500);
            } else {
                $results.html(
                    '<div class="error">' + 
                    (response.data || recipeGeneratorFrontendVars.errorOccurred) + 
                    '</div>'
                ).addClass('show').show();
            }
        }).fail(function() {
            $results.html(
                '<div class="error">' + recipeGeneratorFrontendVars.errorOccurred + '</div>'
            ).addClass('show').show();
        }).always(function() {
            $loading.hide();
            $submitBtn.prop('disabled', false);
        });
    });

    $(document).on('click', '#save-recipe-btn', function() {
        const $btn = $(this);
        const $status = $('#save-status');
        const recipeHtml = $('#recipe-results').html();
        
        // Generate unique ID from recipe content
        const recipeId = generateRecipeId(recipeHtml);
        
        $btn.prop('disabled', true);
        $status.text('Saving...');
        
        $.post(recipeGeneratorFrontendVars.ajaxurl, {
            action: 'save_ai_recipe_to_favorites',
            recipe_id: recipeId,
            recipe_html: recipeHtml,
            _wpnonce: recipeGeneratorFrontendVars.nonce
        }, function(response) {
            if (response.success) {
                $btn.text('Saved!').prop('disabled', true);
                $status.text('Recipe saved to your favorites!');
                
                // Update the count if on saved recipes page
                const $countDisplay = $('.recipe-count');
                if ($countDisplay.length) {
                    const currentCount = parseInt($countDisplay.text().replace(/[()]/g, '')) || 0;
                    $countDisplay.text('(' + (currentCount + 1) + ')');
                }
            } else {
                $status.text('Error: ' + (response.data || 'Could not save recipe'));
                $btn.prop('disabled', false);
            }
        }).fail(function() {
            $status.text('Connection error. Please try again.');
            $btn.prop('disabled', false);
        });
    });

    // Unique ID creations for cipes being saved to user meta
    function generateRecipeId(html) {
        // Create hash from timestamp + random number + first 100 chars of recipe
        const timestamp = Date.now().toString(36);
        const random = Math.random().toString(36).substring(2, 8);
        const contentHash = String(html.length) + html.substring(0, 100).replace(/\s+/g, '');
        return 'recipe_' + timestamp + '_' + random + '_' + contentHash.length;
    }

    $(document).on('click', '.view-recipe-btn', function() {
        const $item = $(this).closest('.saved-recipe-item');
        const recipeId = $item.data('recipe-id');
        const $modal = $('#recipe-modal');
        const $modalContent = $('#modal-recipe-content');
        
        // Use the localized data
        if (recipeGeneratorFrontendVars.saved_recipes && 
            recipeGeneratorFrontendVars.saved_recipes[recipeId]) {
            $modalContent.html(recipeGeneratorFrontendVars.saved_recipes[recipeId].html);

            $modal.show();
            setTimeout(() => {
                $modal.addClass('show');
            }, 10);
            
            // Smooth scroll to top of modal
            $modalContent.scrollTop(0);
        } else {
            console.error('Recipe not found:', recipeId);
            $modalContent.html('<p>Error: Recipe could not be loaded</p>');
            $modal.show();
            setTimeout(() => {
                $modal.addClass('show');
            }, 10);
        }
    });

    // Example for future delete functionality
    function updateRecipeCount(newCount) {
        const $countDisplay = $('.recipe-count');
        if ($countDisplay.length) {
            $countDisplay.text('(' + newCount + ')');
        }
    }

    // Close modal
    $(document).on('click', '.close-modal, .recipe-modal', function(e) {
        // Only close if clicking directly on overlay or close button
        if ($(e.target).hasClass('recipe-modal') || $(e.target).hasClass('close-modal')) {
            closeModal();
        }
    });

    function closeModal() {
        const $modal = $('#recipe-modal');
        $modal.removeClass('show');
        setTimeout(() => {
            $modal.hide();
        }, 300);
    }

    // Escape key closes modal
    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            closeModal();
        }
    });

    // Close when clicking outside modal
    $(document).on('click', function(e) {
        if ($(e.target).hasClass('recipe-modal')) {
            closeModal();
        }
    });

    $(document).on('click', '.print-recipe', function() {
        const printContent = $('#modal-recipe-content').html();
        const printWindow = window.open('', '', '');
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${document.title}</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    h2 { color: #222; margin-bottom: 10px; }
                    .recipe-meta { display: flex; justify-content: space-between; margin-bottom: 0px; color: #666; }
                    .recipe-section { margin-bottom: 5px; }
                    ul, ol { padding-left: 20px; }
                    .dietary-tag { 
                        display: inline-block;
                        background: #e0f7fa;
                        padding: 2px 8px;
                        margin-right: 5px;
                        border: 1px solid;
                        border-radius: 12px;
                        font-size: 0.8em;
                    }
                    @page { margin: 1cm; }
                </style>
            </head>
            <body>
                ${printContent}
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            window.close();
                        }, 200);
                    }
                </script>
            </body>
            </html>
        `);
        printWindow.document.close();
    });

    $(document).on('click', '.share-recipe', function() {
        if (navigator.share && /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            // Mobile devices with share support
            const recipeTitle = $('#modal-recipe-content h2').text() || 'Check out this recipe';
            const recipeText = $('#modal-recipe-content').text().substring(0, 200) + '...';
            const shareUrl = window.location.href.split('?')[0];
            
            navigator.share({
                title: recipeTitle,
                text: recipeText,
                url: shareUrl
            }).catch(err => {
                console.log('Share failed:', err);
            });
        } else {
            // Desktop - show share options modal
            $('#share-options-modal').show();
        }
    });

    // Close share modal
    $(document).on('click', '.close-share-modal, .share-modal', function(e) {
        if ($(e.target).hasClass('share-modal') || $(e.target).hasClass('close-share-modal')) {
            $('#share-options-modal').hide();
        }
    });

    // Platform-specific sharing
    $(document).on('click', '.share-btn', function(e) {
        e.preventDefault();
        const platform = $(this).data('platform');
        const recipeTitle = encodeURIComponent($('#modal-recipe-content h2').text() || 'Check out this recipe');
        const recipeUrl = encodeURIComponent(window.location.href.split('?')[0]);
        const recipeText = encodeURIComponent($('#modal-recipe-content').text().substring(0, 300) + '...');
        
        let shareUrl = '';
        
        switch(platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${recipeUrl}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?text=${recipeTitle}&url=${recipeUrl}`;
                break;
            case 'pinterest':
                // Note: Pinterest requires an image - using a placeholder
                const imageUrl = encodeURIComponent('https://via.placeholder.com/300x200?text=Recipe');
                shareUrl = `https://pinterest.com/pin/create/button/?url=${recipeUrl}&media=${imageUrl}&description=${recipeTitle}`;
                break;
            case 'reddit':
                shareUrl = `https://www.reddit.com/submit?url=${recipeUrl}&title=${recipeTitle}`;
                break;
            case 'email':
                shareUrl = `mailto:?subject=${recipeTitle}&body=${recipeText}%0A%0ARead more: ${recipeUrl}`;
                break;
        }
        
        if (platform === 'email') {
            window.location.href = shareUrl;
        } else {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
        
        $('#share-options-modal').hide();
    });
    $(document).on('click', '.delete-recipe', function() {
        if (!confirm('Are you sure you want to delete this recipe?')) {
            return;
        }
        
        const $modal = $('#recipe-modal');
        const recipeId = $('.saved-recipe-item[style*="display: block"]').data('recipe-id') || 
                        $('.saved-recipe-item').first().data('recipe-id');
        
        $.post(recipeGeneratorFrontendVars.ajaxurl, {
            action: 'delete_saved_recipe',
            recipe_id: recipeId,
            _wpnonce: recipeGeneratorFrontendVars.nonce
        }, function(response) {
            if (response.success) {
                // Close modal and remove from list
                $modal.hide();
                $(`.saved-recipe-item[data-recipe-id="${recipeId}"]`).remove();
                
                // Update count
                const $count = $('.recipe-count');
                if ($count.length) {
                    const current = parseInt($count.text().match(/\d+/)[0]) || 0;
                    $count.text(`(${current - 1})`);
                }
                
                // Show feedback if no recipes left
                if ($('.saved-recipe-item').length === 0) {
                    $('.saved-recipes-list').html('<p>You have no saved recipes.</p>');
                }
            } else {
                alert('Error: ' + (response.data || 'Could not delete recipe'));
            }
        });
    });
});