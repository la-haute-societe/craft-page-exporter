export default class ShareButtonComponent {
  init() {
    // Get element we want to insert btn after
    let btnShare = document.querySelector('.btn.sharebtn');
    if (btnShare === null) return;

    // Create btn
    let el = this.createBtnExport();

    // Add btn
    this.insertAfter(el, btnShare);
  }

  createBtnExport() {
    let btnExport = document.createElement('a');
    btnExport.classList.add('btn');
    btnExport.innerHTML = "Export";
    btnExport.addEventListener('click', this.onBtnExportClick.bind(this));
    return btnExport;
  }

  onBtnExportClick() {
    var modal = new Craft.CraftpageexporterExportModal(this.getEntryId());
  }

  getBtnExportLink() {
    var url = Craft.getUrl(`page-exporter/export/entry-${this.getEntryId()}/site-${this.getSiteId()}`);
    return url.replace('admin/', '');
  }

  getEntryId() {
    let entryIdInput = document.getElementsByName('entryId')[0];
    return entryIdInput.value;
  }

  insertAfter(el, referenceNode) {
    referenceNode.parentNode.insertBefore(el, referenceNode.nextSibling);
  }
}