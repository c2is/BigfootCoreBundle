/* Navigation */
$(document).ready(function () {

    $(window).resize(function () {
        if ($(window).width() > 768) {
            $(".sidebar #nav").slideDown(350);
        } else {
            $(".sidebar #nav").slideUp(350);
        }
    });


    $("#nav > li > a").on('click', function (e) {
        if ($(this).parent().hasClass("has_sub")) {
            e.preventDefault();
        }

        if (!$(this).hasClass("subdrop")) {
            // hide any open menus and remove all other classes
            $("#nav li ul").slideUp(350);
            $("#nav li a").removeClass("subdrop");

            // open our new menu and add the open class
            $(this).next("ul").slideDown(350);
            $(this).addClass("subdrop");
        } else if ($(this).hasClass("subdrop")) {
            $(this).removeClass("subdrop");
            $(this).next("ul").slideUp(350);
        }

    });


    $("#nav > li > ul > li > a").on('click', function (e) {
        if ($(this).parent().hasClass("has_sub")) {
            e.preventDefault();
        }

        if (!$(this).hasClass("subdrop")) {
            // hide any open menus and remove all other classes
            $("#nav li ul li ul").slideUp(350);
            $("#nav > li > ul > li > a").removeClass("subdrop");

            // open our new menu and add the open class
            $(this).next("ul").slideDown(350);
            $(this).addClass("subdrop");
        } else if ($(this).hasClass("subdrop")) {
            $(this).removeClass("subdrop");
            $(this).next("ul").slideUp(350);
        }

    });
});

$(document).ready(function () {
    $(".sidebar-dropdown a").on('click', function (e) {
        e.preventDefault();

        if (!$(this).hasClass("open")) {
            // hide any open menus and remove all other classes
            $(".sidebar #nav").slideUp(350);
            $(".sidebar-dropdown a").removeClass("open");

            // open our new menu and add the open class
            $(".sidebar #nav").slideDown(350);
            $(this).addClass("open");
        } else if ($(this).hasClass("open")) {
            $(this).removeClass("open");
            $(".sidebar #nav").slideUp(350);
        }
    });

    $("#nav .has_sub ul li ul li.active")
        .parent().parent().parent().parent().children('a').click();

    $("#nav .has_sub li.active")
        .parent().parent().children('a').click();
});

/* Form collections */
$(document).ready(function () {
    setupSortableCollectionItem();

    $('body').on('click', 'a.deleteCollectionItem', function (event) {
        event.preventDefault();
        $(this).closest('.form-group').remove();
    });
});

// Support for AJAX loaded modal window.
// Focuses on first input textbox after it loads the window.
$(document).ready(function () {
    $('[data-toggle="modal"]').click(function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (url.indexOf('#') == 0) {
            $(url).modal('open');
        } else {
            $.get(url, function (data) {
                $('<div class="modal hide fade">' + data + '</div>').modal();
            }).success(function () {
                    $('input:text:visible:first').focus();
                });
        }
    });

});

/* Portfolio */
$(function() {
    $('.field-media').each(function() {
        $.bigfoot.portfolio(this);
    });

    initSelects();
})

/* Translatable fields */
$(function() {
    var $translatableFields = $('.translatable-fields');
    if ($translatableFields.length) {
        setupTranslatableFields($translatableFields);

        $('#locales-container').html(Twig.render(localeTabs, {locales: locales, currentLocale: currentLocale, basePath: basePath}));

        var $localeTab = $('.locale-tabs');
        $localeTab.on('click', 'a', function(event) {
            event.stopPropagation();

            if (!$(this).hasClass('active')) {
                var newLocale = $(this).data('locale');
                $('input[data-locale="'+newLocale+'"], textarea[data-locale="'+newLocale+'"]').closest('div.input-group').show();
                $('input[data-locale="'+currentLocale+'"], textarea[data-locale="'+currentLocale+'"]').closest('div.input-group').hide();

                $('a', $localeTab).removeClass('active');
                $(this).addClass('active');
                currentLocale = newLocale;
            }

            return false;
        });
    }
});

/* Sortable */
$(function() {
    $('body').on('click', 'a.addCollectionItem', function (event) {
        event.preventDefault();
        addCollectionItem($(this).data('collection-id'), $(this).data('prototype-name'));
    });
});

/* Tags fields */
window.tags = [];
function initSelects() {
    $("select").width($("select").width()).select2();

    var tags = window.tags;

    if (tags.length == 0) {
        $.ajax({
            url: tagsPath,
            async: false,
            success: function(json) {
                tags = window.tags = $.parseJSON(json);
            }
        });
    }

    var arrayTags = new Array();
    if (tags != undefined && $.isArray(tags) && tags.length > 0) {
        arrayTags = tags;
    }
    var $tagsSelect = $('input.bigfoot_tags_field');
    $tagsSelect.width('100%').select2({tags: arrayTags});
    $('.select2-container').width('100%');
}

/* Functions */
function strpos (haystack, needle, offset) {
    var i = (haystack + '').indexOf(needle, (offset || 0));

    return i === -1 ? false : i;
}

function setupTranslatableFields($translatableFields) {
    $translatableFields.hide();
    // Getting all translated fields to set their parent's data attributes (default locale fields aren't initialized by the translationsubscriber)
    $('input[type="text"], textarea', $translatableFields).each(function() {
        var elementId = $(this).attr('id')
        var parentElementId = elementId.substr(0, elementId.lastIndexOf('-')).replace('_translation', '');

        var $parentElement = $('#'+parentElementId);

        $parentElement
            .data('locale', currentLocale)
            .attr('data-locale', currentLocale);

        $(this).appendTo($parentElement.parent());
    });

    var $wrapper = $('<div class="input-group"></div>');
    var $toWrap = $('input[data-locale], textarea[data-locale]');
    $toWrap.wrap($wrapper);
    $toWrap.each(function() {
        $(this).after($('<span class="input-group-addon"><img src="/bundles/bigfootcore/img/flags/'+$(this).data('locale')+'.gif" /></span>'));
        if ($(this).data('locale') != currentLocale) {
            $(this).closest('div.input-group').hide();
        }
    });
}

function setupSortableCollectionItem() {
    var $sortableFields = $('input.sortable-field');
    if ($sortableFields.length > 0) {
        $sortableFields.closest('div.sortable-collection-item').parent().each(function() {
            $(this).sortable({
                connectWith: '.'+$(this).attr('class'),
                handle: '.accordion-heading',
                update: function () {
                    var inputs = $('input.sortable-field');
                    var nbElems = inputs.length;
                    $('input.sortable-field').each(function(idx) {
                        $(this).val(idx);
                    });
                }
            });
        });
    }
}

function addCollectionItem(id, name) {
    var collectionHolder = $(id);
    var prototypeName = '__name__';

    if (name != undefined) {
        prototypeName = name;
    }

    var prototype = collectionHolder.attr('data-prototype');
    var reg = new RegExp(prototypeName, 'g');
    var form = prototype.replace(reg, collectionHolder.children().length);
    var $form = $(form);
    $form.find('div.accordion-body').addClass('in');

    collectionHolder.append($form);

    setupSortableCollectionItem();

    if (CKEDITOR != undefined) {
        var $textAreas = $form.find('textarea.ckeditor');
        if ($textAreas.length) {
            $textAreas.each(function() {
                CKEDITOR.replace($(this).attr('id'));
            });
        }
    }
}
