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
});