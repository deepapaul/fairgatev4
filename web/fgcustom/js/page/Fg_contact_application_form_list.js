var _this;
var FgXmlHttp = FgXmlHttp;
var FgConatctApplicationFormList = (function () {
    function FgConatctApplicationFormList() {
        this.appFormDuplicatePath = '';
        this.appFormDeletePath = '';
        this.appFormActivatePath = '';
        _this = this;
    }
    FgConatctApplicationFormList.prototype.init = function () {
        this.handleClickEvent();
    };
    FgConatctApplicationFormList.prototype.handleClickEvent = function () {
        $(document).off('click', '.fg_dev_form_activate');
        $(document).on('click', '.fg_dev_form_activate', function () {
            var formId = $(this).attr('data-formid');
            FgXmlHttp.post(_this.appFormActivatePath, { 'formId': formId }, false, function (data) {
                if (data.dataArray.stage == 'stage3' && data.dataArray.isActive == 1) {
                    $('li#Fg_form_list_li_' + data.dataArray.formId + ' a.fg-app-form-link ').removeClass('hide');
                }
                else {
                    $('li#Fg_form_list_li_' + data.dataArray.formId + ' a.fg-app-form-link').addClass('hide');
                }
            });
        });
        $(document).off('click', '.fg_app_form_delete_wrapper');
        $(document).on('click', '.fg_app_form_delete_wrapper', function () {
            var formId = $(this).find('input.fg_contact_app_form_delete').val();
            $('#formIdHidden').val(formId);
            $('#deleteFormPopup').modal('show');
        });
        $(document).off('click', '#delete_form');
        $(document).on('click', '#delete_form', function () {
            var formId = $('#formIdHidden').val();
            FgXmlHttp.post(_this.appFormDeletePath, { 'formId': formId }, false, function () {
                $('#deleteFormPopup').modal('hide');
                $('li#Fg_form_list_li_' + formId).remove();
            });
        });
        $(document).off('click', '.fg-dev-form-duplicate');
        $(document).on('click', '.fg-dev-form-duplicate', function () {
            var formId = $(this).attr('data-formId');
            FgXmlHttp.post(_this.appFormDuplicatePath, { 'formId': formId }, false, function () {
                Breadcrumb.load();
            });
        });
    };
    return FgConatctApplicationFormList;
}());
