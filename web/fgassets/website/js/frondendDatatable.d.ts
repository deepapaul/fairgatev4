/// <reference path="Typescript/jquery.d.ts" />
/// <reference path="Typescript/underscore.d.ts" />
/// <reference path="Typescript/jqueryui.d.ts" />
declare var listTable: any, fc: any, jsonData: any;
declare var FgDatatable: {
    listdataTableInit: (tableId: any, options: any) => any;
    datatableSearch: () => void;
    getSettings: () => any;
    setNewValues: (newsettings: any) => void;
};
declare function manipulatememberColumnFields(json: any): void;
declare function manipulateDocumentColumnFields(json: any): void;
declare function manipulateforumdata(json: any): void;
declare function manipulateCMSPageList(json: any): void;
declare function nl2br(str: any, is_xhtml: any): string;
