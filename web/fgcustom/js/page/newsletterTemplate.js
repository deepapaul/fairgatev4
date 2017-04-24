var templateId =$("#id").val();
$(document).ready(function () {
    FgApp.init();
     FgInputTag.handleUniform();
    $('.colorpicker-default').colorpicker();          
    if (errFilename) {
        $(".alert-danger").show();
        $("#reset_changes").removeAttr("disabled");
    }    
    FormValidation.init('form', 'saveChanges', 'callBackOnInvalidForm');    
    NewsletterTemplate.handleColorPicker();
    NewsletterTemplate.handleListRow();
    NewsletterTemplate.resetChanges();
    FgUtility.changeColorAndHandleRequiredOnDelete();   
    NewsletterTemplate.enableSalutationText();
    FgColumnSettings.handleSelectPicker();
    NewsletterTemplate.handleSelectLanguage();
    $(window).resize(function() {
        NewsletterTemplate.autoResize('myiframe');
    });

    NewsletterTemplate.handleEmailField();
});

$(document).ajaxComplete(function() {
    NewsletterTemplate.autoResize('myiframe');
});
$(window).bind("load", function() {
   
     NewsletterTemplate.initUpload(imageElementUploaderOptions);
    NewsletterTemplate.handleExistingimageUpload(templateId);
});
var chkReadyState = setInterval(function() {
     FgUtility.startPageLoading();
    if (document.readyState == "complete") {
         clearInterval(chkReadyState);
          FgUtility.stopPageLoading();
       
    }
}, 300);
//call back function on success validation
function saveChanges() {    
    //parse the all form field value as json array and assign that value to the array
        if($('#newsletter-template-changed').val()==0){
           $('#picture_88').removeClass("fairgatedirty");
           $('#dropzone_file').removeClass("fairgatedirty");
         }
        var objectGraph = FgParseFormField.fieldParse();    
        $('.has-error').removeClass('has-error');
        var catArr = JSON.stringify(objectGraph);  
        FgDirtyFields.removeAllDirtyInstances();
        var templateId = $('#id').val();
       FgXmlHttp.post(pathTemplateCreate, {'catArr': catArr, "id" : templateId}, false, callbackfn);
}

//call back function when validation fails
function callBackOnInvalidForm() {  
 setTimeout(function () {    
    $( ".tab-pane" ).each(function( index ) {        
        if( $( this ).find(".has-error").length > 0 ) {    
            var elemntId = $( this ).attr('id');
            $("a[href=#"+elemntId+"]").parent().addClass("has-error");     
            $("a[href=#"+elemntId+"]").find('.fa-exclamation-triangle').removeClass("hide");
        }else{
            var elemntId = $( this ).attr('id');
            $("a[href=#"+elemntId+"]").parent().removeClass("has-error");     
            $("a[href=#"+elemntId+"]").find('.fa-exclamation-triangle').addClass("hide");
        }
      });
    }, 1000);
}

function callbackfn(data) {   
 
    $("#id").val(data.templateId);
    var iFrame = $('#myiframe');
    var iframeUrl = pathTemplateIframe.replace('|TEMPLATEID|', data.templateId);
    iFrame.load(iframeUrl);
    $("#myiframe").attr("src",iframeUrl);
    var newfile =  $('#dropzone_file').val();
    $('#picture_88').val(newfile);
    $('#newsletter-template-changed').val(0);
    NewsletterTemplate.disableDirty();
  
   
}
        
