// FGTemplate 
var FGTemplate = (function() {

    var $lstTemplates = $('#divTemplates').children();

    function bindTemplate(templateId, jsnData) {

        if (!$lstTemplates || !$lstTemplates.length) {
            return '';
        }

        var txtTemplate = getTemplate(templateId); // Gets the requested template html. 

        var templateCompiled = _.template(txtTemplate); // Pre-compiles the template. 

        var htmlFinal = templateCompiled(jsnData); // Binds the template with json data. 

        return htmlFinal;
    }

    function getTemplate(templateId) {

        var txtTemplate = $lstTemplates.map(function() {
            if (this.id == templateId) {
                return $(this).html();
            }
        }).get(0);

        return txtTemplate;
    }

    return {
        get: function(templateId) {
            return getTemplate(templateId);
        },
        bind: function(templateId, jsnData) {
            return bindTemplate(templateId, jsnData);
        }
    };

}());
