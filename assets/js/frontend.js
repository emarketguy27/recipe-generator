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
});