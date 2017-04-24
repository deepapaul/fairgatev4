declare class Fgcmspage {
    options: any;
    settings: any;
    sortSetting: any;
    defaultSettings: any;
    defaultSortOptions: any;
    constructor(options: any);
    initSettings(): void;
    renderContainerBox(containerId: any): any;
    renderColumnBox(columnId: any): any;
    renderBox(boxId: any): any;
    renderElement(elementId: any): any;
    renderPage(jsonData: any): any;
    pageContainer(): string;
    containerColumns(): string;
    columnBox(): string;
    elementBox(): string;
    sidebarInit(): string;
    contentInit(): string;
    pageInit(): void;
    appendContent(pageContent: any): void;
    sortableEvent(identifier: string, sortoptions: any): void;
    columnWidthCalculation(currentContainer: any): void;
    createContainer(pageId: any, containerId: any): void;
    hideRemoveButton(): void;
    getMaxColumnCount(): number;
}
