jQuery(document).ready(function($) {
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
    // $('#test-api-connection').on('click', function() {
    //     var $button = $(this);
    //     var $result = $('#test-connection-result');
        
    //     $button.prop('disabled', true);
    //     $result.html('<span class="spinner is-active" style="float:none; margin:0;"></span>');
        
    //     $.post(ajaxurl, {
    //         action: 'recipe_generator_test_connection',
    //         nonce: $button.data('nonce')
    //     }, function(response) {
    //         if (response.success) {
    //             $result.html('<span style="color:#46b450;">✓ ' + response.data + '</span>');
    //         } else {
    //             $result.html('<span style="color:#dc3232;">✗ ' + response.data + '</span>');
    //         }
    //     }).fail(function() {
    //         $result.html('<span style="color:#dc3232;">✗ ' + recipeGeneratorVars.errorOccurred + '</span>');
    //     }).always(function() {
    //         $button.prop('disabled', false);
    //     });
    // });
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
});