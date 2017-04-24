var FgValidateFilter = function () {
    var checkIfExist, firstInput, secondInput, fieldId, fieldType, dataType, filteredMasterJson, criteriaOption, criteriaType;
    var iterateFilterCriterias = function (masterJson, filterJson, filterType) {
        var validateFlag = true;
        var filterJsonData = (filterType == 'sponsor') ? filterJson.sponsor_filter : filterJson.contact_filter;
        _.map(filterJsonData, function (value) {
            validateFlag = (validateFlag) ? validateEachFilterCriteria(masterJson, value) : false;
        });

        return validateFlag;
    }

    var validateEachFilterCriteria = function (masterJson, filterCriteria) {

        fieldId = filterCriteria.entry;
        fieldType = filterCriteria.type;
        dataType = filterCriteria.data_type;
        firstInput = filterCriteria.input1;
        //firstInput = ($.isNumeric(firstInput))?parseInt(firstInput):firstInput; 
        secondInput = filterCriteria.input2;
        //secondInput = ($.isNumeric(secondInput))?parseInt(secondInput):secondInput; 
        filteredMasterJson = _.where(masterJson, {id: fieldType});
        try {
            criteriaOption = _.where(filteredMasterJson[0].entry, {id: fieldId});
        } catch (err) {
            return false;
        }
        if (!validateCriteriaOption(criteriaOption)) {
            return false;
        }
        criteriaType = fieldType.split("-")[0];
        switch (criteriaType) {
            case 'CF':
                return validateContactField();
                break;
            case 'CC':
                return validateContactConnection();
                break;
            case 'CO':
                return validateContactOption();
                break;
            case 'SS':
                return validateSponsorOption();
                break;
            case 'CM':
            case 'FM':
                return validateContactOption();
                break;
            case 'FROLES':
            case 'ROLES':
            case 'FILTERROLES':
            case 'TEAM':
            case 'WORKGROUP':
                return validateRoles();
                break;
            default:
                return validateInputOption(criteriaOption, firstInput);
        }
    }

    var validateCriteriaOption = function (criteriaOption) {
        checkIfExist = (criteriaOption.length) ? true : false;

        return checkIfExist;
    }

    var validateInputOption = function (criteria, inputValue) {
        if (dataType == 'select') {
            checkIfExist = (_.where(criteria[0].input, {id: inputValue}).length && criteria[0].type == 'select' && inputValue) ? true : false;
        } else {
            checkIfExist = true;
        }

        return checkIfExist;
    }

    var validateRoles = function () {
        checkIfExist = false;
        if (firstInput == "any") {
            if (criteriaOption[0].input.length > 0) {
                checkIfExist = true;
            } else {
                checkIfExist = false;
            }
        } else {
            if (validateInputOption(criteriaOption, firstInput) && typeof secondInput !== 'undefined') {
                if (secondInput == "any") {
                    checkIfExist = true;
                } else {
                    var input1Option = _.where(criteriaOption[0].input, {id: firstInput});
                    checkIfExist = validateInputOption(input1Option, secondInput);
                }
            }
        }

        return checkIfExist;
    }

    /*validation for sponsor services option*/
    var validateSponsorOption = function () {
        checkIfExist = false;
        checkIfExist = (_.where(criteriaOption[0].input, {id: firstInput}).length && criteriaOption[0].type == 'assignments' && firstInput) ? true : false;

        return checkIfExist;
    }


    var validateContactOption = function () {
        firstInput = ($.isNumeric(firstInput)) ? parseInt(firstInput) : firstInput;

        return validateInputOption(criteriaOption, firstInput)
    }

    var validateContactConnection = function () {
        if (fieldId != 'household_relation' && fieldId != 'other_relation') {
            firstInput = ($.isNumeric(firstInput)) ? parseInt(firstInput) : firstInput;
        }

        return validateInputOption(criteriaOption, firstInput);
    }

    var validateContactField = function () {

        return validateInputOption(criteriaOption, firstInput);
    }

    return {
        init: function (masterJson, filterJson, filterType) {
            var validateFlag = iterateFilterCriterias(masterJson, filterJson, filterType);

            return validateFlag;
        }
    };
}();

							