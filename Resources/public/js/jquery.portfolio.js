(function($) {
    if (!$.bigfoot) {
        $.bigfoot = new Object();
    };

    $.bigfoot.portfolio = function(el, options) {
        var base = this;

        base.$el = $(el);
        base.el  = el;

        base.$el.data("bigfoot.portfolio", base);

        base.init = function() {
            base.options = $.extend({},$.bigfoot.portfolio.defaultOptions, options, base.$el.data());

            $.event.props.push("dataTransfer");

            base.listener();
        };

        base.listener = function() {
            $(base.options['portfolioPopinOpenClass']).bind('click', base.openPortfolio);
            $('body').delegate(base.options['portfolioDragContainerId'], 'dragenter dragexit dragover', base.preventDefault);
            $('body').delegate(base.options['portfolioDragContainerId'], 'drop', base.dragMedia);
            $('body').delegate(base.options['portfolioToggleEditClass'], 'click', base.editFormMedia);
            $('body').delegate(base.options['portfolioAddTagClass'], 'click', base.addTagMedia);
            $('body').delegate(base.options['portfolioEditFormClass'], 'submit', base.submitFormMedia);
            $('body').delegate(base.options['portfolioToggleUseClass'], 'click', base.useMedia);
            $('body').delegate(base.options['portfolioMediaDeleteClass'], 'click', base.deleteMedia);
            $('body').delegate(base.options['portfolioValidateClass'], 'click', base.validate);
            $('body').delegate(base.options['portfolioSearchFormClass'], 'submit', base.submitSearchForm);
            $('body').delegate(base.options['portfolioSearchFormTableClass'], 'change', base.changeTableSelection);
        }

        base.preventDefault = function(e) {
            e.stopPropagation();
            e.preventDefault();
        }

        base.openPortfolio = function(e) {
            base.preventDefault(e);

            var popinOpenButton = $(this);
            var popinHref       = popinOpenButton.attr('href');

            if (popinHref.indexOf('#') == 0) {
                $(popinHref).modal('open');
            }
            else {
                $.get(popinHref, function(data) {
                    $(base.options['portfolioPopinClass']).remove();
                    var $data = $(data);

                    // add data attribute field id on modal
                    $data.attr('data-field-id', popinOpenButton.parent('.field-media').children('input').attr('id'));

                    // set class on the image to update
                    $('.media-to-update').removeClass('media-to-update');
                    popinOpenButton.children('img').addClass('media-to-update');

                    // open modal
                    $data.modal();

                    $("table tbody", $('#media')).sortable({
                        axis: 'y',
                        handle: 'img'
                    }).disableSelection();

                    initSelects();
                });
            }
        }

        base.dragMedia = function(e) {
            base.preventDefault(e);

            var files = e.dataTransfer.files;

            $.each(files, function(index, file) {
                var fileReader = new FileReader();

                fileReader.onload = (function(file) {
                    return function(e) {
                        var image     = this.result;
                        var mediaData = {name: file.name, value: image};

                        var data = "data:image/png;base64,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";
                        if (strpos(file.type, 'image') !== false) {
                            data = image;
                        }

                        $.post(defaultPortfolioRoute + 'upload', mediaData, function(json) {
                            var jsonObject = JSON.parse(json);
                            $("#droppedFiles").prepend(jsonObject.html);
                        });
                    };
                })(files[index]);

                fileReader.readAsDataURL(file);
            });

            $("#droppedFiles").fadeIn();
        }

        base.editFormMedia = function(e) {
            base.preventDefault(e);

            var linkEdit  = $(this);
            var mediaItem = linkEdit.parent('td').parent('tr');

            $(base.options['portfolioToggleEditClass'], mediaItem).toggle();

            if (linkEdit.hasClass('on')) {
                $.get(linkEdit.attr('href'), function (json) {
                    json = $.parseJSON(json);
                    mediaItem.after(json.html);
                    initSelects();
                });
            } else if (linkEdit.hasClass('off')) {
                $(base.options['portfolioEditFormClass'] + '-' + mediaItem.attr('data-media-id')).remove();
            }

            return false;
        }

        base.addTagMedia = function(e) {
            base.preventDefault(e);

            var tagForm = $(this);

            $.post(tagForm.attr('href'), {'tag': $('#' + tagForm.data('field-id')).val()}, function (json) {
                var jsonObject = JSON.parse(json);

                $('#' + tagForm.data('field-id')).val('');
                $('#PortfolioMedia_portfolio_tags').append(jsonObject.html);
            });

            return false;
        }

        base.submitFormMedia = function(e) {
            base.preventDefault(e);

            var editForm = $(this);

            $.post(editForm.attr('action'), editForm.serialize(), function (json) {
                var jsonObject     = JSON.parse(json);
                var sMediaItemList = base.options['portfolioMediaItemClass'] + '[data-media-id="' + jsonObject.id + '"]';
                var mediaItemList  = $(sMediaItemList);

                $(base.options['portfolioEditFormClass'] + '-' + jsonObject.id).remove();

                mediaItemList.replaceWith(jsonObject.html);
            });

            return false;
        }

        base.submitSearchForm = function(e) {
            base.preventDefault(e);

            var searchForm = $(this);
            var sMediaIds = $(base.options['portfolioPopinClass']).data('selected');

            $.post(searchForm.attr('action') + '?ids=' + sMediaIds, searchForm.serialize(), function (json) {
                var jsonObject     = JSON.parse(json);
                var portfolioList   = $(base.options['portfolioListClass']);

                portfolioList.replaceWith(jsonObject.html);
            });

            return false;
        }

        base.useMedia = function(e) {
            base.preventDefault(e);

            var popin           = $(base.options['portfolioPopinClass']);
            var selectedIds     = popin.data('selected') && strpos(';', popin.data('selected')) ? popin.data('selected').split(';') : new Array();
            var linkUse         = $(this);
            var mediaItem       = linkUse.parent('td').parent('tr');
            var mediaItemId     = mediaItem.data('media-id');
            var sMediaItemList  = base.options['portfolioMediaItemClass'] +'[data-media-id="' + mediaItem.attr('data-media-id') + '"]';
            var mediaItemList   = $(sMediaItemList);

            $(base.options['portfolioToggleUseClass'], mediaItemList).toggle();

            if (linkUse.hasClass('on')) {
                $(sMediaItemList, $('#media')).remove();
                mediaItemList.removeClass('used').addClass('unused');
                for (var i in selectedIds) {
                    if (selectedIds[i] == mediaItemId) {
                        selectedIds.splice(i, 1);
                        break;
                    }
                }
            }
            else {
                $('table tbody', $('#media')).append(mediaItem.clone());
                mediaItemList.removeClass('unused').addClass('used');
                selectedIds.push(mediaItemId);
            }

            popin.data('selected', selectedIds.join(';'));
        }

        base.deleteMedia = function(e) {
            base.preventDefault(e);

            var linkDelete = $(this);

            $.post(linkDelete.attr('href'), function (json) {
                var jsonObject = JSON.parse(json);
                $(base.options['portfolioMediaItemClass'] + '[data-media-id="' + jsonObject.id + '"]').remove();
            });

            return false;
        }

        base.validate = function(e) {
            base.preventDefault(e);

            var field     = $('#' + $(base.options['portfolioPopinClass']).data('field-id'));
            var linkPopin = field.parent('.field-media').children('a');

            var mediaIds = [];
            $.each($('table tbody tr', $('#media')), function(index, mediaItem) {
                mediaIds.push($(mediaItem).attr('data-media-id'));
            });

            var sMediaIds = mediaIds.join(';');

            field.attr('value', sMediaIds);

            if (linkPopin.children('img').length == 0) {
                var newHref = linkPopin.data('base-href');
                if (sMediaIds.length) {
                    newHref += '/' + sMediaIds;
                }
                linkPopin.attr('href', newHref);
            }
            else {
                var urlArray = linkPopin.attr('href').split('/');
                urlArray[urlArray.length -1] = sMediaIds;
                linkPopin.attr('href', urlArray.join('/'));
            }

            $(base.options['portfolioPopinClass']).modal('hide');

            return false;
        }

        base.changeTableSelection = function(e) {
            base.preventDefault(e);

            var tableField = $(base.options['portfolioSearchFormTableClass']);
            var columnField = $(base.options['portfolioSearchFormColumnClass']);

            columnField.children('option:not(:first)').remove();
            if (tableField.val()) {
                $.get(defaultPortfolioRoute + 'list-fields?table=' + tableField.val(), function(json) {
                    var jsonObject = JSON.parse(json);
                    $.each(jsonObject, function (key, item) {
                        columnField.append($('<option>', {
                            value: key,
                            text : item
                        }));
                    });
                });
                columnField.removeAttr('disabled');
            } else {
                columnField.attr('disabled', 'disabled');
            }

            return false;
        }

        base.init();
    };

    $.bigfoot.portfolio.defaultOptions = {
        'portfolioDragContainerId': '#dragContainer',
        'portfolioPopinClass': '.portfolio-popin',
        'portfolioPopinOpenClass': '.portfolio-popin-open',
        'portfolioValidateClass': '.portfolio-validate',
        'portfolioMediaItemClass': '.portfolio-media-item',
        'portfolioMediaDeleteClass': '.portfolio-media-delete',
        'portfolioEditFormClass': '.portfolio-edit-form',
        'portfolioToggleEditClass': '.portfolio-toggle-edit',
        'portfolioToggleUseClass': '.portfolio-toggle-use',
        'portfolioAddTagClass': '.portfolio-add-tag',
        'portfolioSearchFormClass': '.portfolio-search-form',
        'portfolioSearchFormTableClass': '.portfolio-search-form-table',
        'portfolioSearchFormColumnClass': '.portfolio-search-form-column',
        'portfolioListClass': '.portfolio-list',
        'portfolioLimit': 1,
        'portfolioType': ["image"]
    };

    $.fn.bigfoot_portfolio = function(options) {
        return this.each(function() {
            (new $.bigfoot.portfolio(this, options));
        });
    };

    $.fn.getbigfoot_portfolio = function() {
        this.data("bigfoot.portfolio");
    };

})(jQuery);