var NewsletterTemplate = {
    
    handleExistingimageUpload:function (templateId) {
           
       if (templateId > 0) {
            var filename = $("#picture_88").val();
            if (filename != '') {
                
                var path = '/uploads/' + club_id + '/admin/newsletter_header/';
                var filenamepath = path + filename;
            }
            var rowId = templateId;
            var datatoTemplate = { name: rowId, id : templateId, };
            ImagesUploader.showExistImagePreview(rowId,datatoTemplate,filenamepath);
       }
       
       $("#triggerFileUpload").on("click", function(e) {
            e.stopImmediatePropagation();
            e.stopPropagation();
             if ( e.target === this ) {
                    $('#image-uploader').trigger('click');
             }
            
        });
        
    },
    
     initUpload: function(settings){
       FgFileUpload.init($('#image-uploader'), settings);
    },
    
    //color picker handler
    handleColorPicker: function () {

        var defaultsettings = {
            control: 'wheel',
            opacity: false,
            theme: 'bootstrap',
            change: function(value) {  
                $(this).find('input').val(value);
            }
            };
  
        
        $('.colorpicker-input').each(function(){
            var colorval = $(this).find('input.form-control').val();
            defaultsettings.defaultValue = colorval;
            $(this).minicolors(defaultsettings);
        });
    },
    
    
    // reset changes handler
    resetChanges: function() {
        $('#reset_changes').on('click', function () {
            if (templateId > 0) {
                window.location = pathTemplateEdit;
            } else {
                window.location = pathTemplateCreate;
            }
        });
        //enable fairgatedirty class on fileds for image drag drop
        $('#save_changes').on('click', function () {            
            FgDirtyFields.updateFormState();
            if (templateId == 0) {
                $('.dirtyClass').addClass('fairgatedirty');
            }            
        });        
    },
    
    // list row (sponsor section) handler
    handleListRow: function () {       
        $('div[data-list-wrap]').rowList({
            template: '#sponsorContentlistWrap',
            jsondataUrl: pathTemplateSponsorContent,
            fieldSort: '.sortables',
            submit: ['#save_changes', 'form'],
            deleteBtn:'.fg-row-close',
            reset: '#reset_changes',
            useDirtyFields: true,
            dirtyFieldsConfig: {"enableDiscardChanges": false,'discardChangesCallback': NewsletterTemplate.discardChangesCallbackFn},
            rowCallback : function(data){                                
                NewsletterTemplate.handleFormElements();
                var lastSortVal = parseInt($('input.sort-val').length);                
                $('input.sort-val:last').val(lastSortVal);
            },
            // searchfilterData: filterData,
            addData: ['.addField', {
                isAllActive: false,
                isNew: true
            }, 'editor.init'],
            loadTemplate:[{
                btn:'.cat-2', 
                template:'#sponsor-area',
                target:'#addContent'
            }],
            validate: true,
            initCallback: function() {  
                NewsletterTemplate.handleFormElements();
            }
        });
    },
    
    //handle form elents in sponsor section
    handleFormElements: function() {        
        $('select.selectpicker').selectpicker({noneSelectedText: datatabletranslations.noneSelectedText});  
        $('select.selectpicker').selectpicker('render');
        FgFormTools.handleUniform();
        FgFormTools.handleSelect2();   
        NewsletterTemplate.handleSelectLanguage();
    },
    
    //enable salutation text with respect to salutation type
    enableSalutationText: function() {
        $('.salutation-type-input').on('click', function () {
            if (this.value == "SAME") {
                $("#salutation-general-input").removeAttr("disabled")
            } else {
                $("#salutation-general-input").attr("disabled", "disabled")
            } 
        });
    },  
    
    //select all functionality for language box
    handleSelectLanguage: function () {
        var selectedli = $('.fg-lang-select').find('li.selected').length;
        totalli = $('.fg-lang-select').find('li').length;
        if (totalli == selectedli) {
            $('.fg-lang-select').find('.filter-option').html(all);
        }
    },
    
    // auto resize of iframe
    autoResize: function (id) {
        var newheight;
        if (document.getElementById) {
            newheight = document.getElementById(id).contentWindow.document.body.scrollHeight;
        }
        $("#"+id).height(newheight*1 + 15);
        if($(window).width() < 768) {
            $("#"+id).height(newheight + 20);
        }
    },
    
    //discard changes call back function
    discardChangesCallbackFn: function () {        
        $("#tab_15_3").find('select.selectpicker').selectpicker({noneSelectedText: datatabletranslations.noneSelectedText});  
        $("#tab_15_3").find('select.selectpicker').selectpicker('render');
        FgFormTools.handleUniform();
        FgFormTools.handleSelect2();   
        FgColumnSettings.handleSelectPicker();
        NewsletterTemplate.handleSelectLanguage();
    },
    handleEmailField: function() {
        $(document).on("keyup", "input[type=email]", function() {
            $('input[name=sender_email]').val($(this).val());
            FgDirtyFields.updateFormState();
        }) ;
    },
    disableDirty:function(){
        $("input").removeClass("fairgatedirty");
        $("input").removeClass("dirtyClass");
        $("#reset_changes").attr('disabled',true);
        $("#save_changes").attr('disabled',true);
    }
}
