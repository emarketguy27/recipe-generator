jQuery(document).ready(function($) {
    $('#recipe-generator-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $results = $('#recipe-results');
        var $loading = $('.rg-loading');
        
        // Show loading state
        $form.hide();
        $loading.show();
        $results.hide();
        
        // Prepare data
        var formData = {
            action: 'recipe_generator_generate_recipe',
            _wpnonce: $form.find('input[name="recipe_nonce"]').val(),
            servings: $form.find('#rg-servings').val(),
            include: $form.find('#rg-include').val(),
            exclude: $form.find('#rg-exclude').val(),
            dietary: $form.find('input[name="dietary[]"]:checked').map(function() {
                return this.value;
            }).get()
        };
        
        // Make the request
        $.post(recipeGeneratorVars.ajaxurl, formData, function(response) {
            if (response.success) {
                $results.html(response.data.recipe).fadeIn();
            } else {
                $results.html(
                    '<div class="error">' + 
                    (response.data || recipeGeneratorVars.errorOccurred) + 
                    '</div>'
                ).fadeIn();
            }
        }).fail(function() {
            $results.html(
                '<div class="error">' + recipeGeneratorVars.errorOccurred + '</div>'
            ).fadeIn();
        }).always(function() {
            $loading.hide();
            $form.show();
        });
    });
});