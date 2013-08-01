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

$(document).ready(function () {
    AddCollectionItemEvent();
    DeleteCollectionItemEvent();
    sortCollection();
});

function AddCollectionItemEvent() {
    $('a.addCollectionItem').on('click', function (event) {
        event.preventDefault();
        addCollectionItem($(this).data('collection-id'));
    });
}

function DeleteCollectionItemEvent() {
    $('a.deleteCollectionItem').on('click', function (event) {
        event.preventDefault();
        $(this).parent('div').remove();
    });
}

function addCollectionItem(id) {

    var collectionHolder = $(id);

    var prototype = collectionHolder.attr('data-prototype');
    form = prototype.replace(/__name__/g, collectionHolder.children().length);

    collectionHolder.append(form);

    DeleteCollectionItemEvent();

    $(id).find('a.addCollectionItem').on('click', function (event) {
        event.preventDefault();
        addCollectionItem($(this).data('collection-id'));
    });

    setupTranslatableFields($('.translatable-fields'))
}

$(document).ready(function () {
    $('form').on('change', '.choice-load-embeded-form', function () {
        var form        = $(this.form);
        var select      = $(this);
        var selectId    = $(this).attr('id');
        var pattern     = select.data('url-pattern');
        var targetValue = select.data('target-value');

        $.post(pattern, form.serialize(), function (html) {
            for (var i = 0; i < 10; i++) {
                var regexp = new RegExp("_" + i + "_", 'g');
                html = html.replace(regexp, '_' + (100 + i) + '_');
                var regexp = new RegExp("\\[" + i + "\\]", 'g');
                html = html.replace(regexp, '[' + (100 + i) + ']');
            }

            $('.padd', form).html(html);
        });
    })
});

$(document).ready(function () {
    // Support for AJAX loaded modal window.
    // Focuses on first input textbox after it loads the window.
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

$(document).ready(function() {
    $("input[id$='treeview']").each(function() {
        var base  = $(this);
        var table = $('<table id="treeview"></table>');
        var form  = base.closest('form');

        base.after(table);
        table.tree({
            dragAndDrop: true,
            autoOpen   : 0,
            data       : JSON.parse(base.val())
        });

        form.on('submit', function () {
            var data = table.tree('toJson');
            base.val(data);
        });
    });
});

$(function() {
    $('.field-media').each(function() {
        $.bigfoot.portfolio(this);
    });

    initSelects();
})

$(function() {
    var $translatedFields = $('.translatable-fields');
    if ($translatedFields.length) {
        setupTranslatableFields($translatedFields);

        $translatedFields.parent().parent().prepend(Twig.render(localeTabs, {locales: locales, currentLocale: currentLocale, basePath: basePath}));

        var $localeTab = $('.locale-tabs');
        $localeTab.on('click', 'a', function(event) {
            event.stopPropagation();

            if (!$(this).hasClass('active')) {
                var newLocale = $(this).data('locale');
                $('input[data-locale="'+newLocale+'"], textarea[data-locale="'+newLocale+'"]').show();
                $('input[data-locale="'+currentLocale+'"], textarea[data-locale="'+currentLocale+'"]').hide();

                $('a', $localeTab).removeClass('active');
                $(this).addClass('active');
                currentLocale = newLocale;
            }

            return false;
        });
    }
})

function strpos (haystack, needle, offset) {
    var i = (haystack + '').indexOf(needle, (offset || 0));

    return i === -1 ? false : i;
}

function setupTranslatableFields($translatedFields) {
    $translatedFields.hide();
    // Getting all translated fields to set their parent's data attributes (default locale fields aren't initialized by the translationsubscriber)
    $('input[type="text"], textarea', $translatedFields).each(function() {
        var elementId = $(this).attr('id')
        var parentElementId = elementId.substr(0, elementId.lastIndexOf('-')).replace('_translation', '');

        var $parentElement = $('#'+parentElementId);

        $parentElement
            .data('locale', currentLocale)
            .attr('data-locale', currentLocale);

        $(this).appendTo($parentElement.parent()).hide();
    });
}

function sortCollection() {

    if ($('input.sortable-field').length > 0) {

        $('input.sortable-field').parent('div').parent('div').parent('div').each(function() {
            $(this).sortable({
                update: function () {
                    var inputs = $('input.sortable-field');
                    var nbElems = inputs.length;
                    $('input.sortable-field').each(function(idx) {
                        $(this).val(idx);
                    });
                }
            });
        });

        $('input.sortable-field').each(function() {
            $(this).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" );
        });
    }

}
