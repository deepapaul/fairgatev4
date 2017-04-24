$(document).ready(function(){
    FgUtility.moreTab();
    FgMoreMenu.initServerSide('data-tabs', 'data-tabs-content', 'data');     
    FgDocumentTable.init();   
        setTimeout( renderAddExistingDiv(), 2000);    
});

// function to render div containing add existing autocomplete inputs
function renderAddExistingDiv() {    
    $( ".div-add-existing" ).remove();
    var template = $('#templateAddExisting').html();  
    var readonlyFlag = (typeof readonlyFlag != 'undefined') ? readonlyFlag : 0;
    if(readonlyFlag != 1) {
        $( ".fg-datatable-pagination" ).before( template );
        $( "#existingDocs" ).fbautocomplete({
            url: autocompleteUrl, // which url will provide json!                
            maxItems: 1,
            useCache: false,
            onItemSelected: function($obj, itemId, selected) {
            }
        });
    }
}

function addDocument() {
    if(document.getElementsByName("otherDocuments[id][]")) {
        documentId = $('.ids-fbautocomplete').val();
        if(documentId) {
            $.ajax({
                type: "POST",
                    url: assignDocumentPath,
                    data: {"documentId": documentId}
                })
                .done(function (data) {                         
                    hideField();      
                    redrawdataTableFromServer();
                });  
        }
    }            
}

function showField() {
    $('.fg-save-template').addClass('show-input');
}
function hideField() {
    if ( $( ".remove-fbautocomplete" ).length ) {
        $( ".remove-fbautocomplete" ).trigger( "click" );
    }
    $('.fg-save-template').removeClass('show-input');
} 

function redrawdataTable() {
    redrawdataTableFromServer();
    renderAddExistingDiv();
}

