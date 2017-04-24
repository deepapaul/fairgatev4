/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgCmsThemeBackgroundList {
    public imguploaderObj: any;
    public tabselected = 1;
    public titlebarObj:any;
    constructor() {

    }

    public renderTabContent(templateId, data, appendDom) {
        
        let pageContent = FGTemplate.bind(templateId, {
            backgroundDetails: data
        });
        $(appendDom).html(pageContent);
    }

    public initUpload(settings) {
        $('#tab1 .fg-media-img-uploader').off('click');
        $('#tab1 .fg-media-img-uploader').on('click', function() {
            $('#tab1 .image-uploader').trigger('click');
        });

        imguploaderObj = FgFileUpload.init($('#tab1 .image-uploader'), settings);
    }
    //create image for preview 
    public createImagePreview(input, imgTagId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#' + imgTagId).attr('src', e.target.result).css({ 'height': '100px' });
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
    //call back after adding new image
    public addImgCallback(uploadedObj, data) {
        fgthemeBackground.handleSortOrder(uploadedObj, data);
        FgDirtyFields.updateFormState();
        $('select.selectpicker').select2();
    }

    public handleSortOrder(uploadedObj, data) {
        let rowId = data.fileid;
        let n = ($(".fg-files-uploaded-lists-wrapper li.fg-files-uploaded-list").length) ? (parseInt($(".fg-files-uploaded-lists-wrapper li").length)) : 1;
        if (rowId) {
            $('#' + rowId).find('input.fg-dev-sortable').val(n);
        }
    }
    public addGalleryImgCallback(data) {
        fgthemeBackground.addImgCallback({}, { 'fileid': data[0].itemId });
        FgDirtyFields.updateFormState();
    }


    public handleGalleryBrowser(gellerySettings, mainIdentifier) {
        FgGalleryBrowser.initialize(gellerySettings);
        FgGalleryBrowser.setSortable($('.fg-files-uploaded-lists-wrapper'));
        setTimeout(function() {
            $(mainIdentifier + ".fg-a-add-video").remove();
        }, 100)

    }
    public handleDeleteNewRow() {
        $('body').off('click', '.fg-delete-img');
        $('body').on('click', '.fg-delete-img', function(e) {    //delete media        
            $(this).parents().eq(1).remove();
        });
    }

    public saveBackgroundImageDetails() {
        let _this = this;
        $("body").on('click', "#save_changes", function() {
            //validation
            $("#articleimg-upload-error-container").html('');
            if ($('#radios-0').is(':checked')) {
                $('#default_bg_slider_time').val('');
                $('#random_bg_slider_time').val('');
            }
            if ($("#radios-1").is(':checked') && $('#default_bg_slider_time').val() == '') {
                $("#articleimg-upload-error-container").html(timevalidationMessage);
                return;
            } else if ($("#sliderwithRandom").is(':checked') && $('#random_bg_slider_time').val() == '') {
                $("#articleimg-upload-error-container").html(timevalidationMessage);
                return;
            } else if (($('#default_bg_slider_time').val() != '') && $.isNumeric($('#default_bg_slider_time').val()) == false) {
                $("#articleimg-upload-error-container").html(validationMessage);
                return;
            } else if (($('#random_bg_slider_time').val() != '') && $.isNumeric($('#random_bg_slider_time').val()) == false) {
                $("#articleimg-upload-error-container").html(validationMessage);
                return;
            }
            $("#articleimg-upload-error-container").html('');
            let objectGraph = {};

            $("ul.fg-files-uploaded-lists-wrapper li:not(.inactiveblock)").each(function(e, value) {
                let oldVal =$(this).find(".fg-dev-sortable").val();
                if(oldVal !=(e + 1)) {
                $(this).find(".fg-dev-sortable").val(e + 1);
                $(this).find(".fg-dev-sortable").addClass('fg-dev-newfield');
                }

            })
            objectGraph = FgInternalParseFormField.formFieldParse('fg_cms_background_add');
            _this.initDirty();
            $('.fg-files-uploaded-lists-wrapper').find('.inactiveblock').remove();
            let imageDetails = JSON.stringify(objectGraph);                      //SAVE 

            FgXmlHttp.post(backgroundImageSave, {
                'imageDetails': imageDetails, 'configId': configId

            }, '', function(response) {
                backgroundDetails = response.backgroundData;
                fgthemeBackground.renderTabContent('templateOriginalSize', backgroundDetails, '#tab2');
                fgthemeBackground.renderTabContent('templateFullscreen', backgroundDetails, '#tab1');


                if (fgthemeBackground.tabselected == '2') {
                    fgthemeBackground.initOriginalImageUpload(backgroundOriginalImgUploaderOptions, '#tab2');
                    fgthemeBackground.handleGalleryBrowser(originalGalleryBrowserSettings, "#tab2");

                } else {
                    fgthemeBackground.initUpload(backgroundFullImgUploaderOptions, '#tab1');
                    fgthemeBackground.handleGalleryBrowser(galleryBrowserSettings, '#tab1');
                }
                fgthemeBackground.initDirty();
                $('select.selectpicker').select2();
                FgFormTools.handleUniform();


            });
        })


    }
    //make row color pink on delete
    public handleDeleteIconColor() {
        $('body').off('click', '.make-switch');
        $('body').on('click', '.make-switch', function(e) {
            if ($(this).is(':checked') == true) {
                $(this).parents('li').addClass('inactiveblock');
            } else {
                $(this).parents('li').removeClass('inactiveblock');
            }
        });
    }


    public initOriginalImageUpload(settings) {
        $('#fg-media-img-uploader').off('click');
        $('#fg-media-img-uploader').on('click', function() {
            $('#image-original-uploader').trigger('click');
        });
        FgFileUpload.init($('#image-original-uploader'), settings);
    }

    public initDirty() {
        FgDirtyFields.init('fg_cms_background_add', { saveChangeSelector: "#save_changes, #reset_changes", enableDiscardChanges: true, enableUpdateSortOrder: true, discardChangesCallback: fgthemeBackground.discardChangesCallback });
    }

    public bgtabInit() {
        let _this = this;
        $(function() {
            _this.initDirty();
           _this.titlebarObj = $(".fg-action-menu-wrapper").FgPageTitlebar({
                title: true,
                editTitleInline: false,
                tab: true,
                tabType: 'server',
                languageSwitch: false,
                editTitle: true
            });

            $("#paneltab").find(".active").removeClass('active');
            $("#fg_tab_background").addClass('active');
            //tab click handle
            $('body').on('click', 'ul.fg-dev-bg-tabs li', function() {
                if ($(this).attr('data-type') == 1) {
                    _this.tabselected = 1;
                    _this.handleGalleryBrowser(galleryBrowserSettings, '#tab1');
                    _this.initUpload(backgroundFullImgUploaderOptions, '#tab1');

                } else {
                    _this.tabselected = 2;
                    $('select.selectpicker').select2();
                    _this.handleGalleryBrowser(originalGalleryBrowserSettings, "#tab2");
                    _this.initOriginalImageUpload(backgroundOriginalImgUploaderOptions, '#tab2');

                }


            })
            //To give click event for text for to handle the selection of corresponding radio button 
            $('body').on('click', '.fg-bg-radio', function() {
                $(".fg-bg-radio").val('');
                $(this).parents('.radio-block').find('.fg-radio-select').trigger('click');
                $.uniform.update()
            });
            _this.initDirty();
            //For edit page title
            _this.changePageTitle();
            _this.savePageTitle();


            // FgPageTitlebar.setMoreTab();
        });
    }

    public discardChangesCallback() {

        //Redraw the content
        fgthemeBackground.renderTabContent('templateOriginalSize', backgroundDetails, '#tab2');
        fgthemeBackground.renderTabContent('templateFullscreen', backgroundDetails, '#tab1');


        if (fgthemeBackground.tabselected == '2') {
            fgthemeBackground.initOriginalImageUpload(backgroundOriginalImgUploaderOptions, '#tab2');
            fgthemeBackground.handleGalleryBrowser(originalGalleryBrowserSettings, "#tab2");
            $("#data_li_2").addClass("active");
            $("#tab2").addClass("active");
            $("#data_li_1").removeClass("active");
            $("#tab1").removeClass("active");

        } else {
            fgthemeBackground.initUpload(backgroundFullImgUploaderOptions, '#tab1');
            fgthemeBackground.handleGalleryBrowser(galleryBrowserSettings, '#tab1');
        }
        fgthemeBackground.initDirty();
        $('select.selectpicker').select2();
        FgFormTools.handleUniform();
        fgthemeBackground.titlebarObj.setMoreTab();

//        if (fgthemeBackground.tabselected == '2') {                     
//            //  to remove select container for select        2 call
//            $("#tab2 .fg-files-upload-wrapper .select2-container.selectpicker").rem        ove();
//            fgthemeBackground.handleGalleryBrowser(originalGalleryBrowserSettings, "#t        ab2");
//            fgthemeBackground.initOriginalImageUpload(backgroundOriginalImgUploaderOptions, '#t        ab2');
//                    
//            $("#data_li_2").addClass("act        ive");
//            $("#tab2").addClass("act        ive");
//            $("#data_li_1").removeClass("act        ive");
//            $("#tab1").removeClass("act        ive");
//            $('select.selectpicker').sele        ct2();
//                }
//                else {
//            fgthemeBackground.initUpload(backgroundFullImgUploaderOptions, '#t        ab1');
//            fgthemeBackground.handleGalleryBrowser(galleryBrowserSettings, '#t        ab1');
//            $(".fg-uniform-unwrap").unwrap().unw        rap();
//            FgFormTools.handleUnif        orm();
//        }

    }

    public changePageTitle() {
        $('body').on('click', '.fg-action-editTitle', function() {
            $('.fg-cms-title-change-form').removeClass('has-error');
            $('span.required').remove();
            let titleText = $('.page-title  .page-title-text').html();
            $('#pageTitleChange').val(titleText);
            $('#config-title-change-modal').modal('show');
        });
    }
    public savePageTitle() {
        $(document).off('click', '#savePopup');
        $(document).on('click', '#savePopup', function() {
            let pageTitle = $('#pageTitleChange').val();
            if ($.trim(pageTitle) === '') {
                $('.fg-cms-title-change-form').addClass('has-error');
                $('.fg-error-add-required').append('<span class="required">' + transFields.required + '</span>');
                return false;
            } else {
                FgXmlHttp.post(changePageTitlePath, { 'config': configId, 'title': pageTitle }, '', function(response) {
                    $('#config-title-change-modal').modal('hide');
                    $('.page-title  .page-title-text').html('');
                    $('.page-title  .page-title-text').html(pageTitle);
                });
            }
        });
    }




}
