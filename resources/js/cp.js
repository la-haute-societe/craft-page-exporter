class ShareButtonComponent {
    init() {
        // Get element we want to insert btn after
        let btnShare = document.querySelector('.btn.sharebtn');

        // Create btn
        let el = this.createBtnExport();

        // Add btn
        this.insertAfter(el, btnShare);
    }

    createBtnExport() {
        let btnExport = document.createElement('a');
        btnExport.classList.add('btn');
        btnExport.innerHTML = "Export";
        btnExport.href = this.getBtnExportLink();
        return btnExport;
    }

    getBtnExportLink() {
        var url = Craft.getUrl(`page-exporter/export/entry-${this.getEntryId()}/site-${this.getSiteId()}`);
        return url.replace('admin/', '');
    }

    getEntryId() {
        let entryIdInput = document.getElementsByName('entryId')[0];
        return entryIdInput.value;
    }

    getSiteId() {
        // let siteIdInput = document.getElementsByName('siteId')[0];
        // return siteIdInput.value;
        return Craft.siteId;
    }


    insertAfter(el, referenceNode) {
        referenceNode.parentNode.insertBefore(el, referenceNode.nextSibling);
    }
}

let shareButtonComponent = new ShareButtonComponent();
shareButtonComponent.init();
