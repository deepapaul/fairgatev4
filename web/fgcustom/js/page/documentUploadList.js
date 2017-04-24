$(document).ready(function() {
    FgColumnSettings.handleSelectPicker();
    fileUploader.init({id:'upload-1'});
    FgDirtyForm.rescan('upload-form');
    documentupload.handleReset();
    documentupload.handleUploadMenu();    
    documentupload.handleSave();
    $('div[data-upload-doc-area]').on('keydown','input[type=text]',function(event){
        if(event.keyCode == 13) {
          event.preventDefault();
          return false;
        } 
    });
}); 