jQuery(document).ready(function($) {    
    // Copy shortcode functionality
    $('.copy-shortcode').on('click', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const shortcode = $btn.data('clipboard-text');
        const $temp = $('<textarea>');
        
        $('body').append($temp);
        $temp.val(shortcode).select();
        
        try {
            document.execCommand('copy');
            $btn.text('Copied!');
            
            // Revert button text after 2 seconds
            setTimeout(() => {
                $btn.text('Copy');
            }, 2000);
            
        } catch (err) {
            console.error('Failed to copy text: ', err);
            $btn.text('Error');
        }
        
        $temp.remove();
    });

    // Handle prompt reset
    $('#reset-prompt').on('click', function(e) {
        e.preventDefault();
        
        if (confirm(recipeGeneratorVars.confirmResetPrompt)) {
            $.post(recipeGeneratorVars.ajaxurl, {
                action: 'recipe_generator_reset_prompt',
                _wpnonce: recipeGeneratorVars.nonce
            }, function(response) {
                if (response.success) {
                    $('textarea[name="recipe_generator_prompt"]').val(response.data.default_prompt);
                    alert(recipeGeneratorVars.promptResetSuccess);
                } else {
                    alert(response.data || recipeGeneratorVars.errorOccurred);
                }
            }).fail(function() {
                alert(recipeGeneratorVars.errorOccurred);
            });
        }
    });

    // Add dietary option
    $('#add-dietary-option').on('click', function(e) {
        e.preventDefault();
        var newOption = $('#new_dietary_option').val().trim();
        
        if (newOption) {
            $.post(recipeGeneratorVars.ajaxurl, {
                action: 'recipe_generator_add_dietary_option',
                option: newOption,
                _wpnonce: recipeGeneratorVars.nonce
            }, function(response) {
                if (response.success) {
                    $('#current-dietary-options').append(
                        '<li>' + response.data.label + 
                        ' <a href="#" data-key="' + response.data.key + '" class="remove-dietary-option">(' + recipeGeneratorVars.remove + ')</a></li>'
                    );
                    $('#new_dietary_option').val('');
                } else {
                    alert(response.data || recipeGeneratorVars.errorOccurred);
                }
            }).fail(function() {
                alert(recipeGeneratorVars.errorOccurred);
            });
        }
    });

    // Remove dietary option
    $(document).on('click', '.remove-dietary-option', function(e) {
        e.preventDefault();
        var $li = $(this).closest('li');
        var key = $(this).data('key');
        
        if (confirm(recipeGeneratorVars.confirmRemoveOption)) {
            $.post(recipeGeneratorVars.ajaxurl, {
                action: 'recipe_generator_remove_dietary_option',
                key: key,
                _wpnonce: recipeGeneratorVars.nonce
            }, function(response) {
                if (response.success) {
                    $li.remove();
                } else {
                    alert(response.data || recipeGeneratorVars.errorOccurred);
                }
            }).fail(function() {
                alert(recipeGeneratorVars.errorOccurred);
            });
        }
    });

    // Reset dietary options
    $('#reset-dietary-options').on('click', function(e) {
        e.preventDefault();
        
        if (confirm(recipeGeneratorVars.confirmResetOptions)) {
            $.post(recipeGeneratorVars.ajaxurl, {
                action: 'recipe_generator_reset_dietary_options',
                _wpnonce: recipeGeneratorVars.nonce
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || recipeGeneratorVars.errorOccurred);
                }
            }).fail(function() {
                alert(recipeGeneratorVars.errorOccurred);
            });
        }
    });

    // Test API Connection
    $('#test-api-connection').on('click', function() {
        var $button = $(this);
        var $result = $('#test-connection-result');
        
        $button.prop('disabled', true);
        $result.html('<span class="spinner is-active" style="float:none; margin:0;"></span> Testing...');
        
        $.post(ajaxurl, {
            action: 'recipe_generator_test_connection',
            nonce: $button.data('nonce')
        }, function(response) {
            if (response.success) {
                $result.html('<span style="color:#46b450;">✓ ' + response.data + '</span>');
            } else {
                // Show full error message
                $result.html('<span style="color:#dc3232;">✗ ' + response.data + '</span>');
                console.error('API Test Failed:', response);
            }
        }).fail(function(xhr) {
            $result.html('<span style="color:#dc3232;">✗ ' + recipeGeneratorVars.errorOccurred + '</span>');
            console.error('AJAX Error:', xhr.responseText);
        }).always(function() {
            $button.prop('disabled', false);
        });
    });

    // Test prompt
    $('#test-prompt').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $results = $('#test-results');
        var $apiResponse = $('#api-response');
        var $loadingBar = $('.loading-bar');
        
        $button.prop('disabled', true).text(recipeGeneratorVars.testing);
        $results.show();
        $apiResponse.html('<p class="loading">' + recipeGeneratorVars.testing + '</p>');
        $loadingBar.addClass('active');
        
        var data = {
            action: 'recipe_generator_test_prompt',
            servings: $('#test_servings').val(),
            include: $('#test_include').val(),
            exclude: $('#test_exclude').val(),
            dietary: $('input[name="test_dietary[]"]:checked').map(function() {
                return this.value;
            }).get(),
            _wpnonce: recipeGeneratorVars.nonce
        };
        
        $.post(recipeGeneratorVars.ajaxurl, data, function(response) {
            if (response.success) {
                $('#generated-prompt').text(response.data.prompt);
                $apiResponse.html(response.data.response);
            } else {
                $apiResponse.html('<div class="error"><p>' + (response.data || recipeGeneratorVars.errorOccurred) + '</p></div>');
            }
        }).fail(function() {
            $apiResponse.html('<div class="error"><p>' + recipeGeneratorVars.errorOccurred + '</p></div>');
        }).always(function() {
            $button.prop('disabled', false).text(recipeGeneratorVars.testPrompt);
            $loadingBar.removeClass('active');
        });
    });

    // View recipe modal
    $(document).on('click', '.view-recipe', function(e) {
        e.preventDefault();
        
        try {
            var recipeHtml = $(this).data('recipe-html');
            if (typeof recipeHtml === 'string') {
                recipeHtml = JSON.parse(recipeHtml);
            }
            
            var $modal = $('<div>').addClass('recipe-modal').css({
                'position': 'fixed',
                'top': '0',
                'left': '0',
                'width': '100%',
                'height': '100%',
                'background': 'rgba(0,0,0,0.8)',
                'z-index': '99999',
                'overflow-y': 'auto'
            });
            
            var $content = $('<div>').addClass('modal-content').css({
                'background': '#fff',
                'margin': '2rem auto',
                'padding': '20px',
                'max-width': '800px',
                'position': 'relative'
            });
            
            $content.append('<span class="close-modal" style="position:absolute;top:10px;right:10px;cursor:pointer;font-size:20px;">&times;</span>');
            $content.append('<div class="recipe-content">' + recipeHtml + '</div>');
            $modal.append($content);
            
            $('body').append($modal);
            
            $modal.on('click', '.close-modal', function() {
                $modal.remove();
            });
            
            $(document).keyup(function(e) {
                if (e.key === "Escape") {
                    $modal.remove();
                }
            });
        } catch (error) {
            console.error('Error displaying recipe:', error);
            alert(recipeGeneratorVars.errorOccurred);
        }
    });

    // Handle bulk action form submission
    $('#doaction, #doaction2').on('click', function(e) {
        if ($('select[name="action"]').val() === 'create_post') {
            e.preventDefault();
            
            // Collect all selected recipes with their user IDs
            var recipes = [];
            $('input[name="recipe[]"]:checked').each(function() {
                recipes.push({
                    id: $(this).val(),
                    user_id: $(this).data('user-id')
                });
            });
            
            if (recipes.length === 0) {
                alert('No recipes selected');
                return;
            }
            
            // Show processing indicator
            var $button = $(this);
            $button.prop('disabled', true).text('Processing...');
            
            // Send via AJAX
            $.post(ajaxurl, {
                action: 'recipe_generator_bulk_create_posts',
                recipes: recipes,
                _wpnonce: recipeGeneratorVars.nonce
            }, function(response) {
                if (response.success) {
                    // Update status indicators for created posts
                    if (response.data.created_posts && response.data.created_posts.length) {
                        response.data.created_posts.forEach(function(post) {
                            var $row = $('input[value="' + post.recipe_id + '"]').closest('tr');
                            $row.find('.column-post_status').html(
                                '<span class="recipe-status">' +
                                '<span class="dashicons dashicons-edit"></span> ' +
                                'Draft <a href="' + post.edit_link + '" target="_blank">(Edit)</a>' +
                                '</span>'
                            );
                        });
                    }
                    
                    // Show success message
                    $('#wpbody-content').before(
                        '<div class="notice notice-success is-dismissible"><p>' +
                        response.data.message + ' ' +
                        '<a href="' + adminurl + 'edit.php?post_status=draft&post_type=post">View drafts</a>' +
                        '</p></div>'
                    );
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.data || 'Operation failed'));
                    $button.prop('disabled', false).text('Apply');
                }
            }).fail(function() {
                alert('Connection error. Please try again.');
                $button.prop('disabled', false).text('Apply');
            });
        }
    });
});