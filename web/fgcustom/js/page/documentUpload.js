var documentupload={
    //initialization function for upload block
    rowinit:function(row){
        FgFormTools.handleUniform();
        $('.selectpicker').selectpicker({
           noneSelectedText: datatabletranslations.noneSelectedText,
           countSelectedText: datatabletranslations.countSelectedText,
        });
        if(docType ==='CLUB') {
            documentupload.handleAutoComplete(row);
        } else if(docType ==='TEAM' || docType ==='WORKGROUP' ) {
            FgColumnSettings.handleSelectPicker();
        } else if(docType ==='CONTACT'){
            if($('input[data-contactlist]').length ){
               documentupload.handleContactsAuto(row);
            }
        }
            
        documentupload.handleDepositedSelection();

    },
    //doc list upload save handler
    handleSave:function(){
            $('#save_changes').on('click',function () {
            documentupload.setcategorySelection();
            if($('#save_changes').is(':disabled')) {
                return false;
            }
            FormValidation.init('upload-form','');
            if($('#upload-form').valid()===false ) {
                 return false;
            }
            //get json data array
            var objectGraph = {};
            $("form :input").each(function() {
                    var inputVal = '';
                    var inputType = $(this).attr('type');
                    if (inputType == 'checkbox') {
                        inputVal = $(this).attr('checked') ? 1 : 0;
                    } else if (inputType == 'radio') {
                        if ($(this).is(':checked')) {
                            inputVal = $(this).val();
                        }
                    } else {
                        inputVal = $(this).val();
                    }
                    var selection=$(this).attr('data-club');
                    if ((selection ==='club' || selection ==='contact' ) && inputVal !=='') {
                        inputVal=JSON.parse(inputVal);
                    }
                    var attr = $(this).attr('data-key');
                    if (typeof attr !== typeof undefined && attr !== false) {
                        if (inputVal !== '' || $(this).is("textarea") || $(this).is("select")) {
                            converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                        } else if (inputType == 'hidden') {
                            converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                        }
                    }

            }); 
           var documentArr = JSON.stringify(objectGraph);
           FgXmlHttp.post($('#upload-form').attr('data-url'), {'documentArr': documentArr,'type': docType } , false,documentupload.handleCallback);
           return false;
        });
    },
    //get data array for saving data 
    getResultObject:function(){
        var objectGraph = {};
            $("form :input").each(function() {
                    var inputVal = '';
                    var inputType = $(this).attr('type');
                    if (inputType == 'checkbox') {
                        inputVal = $(this).attr('checked') ? 1 : 0;
                    } else if (inputType == 'radio') {
                        if ($(this).is(':checked')) {
                            inputVal = $(this).val();
                        }
                    } else {
                        inputVal = $(this).val();
                    }
                    var selection=$(this).attr('data-club');
                    if ((selection ==='club' || selection ==='contact' ) && inputVal !=='') {
                        inputVal=JSON.parse(inputVal);
                    }
                    var attr = $(this).attr('data-key');
                    if (typeof attr !== typeof undefined && attr !== false) {
                        if (inputVal !== '' || $(this).is("textarea") || $(this).is("select")) {
                            converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                        } else if (inputType == 'hidden') {
                            converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                        }
                    }

            }); 
           return JSON.stringify(objectGraph);
    },
    //club autocomplete handler
    handleAutoComplete:function(row){
        var selectedClubIds=$('#'+row).find('input[data-selected]').attr('data-selected');
        var excludedClubIds=$('#'+row).find('input[data-excluded]').attr('data-excluded');
        selectedClubIds=(typeof selectedClubIds != typeof undifined && selectedClubIds !='' ) ? selectedClubIds:[];
        excludedClubIds=(typeof excludedClubIds != typeof undifined && excludedClubIds !='' ) ? excludedClubIds:[];
            var selectedClub = [];
            var excludedClub =[];
            index1 = index = 0;
            $.each(clubs,function(i,vals){
                if(selectedClubIds.indexOf(','+vals.id+',') >= 0){
                    selectedClub[index++]={id:vals.id,title: vals['title']};
                }
                if(excludedClubIds.indexOf(','+vals.id+',') >= 0){
                    excludedClub[index1++]={id:vals.id,title: vals['title']};
                }
            });
        
        $('#'+row).find('input[data-clublist]').each(function(){
            $(this).fbautocomplete({
                removeButtonTitle: removestring,
                maxItems: 50,
                useCache: true,
                selected: $(this).attr('data-clublist')==='exclude'? excludedClub :selectedClub,
                staticRetrieve:function(term){
                    var clubList=[];
                    jQuery.each(clubs, function( i,str ) {
                        if(str['title'].toLowerCase().match("^"+term.toLowerCase())){
                            clubList.push({id:str.id,title:str.title});
                        }
                    });
                    return clubList;
                },
                onItemSelected: function($obj, itemId, selected) {
                    var ids= $('#'+$obj.context.id+'Selection').val()==='' || $('#'+$obj.context.id+'Selection').val()==='[""]' ? []: JSON.parse($('#'+$obj.context.id+'Selection').val());
                    ids.push(itemId);
                    $('#'+$obj.context.id+'Selection').val(JSON.stringify(ids));
                    $('#'+$obj.context.id+'Selection').parents('div[data-selection-auto]').find('input[data-selected]').val('1');
                    FgDirtyFields.updateFormState();
                },
                onItemRemoved: function($obj, itemId) {
                    ids= JSON.parse($('#'+$obj.context.id+'Selection').val());
                    if(typeof (ids) == 'object') { 
                        var newArray = jQuery.grep(ids, function (item,index) { return item !== itemId;  });
                        $('#'+$obj.context.id+'Selection').val(JSON.stringify(newArray));
                    }
                    var selectedval=$('#'+$obj.context.id+'Selection').val();
                    if(selectedval=='[]' || selectedval=='[]' ){
                        $('#'+$obj.context.id+'Selection').parents('div[data-selection-auto]').find('input[data-selected]').val('');
                    }
                    FgDirtyFields.updateFormState();
                },
                onAlreadySelected: function($obj) {

                }
            });
        });
    },
    //document save callback
    handleCallback:function(responce){
        var catId = '';
        var subId = '';
        saveClicked=0;
        var updateArr = [];
        var dataType = 'DOCS-'+clubId;
        $('select[data-subcategoryId]').each(function(){
             subId=  $(this).val();
             catId= $(this).find('option[value='+subId+']').parents('optgroup').attr('data-id');
             updateArr.push({'categoryId' : catId, 'subCatId' : subId, 'dataType' : dataType, 'sidebarCount' : 1, 'action' : 'add'});
        });
        FgCountUpdate.update('add', 'document', type.toLowerCase(), updateArr, responce.totalCount); //add, modulename, type, catId, roleId
        FgDirtyFields.removeFields($('div[data-fieldarea] ul'));
        $('div[data-fieldarea] ul').html('');
        $('div[data-upload-doc-area]').addClass('hide');
        redrawdataTable();
        FgDirtyFields.updateFormState();
    },
    /**
     * switching hanler for club  deposited with and team visibility
     */
    handleDepositedSelection:function(){
        $('body').on('change','input[data-deposited]',function(){
            var parent=$(this).parents('dd');
            var selVal=$(parent).find('input[data-deposited]:checked').val();
            switch(selVal){
                case 'SELECTED': case 'ALL':
                    $(parent).find('div[data-selection-auto] .fbautocomplete-main-div input').attr('disabled',true);
                    $(parent).find('div[data-selection-auto] .fbautocomplete-main-div').addClass('fg-input-wrapper-disabled');
                    $(parent).find('div[data-selection-auto] input[data-selected]').removeAttr('required');
                    $(parent).find('div[data-selection-auto='+selVal+'] .fbautocomplete-main-div').removeClass('fg-input-wrapper-disabled');
                    $(parent).find('div[data-selection-auto='+selVal+'] .fbautocomplete-main-div input').removeAttr('disabled');
                    if(selVal==='SELECTED') {
                        $(parent).find('div[data-selection-auto='+selVal+'] input[data-selected]').attr('required',true);
                    }
                    break;
                case 'team_functions':
                    $(parent).find('div[data-selection-auto] select').removeAttr('disabled');
                    $('div[data-selection-auto] select').selectpicker('refresh');
                    break;
                default:
                    $(parent).find('div[data-selection-auto] .fbautocomplete-main-div').addClass('fg-input-wrapper-disabled');
                    $(parent).find('div[data-selection-auto] .fbautocomplete-main-div input').attr('disabled',true);
                    $(parent).find('div[data-selection-auto] select').attr('disabled',true);
                    $(parent).find('div[data-selection-auto] input[data-selected]').removeAttr('required');
                    $('div[data-selection-auto] select').selectpicker('refresh');
                    break;
            }
        });
    },
    /**
     * Reset changes handler
     */
    handleReset:function(){       
        $('#reset_changes').on('click', function () {
            if(!$(this).is(':disabled')){
                $('div[data-fieldarea] ul').html('');
                $('div[data-upload-doc-area]').addClass('hide'); 
            }
        });
    },
    /**
     * set category and sub category values
     * @returns {Boolean}
     */
    setcategorySelection:function(){
        $('select[data-subcategoryId]').each(function(){
            var subCat=$(this).val();
            $(this).parents('li').find('input[data-categoryId]').val($(this).find('option[value='+subCat+']').parents('optgroup').attr('data-id'));
        });
        return true;
    },
    /**
     * Contact autocomplete handler for include and exclude in contact doc
     * @param {type} row
     */
    handleContactsAuto:function(row){
        selectedContact =(typeof selectedContacts===typeof undifined) ? '':selectedContacts;
        contactExcluded =(typeof contactExcluded===typeof undifined) ? '':contactExcluded;
        $('#'+row).find('input[data-contactlist]').each(function(){
            $(this).fbautocomplete({
                url: contactUrl, // which url will provide json!
                removeButtonTitle: removestring,
                params: {'isCompany': 2} ,        
                selected: $(this).attr('data-contactlist')==='exclude'? contactExcluded :selectedContact,
                maxItems: 50,
                useCache: true,
                onItemSelected: function($obj, itemId, selected) {
                    var ids= $('#'+$obj.context.id+'Selection').val()==='' ? []: JSON.parse($('#'+$obj.context.id+'Selection').val());
                    ids.push(itemId);
                    $('#'+$obj.context.id+'Selection').val(JSON.stringify(ids));
                    FgDirtyFields.updateFormState();
                },
                onItemRemoved: function($obj, itemId) {
                    ids= JSON.parse($('#'+$obj.context.id+'Selection').val());
                    if(typeof (ids) == 'object') {
                        var newArray = jQuery.grep(ids, function (item,index) { return item !== itemId;  });
                        $('#'+$obj.context.id+'Selection').val(JSON.stringify(newArray));
                    }
                    FgDirtyFields.updateFormState();
                },
                onAlreadySelected: function($obj) {
                    
                }
            });
        });
    },
    /**
     * contact filter handler
     * 
     * @param {type} row
     */
    handleFilter:function(row){
       $.getJSON(pathFilterData, {
            'getFilterRole': false
        }, function(jsonFilterData) {
            filterData= (typeof filterData===typeof undifined) ? false:filterData;
            var abc= $('#upload-filter-area').searchFilter({
                    jsonGlobalVar: jsonFilterData,
                    save: '#save_filter',
                    filterName: "contact_filter",
                    addBtn: '#add-'+row,
                    storageName: 'filter'+row,
                    clearBtn: '.remove-filter',
                    clearAddDefault:false,
                    customSelect: true,
                    rebuild: filterData,
                    dateFormat: FgApp.dateFormat,
                    selectTitle: selectType,
                    conditions: filterCondition,
                    criteria: '<div class="col-lg-1"><span class="fg-criterion">'+cm_criteria+'</span></div>',
                    markUpClass:{
                        level1:"col-lg-1 1x",
                        level2:"col-lg-2 2x",
                        level3:"col-lg-2 3x",
                        level4:"col-lg-2 4x",
                        level5:"col-lg-3 5x",
                        level6:"col-lg-2 6x"

                    },
                    onComplete: function(data) {
                        if (data != 0) {
                            if (data != 1) {
                                $('input[data-filtertype]').val(JSON.stringify(data));
                            } else {
                                $('input[data-filtertype]').val('{"contact_filter":{}}');
                            }
                            $('#filterError').val(1);
                            FgDirtyFields.updateFormState();
                        }
                    },
                    savedCallback: function(data) {
                        //console.log(JSON.stringify(data));
                    },
                    errorCallack: function() {
                        $('input[data-filtertype]').val("");
                        $('#filterError').val(0);
                        console.log('error');
                        FgDirtyFields.updateFormState();
                    },
                    removeRowCallback: function() {
                        $('body').find('#save_filter').trigger('click');
                        FgDirtyFields.updateFormState();
                    },
                    changeCallback: function() {
                        $('body').find('#save_filter').trigger('click');
                        FgDirtyFields.updateFormState();
                    }
                });
            });
    },
    /**
     * Edit document save handler
     */
    handleEditDocumentSave:function(){
        documentArr = documentupload.getResultObject();
        if (typeof saveAction !== 'undefined') {
            FgXmlHttp.post(saveAction, {'documentArr': documentArr,'type': type} , false, documentupload.handleCallback);
        }
    },
    /**
     * delete file handler for edit docs
     */
    handleDeleteSingleFile:function(){
        $('body').on('click','a[data-dismiss=fileinput]',function(){
                FgDirtyFields.removeFields($('ul[data-files-ul]'));
                $('ul[data-files-ul]').html('');
        });
    },
    /**
     * dynamic menu handler for upload
     */
    handleUploadMenu:function(){
        $('body').on('click','a[data-action-type=upload].fg-dev-menu-click',function(){
            $('input[data-uploadtemplate]').click();
        });
    },
    disableButtons: function() {
         $('form').find('#save_changes').attr('disabled', 'disabled');
         $('form').find('#reset_changes').attr('disabled', 'disabled');
    }
}
