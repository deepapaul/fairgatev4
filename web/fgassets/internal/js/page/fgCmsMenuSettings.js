
/*function to initialize on page load */
$(document).ready(function () {
    FgCmsMenuSettings.initPageFunctions();

    /* function to show data in different languages on switching language */
    $(document).off('click', 'button[data-elem-function=switch_lang]');
    $(document).on('click', 'button[data-elem-function=switch_lang]', function () {
        selectedLang = $(this).attr('data-selected-lang');
        $('button[data-elem-function=switch_lang]').removeClass('active');
        $(this).addClass('active');
        FgUtility.showTranslation(selectedLang);
    });
});

$(document).on('click','.fg-cms-page-url-nav',function(){
    var navID = $(this).parent().attr('nav-url-id');
    sessionStorage.setItem("navId", navID);
    window.open(pageListPath, '_blank');
});
$(document).on('click','.fg-dev-remove',function(){
    var navigationId = $(this).parent().attr('nav-url-id');
    $('span[nav-url-id='+navigationId+']').parents('.fg-dev-pagetitle').html('');
    $('.fg-dev-unassign-'+navigationId).prop('checked', true);
    FgDirtyFields.updateFormState();
    FgDirtyFields.enableSaveDiscardButtons();
});

FgCmsMenuSettings = {
    
    /* function  called to initialize navigation menu settings page */
    initPageFunctions: function () {
        FgCmsMenuSettings.initPageTitleBar();
        FgCmsMenuSettings.renderTemplate();
    },
    /* function to init the page title bar*/
    initPageTitleBar: function () {
        $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            languageSwitch: true
        });
    },
    /* function to render navigation menu list*/
    renderTemplate: function () {
        $('#cms_navigation_points_list').rowList({
            template: '#navPointsListingTemplate',
            jsondataUrl: navigationDataUrl,
            submit: ['#save_changes', 'cms_navigation_settings'],
            reset: '#reset_changes',
            useDirtyFields: true,
            fieldSort: false,
            dirtyFieldsConfig: {
                enableDiscardChanges: false,
                enableDragDrop: false,
                enableUpdateSortOrder: false,
                setInitialHtml: false,
                sortOrderSelector: false
            },
            loadTemplate: [{
                    btn: '#addNewMenu',
                    template: '#navigationPointTemplate'
                }],
            addData: ['#addNewMenu', {
                    isActive: true,
                    isNew: 1,
                    isPublic: true,
                    subMenuCount: 0,
                    title: '',
                    parentId: 1
                }],
            rowCallback: function () {
                FgUtility.showTranslation(selectedLang);
                FgCmsMenuSettings.updateSortOrder();
            },
            validate: true,
            postURL: saveDataUrl,
            onSuccessCallback: function() {
                FgCmsMenuSettings.handleDelete();
            },
            success: function () {
                alert('Posting Data');
            },
            load: function () {
            },
            initCallback: function () {
                FgDirtyFields.init('cms_navigation_settings', {enableDiscardChanges: true, enableDragDrop: false, enableUpdateSortOrder: false,
                    setInitialHtml: false,
                    sortOrderSelector: false,
                    discardChangesCallback: function () {
                        customFunctions.buildTemplate();
                        FgUtility.showTranslation(selectedLang);
                    }});
                FgCmsMenuSettings.initNestable();
                FgUtility.showTranslation(selectedLang);
                FgLanguageSwitch.checkMissingTranslation(defaultLang);
                FgCmsMenuSettings.handleDelete();
                FgCmsMenuSettings.updateSortOrder();
                FgCmsMenuSettings.initTooltip()
            }
        });
    },
    /* FAIR-2405 System testing issues in CMS -I issue 2 */
    handleDelete: function(){
        $('form').off('click', 'input[data-inactiveblock=changecolor]');
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function () {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('div#' + parentId);
            $(parentDiv).toggleClass('inactiveblock');
            if ($(this).is(':checked')) {
                var inpurReq = $(parentDiv).find('input[required]');
                $(inpurReq).attr('data-required', true);
                $(inpurReq).removeAttr('required');

            } else {
                var inpurReq = $(parentDiv).find('input[data-required]');
                $(inpurReq).attr('required', true);
                $(inpurReq).removeAttr('data-required');
            }
             FgCmsMenuSettings.updateSortOrder();
        });
    },
    /* function  called to initialize nestable plugin */
    initNestable: function () {
        var menudepth = (isAdditional==1)?1:3;
         $('.fg-nestable').nestable({
            expandBtnHTML: '',
            collapseBtnHTML: '',
            maxDepth: menudepth,
            dragClass: 'dd-dragel fg-cms-nav-wrapper',
            dropCallback: function() {
                FgCmsMenuSettings.updateSortOrder();
                FgUtility.handleNestablelistHandler('.fg-nestable', jstranslations.cmsNavMenuDeleteTooltip);
            }
        });
    },
    saveChanges: function(){
        $('body').off('click', '#save_changes');
        $('body').on('click', '#save_changes', function (e) {
             FgCmsMenuSettings.updateSortOrder();
        });
    },
    /* FAIR-2405 System testing issues in CMS -I issue 2
     * function  called to update the sort order and parent id after nestable sorting */
    updateSortOrder: function () {
        var currentNavSettingsData = $('.fg-nestable').nestable('serialize');
        var i = 0;
        _.each(currentNavSettingsData, function (mainMenu, index) {
            if (!$('#' + mainMenu.id).hasClass('inactiveblock')) {
                $('#' + mainMenu.id + '_sortOrder').val(i + 1);
                $('#' + mainMenu.id + '_parentId').val("1");
                if (_.size(mainMenu.children) > 0) {
                    var j = 0;
                    _.each(mainMenu.children, function (subMenu, subIndex) {
                        if (!$('#' + subMenu.id).hasClass('inactiveblock')) {
                            $('#' + subMenu.id + '_sortOrder').val(j + 1);
                            $('#' + subMenu.id + '_parentId').attr('value', mainMenu.id);
                            if (_.size(subMenu.children) > 0) {
                                var k = 0;
                                _.each(subMenu.children, function (subSubMenu, subSubIndex) {
                                    if (!$('#' + subSubMenu.id).hasClass('inactiveblock')) {
                                        $('#' + subSubMenu.id + '_sortOrder').val(k + 1);
                                        $('#' + subSubMenu.id + '_parentId').attr('value', subMenu.id);
                                        k++;
                                    }
                                });
                            }
                            j++;
                        }
                    });
                }
                i++;
            }
        });
        FgDirtyFields.updateFormState();
    },
    initTooltip:function(){
        $('body').on('mouseover click', '.fg-switch-popovers', function(e) {
            var _this = $(this),  
            thisContent = _this.find('input').is(':checked') ? _this.data('content-check'):_this.data('content-uncheck'),
            posLeft = _this.offset().left-10,
            posTop = _this.offset().top + 50;
            FgInternal.showTooltip({element: e, content: thisContent, position: [posLeft, posTop]});
            $('.popover .popover-content').width($('.popover').width()-27); 
        });
        $('body').on('mouseout', '.fg-switch-popovers', function() {
            $('body').find('.custom-popup').hide();            
            $('.popover .popover-content').width('');
        });
    }
}