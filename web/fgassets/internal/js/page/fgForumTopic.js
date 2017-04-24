 /**
  * Handle create forum topic
  * @param {type} param
  */
 var flag = 0;
 
    var FgForumCreateTopic = {
        initDocumentReady: function(){
            $(document).ready(function() {
                ForumCkEditor.init();
                FgForumCreateTopic.ckEditorReplace(false);
                $('#preview').attr('disabled',true);
                FgForumCreateTopic.initPageFunctions();  
                $('#reset_changes').attr('disabled',false);
                FgForumCreateTopic.saveChanges();
                FgForumCreateTopic.clickPreview();
                FgForumCreateTopic.clickCancel();
            });
        },
        
        saveChanges: function(){
            $('#save_changes').click(function() {
                 var validation = 0;
                var bbcodeTohtml = bbcodeParser.bbcodeToHtml($('#forum-post-text').val()).replace(/(<(?!a|img)([^>]+)>)/ig,"");
                if ($('#subject').val() == '') { // Setting validation flag if there is any errors
                    validation = 1;
                    $('#subject').parents().eq(1).addClass("has-error");
                    $('#subject').siblings().text(required);
                }else{
                    $('#subject').parents().eq(1).removeClass("has-error");
                    $('#subject').siblings().text('');
                }
                if (bbcodeTohtml == '') { // Setting validation flag if there is any errors
                    validation = 1;
                    $('#forum-post').parents().eq(1).addClass("has-error");
                    $('#forum-post').parent().find('.help-block').text(required);
                }else{
                    $('#forum-post').parents().eq(1).removeClass("has-error");
                     $('#forum-post').parent().find('.help-block').text('');
                }

                if (validation == 0) { // Checking validation. If any, should display error message
                    //convert bbcode if any to html
                    var bbcodeTohtml = bbcodeParser.bbcodeToHtml($('#forum-post-text').val());
                    $('#forum-post-text').val(bbcodeTohtml);
                    var objectGraph = {};
                    //parse the all form field value as json array and assign that value to the array
                        objectGraph = FgInternalParseFormField.fieldParse();
                        var forumArr = JSON.stringify(objectGraph);
                        FgDirtyFields.removeAllDirtyInstances();
                        FgXmlHttp.post(savePath, {'postArr': forumArr,'role':grpId,'grpType':grpType} , false, false);
                } else {
                    $('#failcallbackServerSide').show(); // Displaying errors
                    //scroll to top common form error alert on failing validation
                    FgXmlHttp.scrollToErrorDiv();
                }
            });
        },
        initPageFunctions:function(){
            FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
            FgForumCreateTopic.pageInit();
        },
        pageInit:function() {
            FgDirtyFields.init('forum-topic', {'enableDiscardChanges' : false}); // Initing fairgate dirty class
            FgFormTools.handleUniform();
            $('#subject').focus();
        },
        clickPreview :function(){
                $('#preview').click(function(){
                if(flag == 0){ //preview
                     flag = 1;

                    $('#forum-post').parent().addClass('hide');
                    $('#preview-text').removeClass('hidden');
                    var bbcodeTohtml = bbcodeParser.bbcodeToHtml($('#forum-post-text').val());
                    $('#preview-text').html(bbcodeTohtml);
                    $('#preview').text(second_btn_valedit);
                    if(CKEDITOR.instances[textareaName]){
                        CKEDITOR.instances[textareaName].destroy();  
                    }
                }else{ //editor
                     flag = 0;
                     var  initialHtml = $('#forum-post-text').html();
                     $('#forum-post').parent().removeClass('hide');
                     $('#forum-post-text').html(initialHtml);
                     $('#preview-text').addClass('hidden');
                     $('#preview').text(second_btn_val);
                     FgForumCreateTopic.ckEditorReplace(true);                                 
                }

            });
        },
        clickCancel:function(){
            $('#reset_changes').click(function(){
                $('.bckid').trigger('click');
            });
        },
        ckEditorReplace:function (autofocus){
            CKEDITOR.replace(textareaName, {
                toolbar: advancedToolsArr,
                language :locale

            }).on('change',function(){

                if(CKEDITOR.instances[textareaName].getData() != ''){
                    $('#preview').attr('disabled',false);
                    $('#forum-post-text').html(CKEDITOR.instances[textareaName].document.getBody().getHtml());
                    FgDirtyFields.updateFormState();
                } else{
                    $('#forum-post-text').html('');
                    FgDirtyFields.updateFormState();
                    $('#preview').attr('disabled',true);
                }

            });
            //To focus cursor in correct position // only for edit
            if(autofocus) {
                CKEDITOR.instances[textareaName].on('instanceReady', function(ev) {
                    FgForumCreateTopic.ckeditorFocus(CKEDITOR.instances[textareaName]);
                });
            }
            CKEDITOR.instances[textareaName].addContentsCss('/fgassets/global/css/fg-ckeditor.css');
        },
        //focus at the end of text in the editor
        ckeditorFocus: function(editorInstance) {
            editorInstance.focus();
            var s = editorInstance.getSelection(); // getting selection
            var selected_ranges = s.getRanges(); // getting ranges
            var node = selected_ranges[0].startContainer; // selecting the starting node
            var parents = node.getParents(true);

            node = parents[parents.length - 2].getFirst();

            while (true) {
                var x = node.getNext();
                if (x == null) {
                    break;
                }
                node = x;
            }

            s.selectElement(node);
            selected_ranges = s.getRanges();
            selected_ranges[0].collapse(false);  //  false collapses the range to the end of the selected node, true before the node.
            s.selectRanges(selected_ranges); 
            editorInstance.insertHtml('');
        }
        
    };
    
    var FgForumSearchTopic = {
        initDocumentReady: function(){
            $(document).ready(function(){
                FgForumSearchTopic.init(search);
                FgForumSearchTopic.searchEnter();
                FgForumSearchTopic.paginationClick();
            });
        },
        searchEnter: function(){
            $('#fg_dev_member_search').keypress(function(e){
                    if(e.which == 13){//Enter key pressed
                        // search event
                        var search = $.trim($(this).val());
                        if(search != ''){
                            search = search.replace(/\\/g,'\\\\');
                            FgForumSearchTopic.init(search);
                        }
                    }
                });
        },
        renderTemplateContent: function(templateScriptId, jsonData, parentDivId){
                var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
                $('#' + parentDivId).html(htmlFinal);
        },
        init: function(searchTerm){
           
                searchTerm = searchTerm.replace(/&lt;/g,'%3C').replace(/&gt;/g,'%3E').replace(/&quot;/g,'%22').replace('%20',"");
                dataUrl1 = dataUrl.replace('%23%23dummy%23%23',searchTerm);
                searchTerm = searchTerm.replace(/\\\\/g,'\\'); 
                searchTerm = unescape(searchTerm);
                 $('#search-term').html(searchTerm).text();        
                 $.getJSON(dataUrl1,function(data){
                    totalCnt = data['iTotalRecords']
                    dataF = data;

                    FgForumSearchTopic.renderTemplateContent('display_search',{'data':dataF['aaData'],'curPage':curPage,'dpp':dpp,'noResult':noResult,'createdBy':createdBy,'on':on}, 'search-content');
                    FgForumSearchTopic.renderTemplateContent('fg-forum-pagination-search',{'totalCnt':totalCnt,'dpp':dpp,'page':1}, 'pagination-search');
                });
        },
        paginationClick: function(){
                $('body').on('click', '.fg-dev-posts-pagination li a', function() {
                    curPage = $(this).attr('data-page');        
                    FgForumSearchTopic.renderTemplateContent('display_search',{'data':dataF['aaData'],'curPage':curPage,'dpp':dpp,'noResult':noResult,'createdBy':createdBy,'on':on}, 'search-content');
                    FgForumSearchTopic.renderTemplateContent('fg-forum-pagination-search',{'totalCnt':totalCnt,'dpp':dpp,'page':curPage}, 'pagination-search');
                });

            }
    };






