declare var setMegaMenuActive: () => void;
declare var FgInternalDragAndDrop: {
    init: (sortDiv: any) => void;
    sortWithOrderUpdation: (sortDiv: any, doChildSort: any) => void;
    resetChanges: (resetSections: any) => void;
};
declare var handleDragAndDrop: (sortDiv: any, doChildSort: any, sortCheck: any, inputId: any, updateSortOrder: any) => void;
declare function doSortOrderUpdation(parentElement: any): void;
