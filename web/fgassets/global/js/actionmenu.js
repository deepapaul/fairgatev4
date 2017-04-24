/*==============================================
 * Action menu Script in Angular JS
 * Author : Sebin
 ================================================*/
window.actionMenuTextDraft = []   //declaring global object for 


/** 
 * -------------------------------------------------
 * @Create common app module for Internal pages 
 * -------------------------------------------------
 */
var app = angular.module('fairgate', []);


/*==================================================================================================
 =           Change Angular delimiter to fix TWIG {{}} issue
 ===================================================================================================*/

app.config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{');
    $interpolateProvider.endSymbol('}]}');
});

/*==================================================================================================
 =           Angular Base Controller
 ===================================================================================================*/


app.controller('BaseController', ['$scope', function ($scope) {

        //=====  Register Global Menu Json to a variable ======		
        $scope.menuType = 0;      //Menu type :- none=0/ Single=1/ Multiple=2
        $scope.menuContent = window.actionMenuTextDraft; // Getting value from page

        /*==================================================================================================
         =            Angular Function for check provideed variable is undefiend or not   =
         ===================================================================================================*/


        angular.isUndefinedOrNull = function (val) {
            return angular.isUndefined(val) || val === null
        }

        /*=============================================================================
         =            Angular Function for watch changes in Menu Json array            =
         =============================================================================*/

        $scope.$watch('menuContent', function (newVal, oldVal) {
            $scope.processMenu(newVal);
        }, true);

        $scope.$watch('menuType', function () {
            $scope.processMenu($scope.menuContent);
        }, true);
        $scope.$watch('menuFilteredData', function () {
            $scope.processMenu($scope.menuContent);
        }, true);
        /*=====  End of Angular Function for watch changes in Menu Json array  ======*/



        /*==================================================================================
         =            Function for proccessing elements in action menu drop down            =
         ==================================================================================*/

        $scope.processMenu = function (menuData) {

            var noMenuData = angular.isUndefined(menuData.active);
            var i = 0;
            if (!noMenuData) {
                if ($scope.menuType === 0) {
                    angular.forEach(menuData.active.none, function (value, key) {
                        menuData.active.none[key].sortOrder = i++;
                        menuData.active.none[key].KeyName = key;
                    });
                    $scope.menuFilteredData = menuData.active.none;
                    $scope.keyName = Object.keys(menuData.active.none);
                } else if ($scope.menuType === 1) {
                    angular.forEach(menuData.active.single, function (value, key) {
                        menuData.active.single[key].sortOrder = i++;
                        menuData.active.single[key].KeyName = key;
                    });
                    $scope.menuFilteredData = menuData.active.single;
                    $scope.keyName = Object.keys(menuData.active.single);

                } else if ($scope.menuType === 2) {
                    angular.forEach(menuData.active.multiple, function (value, key) {
                        menuData.active.multiple[key].sortOrder = i++;
                        menuData.active.multiple[key].KeyName = key;
                    });

                    $scope.menuFilteredData = menuData.active.multiple;
                    $scope.keyName = Object.keys(menuData.active.multiple);
                }
                /*>>>>>> preventing default sorting issue <<<<*/
                $scope.dataArray = Object.keys($scope.menuFilteredData)
                        .map(function (key) {
                            return $scope.menuFilteredData[key];
                        });
            }
        };
        //funtion for preveint default sorting - currently  not in use
        $scope.notSorted = function (obj) {
            if (!obj) {
                return [];
            }
            return Object.keys(obj);
        };
        /*=====  End of Function for proccessing elements in action menu drop down  ======*/

    }]);

angular.module('myFilters', [])
        .filter('keys', function () {
            return function (input) {
                if (!input) {
                    return [];
                }
                return Object.keys(input);
            }
        });

