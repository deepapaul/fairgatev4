var FormDropzone = function () {


    return {
        //main function to initiate the module
        init: function () {  
           // myDropzone5 myDropzone21 is id 
            Dropzone.options.myDropzone5 = {
                init: function() {
                    //to prevent more than 1 file upload
                    this.on("maxfilesexceeded", function(file) { 
                        this.removeAllFiles();
                        this.addFile(file);
                    });
                    this.on("addedfile", function(file) {
                        Metronic.startPageLoading();
                        setTimeout(function() {
                            $("#fg_field_category_21_5").attr('data-value',file.name);
                            $("#picture_5").val(file.name);
                            setTimeout(function() {
                                var deletedDragFiles= $("#deleteddragFiles").val();
                                if((deletedDragFiles.indexOf("5")) != -1) {
                                    $("#deleteddragFiles").val("");
                                }
                                var valImage = $("#my-dropzone-5").children().find('img').attr("src");
                                if (typeof valImage != "undefined") {
                                    valImage = valImage.split("base64,");
                                    $("#picture_byte_5").val(valImage[1]);
                                }
                                if(($("#picture_byte_5").val())==0) {
                                    $("#picture_5").val("");
                                }
                                var hasPreview5 = $("#my-dropzone-5").children().length;
                                if(hasPreview5 == 3) {
                                    $('#my-dropzone-5 > :nth-child(2)').hide();
                                }
                                 Metronic.stopPageLoading();
                            }, 200);
                           
                        }, 200);
                        $("#save_changes").attr("disabled",false);
                        $("#reset_change").attr("disabled",false)
                    });
                    this.on("removedfile", function(file) { 
                        $("#fg_field_category_21_5").attr('data-value',"");
                        $("#picture_5").val("");
                        $("#picture_byte_5").val("");
                        var deletedFiles = $("#deleteddragFiles").val();
                        if((deletedFiles.indexOf("5")) === -1) {
                             deletedFiles = deletedFiles+','+'5';
                        }
                        //deletedFiles = (deletedFiles=='') ? '5' : (deletedFiles+','+'5');
                        $("#deleteddragFiles").val(deletedFiles);
                        $("#save_changes").attr("disabled",false);
                        $("#reset_change").attr("disabled",false);
                        $('#my-dropzone-5 > :nth-child(2)').show();
                    });
                    
                }            
            },
             Dropzone.options.myDropzone21 = {
                init: function() {
                    
                    //to prevent more than 1 file upload
                    this.on("maxfilesexceeded", function(file) { 
                        this.removeAllFiles();
                        this.addFile(file);
                    });
                    this.on("addedfile", function(file) {
                        Metronic.startPageLoading();
                        setTimeout(function() {
                            $("#fg_field_category_21_21").attr('data-value',file.name);
                            $("#picture_21").val(file.name);
                            setTimeout(function() {
                                var deletedDragFiles= $("#deleteddragFiles").val();
                                if((deletedDragFiles.indexOf("21")) != -1) {
                                    $("#deleteddragFiles").val("");
                                }
                                var valImage21 = $("#my-dropzone-21").children().find('img').attr("src");
                                if (typeof valImage21 != "undefined") {
                                    valImage21 = valImage21.split("base64,");
                                    $("#picture_byte_21").val(valImage21[1]);
                                }
                                var hasPreview21 = $("#my-dropzone-21").children().length;
                                if(hasPreview21 == 3) {
                                    $('#my-dropzone-21 > :nth-child(2)').hide();
                                } 
                                if(($("#picture_byte_21").val())==0) {
                                    $("#picture_21").val("");
                                }
                                Metronic.stopPageLoading();
                            }, 200);
                        }, 200);
                        $("#save_changes").attr("disabled",false);
                        $("#reset_change").attr("disabled",false)
                    });
                    this.on("removedfile", function(file) { 
                        $("#fg_field_category_21_21").attr('data-value',"");
                        $("#picture_21").val("");
                        $("#picture_byte_21").val("");
                        var deletedFiles = $("#deleteddragFiles").val();
                        if((deletedFiles.indexOf("21")) === -1) {
                             deletedFiles = deletedFiles+','+'21';
                        }
                        $("#deleteddragFiles").val(deletedFiles);
                        $("#save_changes").attr("disabled",false);
                        $("#reset_change").attr("disabled",false);
                         $('#my-dropzone-21 > :nth-child(2)').show();
                    });
                }            
            },
            Dropzone.options.myDropzone68 = {
                init: function() {
                    //to prevent more than 1 file upload
                    this.on("maxfilesexceeded", function(file) { 
                        this.removeAllFiles();
                        this.addFile(file);
                    });
                    this.on("addedfile", function(file) {
                        Metronic.startPageLoading();
                        setTimeout(function() {
                            $("#fg_field_category_21_68").attr('data-value',file.name);
                            $("#picture_68").val(file.name);
                            setTimeout(function() {
                                var deletedDragFiles= $("#deleteddragFiles").val();
                                if((deletedDragFiles.indexOf("68")) != -1) {
                                    $("#deleteddragFiles").val("");
                                }
                                var valImage68 = $("#my-dropzone-68").children().find('img').attr("src");
                                if (typeof valImage68 != "undefined") {
                                    valImage68 = valImage68.split("base64,");
                                    $("#picture_byte_68").val(valImage68[1]);
                                }
                                var hasPreview68 = $("#my-dropzone-68").children().length;
                                if(hasPreview68 == 3) {
                                    $('#my-dropzone-68 > :nth-child(2)').hide();
                                } 
                                if(($("#picture_byte_68").val())==0) {
                                    $("#picture_68").val("");
                                }
                                Metronic.stopPageLoading();
                            }, 200);   
                        }, 200);
                         $("#save_changes").attr("disabled",false);
                         $("#reset_change").attr("disabled",false)
                    });
                    this.on("removedfile", function(file) { 
                        $("#fg_field_category_21_68").attr('data-value',"");
                        $("#picture_68").val("");
                        $("#picture_byte_68").val("");
                        var deletedFiles = $("#deleteddragFiles").val();
                        if((deletedFiles.indexOf("68")) === -1) {
                             deletedFiles = deletedFiles+','+'68';
                        }
                        $("#deleteddragFiles").val(deletedFiles);
                        $("#save_changes").attr("disabled",false);
                        $("#reset_change").attr("disabled",false);
                        $('#my-dropzone-68 > :nth-child(2)').show();
                    });
                }            
            }
             Dropzone.options.myDropzone88 = {
                init: function() {
                    //to prevent more than 1 file upload
                    this.on("maxfilesexceeded", function(file) { 
                        this.removeAllFiles();
                        this.addFile(file);
                    });
                    this.on("addedfile", function(file) {
                        Metronic.startPageLoading();
                        setTimeout(function() {
                          
                            $("#picture_88").val(file.name);
                            setTimeout(function() {

                                var valImage88 = $("#my-dropzone-88").children().find('img').attr("src");
                                if (typeof valImage88 != "undefined") {
                                    valImage88 = valImage88.split("base64,");
                                    $("#picture_byte_88").val(valImage88[1]);
                                }
                                var hasPreview68 = $("#my-dropzone-88").children().length;
                                if(hasPreview68 == 3) {
                                    $('#my-dropzone-88 > :nth-child(2)').hide();
                                } 
                                if(($("#picture_byte_88").val())==0) {
                                    $("#picture_88").val("");
                                }
                                Metronic.stopPageLoading();
                            }, 200);   
                        }, 200);
                         $("#save_changes").attr("disabled",false);
                         $("#reset_change").attr("disabled",false)
                    });
                    this.on("removedfile", function(file) { 
         
                        $("#picture_88").val("");
                        $("#picture_byte_88").val("");
                        $("#save_changes").attr("disabled",false);
                        $("#reset_change").attr("disabled",false);
                        $('#my-dropzone-88 > :nth-child(2)').show();
                    });
                }            
            }
            
        }
    };
}();