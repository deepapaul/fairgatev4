var FgPasswordValidate = (function () {
    function FgPasswordValidate() {
    }
    FgPasswordValidate.prototype.validate = function (password) {
        var statusArray = [];
        var msgArray = [];
        if (typeof password == 'undefined' || password.length < 8 || password.length > 25) {
            statusArray.push(false);
            msgArray.push('Password length should be between 8-25');
        }
        if (!this.checkAllCharsExist(password)) {
            statusArray.push(false);
            msgArray.push('Contains at least 1 lower case letter and 1 upper case letter (all UTF-8), at least 1 number and at least 1 special character (punctuation)');
        }
        if (this.checkIdenticalChars(password)) {
            statusArray.push(false);
            msgArray.push('Not more than 2 identical characters in a row (e.g., 111 not allowed)');
        }
        if (this.checkSequenceAlphabets(password)) {
            statusArray.push(false);
            msgArray.push('Not any sequence of the English alphabet (above 3 letters)');
        }
        if (statusArray.indexOf(false) >= 0) {
            var valid = false;
        }
        else {
            var valid = true;
        }
        return { status: valid, message: msgArray };
    };
    FgPasswordValidate.prototype.checkIdenticalChars = function (str) {
        var re = new RegExp(/^(.)*(([\S+])\3\3)(.)*$/);
        if (re.test(str)) {
            return true;
        }
        else {
            return false;
        }
    };
    FgPasswordValidate.prototype.checkAllCharsExist = function (str) {
        var re = XRegExp("(?=.*?[\\p{Lu}])(?=.*?[\\p{Ll}])(?=.*[0-9])(?=.*[!@#$%^&*()_+~{}:?><;.,])");
        if (re.test(str)) {
            return true;
        }
        else {
            return false;
        }
    };
    FgPasswordValidate.prototype.checkSequenceAlphabets = function (str) {
        var re = new RegExp(/(abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz|012|123|234|345|456|567|678|789)+/i);
        if (re.test(str)) {
            return true;
        }
        else {
            return false;
        }
    };
    return FgPasswordValidate;
}());
