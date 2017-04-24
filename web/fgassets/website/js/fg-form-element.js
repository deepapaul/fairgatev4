var FgFormElement = (function () {
    function FgFormElement(defaultLang) {
        this.defaultLang = defaultLang;
        this.initJs();
    }
    FgFormElement.prototype.initJs = function () {
        var thisObj = this;
        $("#addNewEle").click(function () {
            thisObj.structureArr = JSON.parse(atob($('#objStructure').val()));
            thisObj.structureArr['meta']['elementsCount'] = parseInt($('#elementsCount').val());
            $('#formFields').append(FGTemplate.bind('newFormElement', { 'structureArr': thisObj.structureArr }));
            thisObj.structureArr['data']['fieldType'] = 'singleline';
            $("#typeOptionsForm" + $('#elementsCount').val()).html(FGTemplate.bind('singleline', { 'structureArr': thisObj.structureArr }));
            $("#selectType" + $('#elementsCount').val()).change(function () {
                thisObj.structureArray = JSON.parse(atob($('#objStructure').val()));
                thisObj.structureArray['data']['fieldType'] = this.value;
                thisObj.structureArray['meta']['elementsCount'] = $('#elementsCount').val();
                var formData = $("#typeOptionsForm" + $(this).attr('data-count')).serializeArray();
                $.each(formData, function (index, obj) {
                    var key = obj.name;
                    if (key == 'placeholder' || key == 'tooltip' || key == 'label') {
                        thisObj.structureArray['data'][key][thisObj.defaultLang] = obj.value;
                    }
                    else {
                        thisObj.structureArray['data'][key] = obj.value;
                    }
                });
                $("#typeOptionsForm" + $(this).attr('data-count')).html(FGTemplate.bind($(this).val(), { 'structureArr': thisObj.structureArray }));
            });
            var newElementCount = parseInt($('#elementsCount').val()) + parseInt('1');
            $('#elementsCount').val(newElementCount);
        });
    };
    return FgFormElement;
}());
//# sourceMappingURL=fg-form-element.js.map