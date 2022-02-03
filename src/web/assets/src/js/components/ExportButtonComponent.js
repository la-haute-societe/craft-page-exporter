export default class ExportButtonComponent {
    init() {
        // Get element we want to insert btn after
        let btnShare = document.querySelector('.btn.sharebtn, #share-btn');
        if (btnShare === null) return;

        // Create btn group
        let btnGroup = this.createBtnGroup();

        // Add btn
        this.insertAfter(btnGroup, btnShare.parentNode);

        // Add menu
        this.addMenu(btnGroup);
    }

    createBtnGroup() {
        let btnGroup = document.createElement('div');
        btnGroup.classList.add('btngroup', 'btngroup--share');

        let btnExport = document.createElement('a');
        btnExport.classList.add('btn', 'export');
        btnExport.innerHTML = "Export";
        btnExport.href      = this.getBtnExportLink();

        btnGroup.appendChild(btnExport);

        return btnGroup;
    }

    addMenu(btnGroup) {
        let btnMore = document.createElement('div');
        btnMore.classList.add('btn', 'menubtn');
        btnGroup.appendChild(btnMore);

        let menu = document.createElement('div');
        menu.classList.add('menu');

        let list = document.createElement('ul');
        menu.appendChild(list);

        let listItem = document.createElement('li');
        list.appendChild(listItem);

        let btnCustomExport       = document.createElement('a');
        btnCustomExport.innerHTML = "Custom export";
        btnCustomExport.addEventListener('click', this.onBtnExportClick.bind(this));
        listItem.appendChild(btnCustomExport);

        this.insertAfter(menu, btnMore);

        new Garnish.MenuBtn(btnMore);
    }

    onBtnExportClick() {
        new Craft.CraftpageexporterExportModal(this.getEntryId());
    }

    getBtnExportLink() {
        let url = Craft.getUrl(`page-exporter/export/entry-${this.getEntryId()}/site-${this.getSiteId()}`);
        return url.replace('admin/', '');
    }

    getEntryId() {
        let entryIdInput = document.getElementsByName('sourceId')[0];
        return entryIdInput.value;
    }

    getSiteId() {
        let siteIdInput = $("input[name=siteId]")[0];

        if (siteIdInput) {
            return siteIdInput.value;
        }
        // let siteId = $('.menu li>a.sel').data('site-id');

        return Craft.siteId;
    }

    insertAfter(el, referenceNode) {
        console.log('Insert before', referenceNode);
        referenceNode.parentNode.insertBefore(el, referenceNode.nextSibling);
    }
}
