jQuery(document).ready(function () {
    var $container = $('#media-fields-list');

    var index = $container.find('fieldset').length;

    $('#add-another-collection-widget').click(function(e) {
        addPricing($container);

        e.preventDefault(); 
        return false;
    });

    if (index !== 0) {
        $container.children('li').each(function(loop_index) {
            $(this).children('legend').text('Element n°' + (loop_index+1))
            addDeleteLink($(this));

        });
    }

    function addPricing(container) {
        var number_field = $container.find('fieldset').length;

        var template = container.attr('data-prototype')
            .replace(/__name__label__/g, 'Element n°' + (number_field+1))
            .replace(/__name__/g,        number_field)
        ;

        var $prototype = $(template);

        addDeleteLink($prototype);

        $container.append($prototype);
        $('#trick_medias_'+number_field+'_type_0').change(function() {
            toggleMediaForm(number_field, 'image');
        });
        $('#trick_medias_'+number_field+'_type_1').click(function() {
            toggleMediaForm(number_field, 'video');
        });

    }

    function addDeleteLink($prototype) {
        var $deleteLink = $('<a href="#" class="btn btn-danger w-50 ms-auto my-2"><i class="fas fa-trash-alt me-2"></i>Supprimer ce média</a>');

        $prototype.append($deleteLink);

        $deleteLink.click(function(e) {
            $prototype.remove();
            $container.children('fieldset').each(function(delete_loop_index) {
                $(this).children('legend').text('Element n°' + (delete_loop_index+1))
            });
            e.preventDefault(); 
            return false;
        });
    }
});


let number_field = $('#media-fields-list').find('fieldset').length;

for (let i = 0; i < number_field; i++) {
    $('#trick_medias_'+i+'_type_0').click(function() {
        toggleMediaForm(i, 'image');
    });
    $('#trick_medias_'+i+'_type_1').click(function() {
        toggleMediaForm(i, 'video');
    });

    if ($('#trick_medias_'+i+'_type_0').is(':checked')) {
        toggleMediaForm(i, 'image');
    } else if ($('#trick_medias_'+i+'_type_1').is(':checked')) {
        toggleMediaForm(i, 'video');
    }
}

function toggleMediaForm(id, type) {
    $('#trick_medias_'+id+' .field-image-video').show();
    if (type === 'image') {
        $('#trick_medias_'+id+' .field-image').show();
        $('#trick_medias_'+id+' .field-video').hide();
    }
    if (type === 'video') {
        $('#trick_medias_'+id+' .field-video').show();
        $('#trick_medias_'+id+' .field-image').hide();
    }
}
