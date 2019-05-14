export default class ShareButtonComponent {
  init() {
    // Get element we want to insert btn after
    let btnShare = document.querySelector('.btn.sharebtn');
    if (btnShare === null) return;

    // Create btn group
    let btnGroup = this.createBtnGroup();

    // Add btn
    this.insertAfter(btnGroup, btnShare);

    // Add menu
    this.addMenu(btnGroup);
  }

  createBtnGroup() {
    let btnGroup = document.createElement('div');
    btnGroup.classList.add('btngroup', 'btngroup--share');

    let btnExport = document.createElement('a');
    btnExport.classList.add('btn', 'export');
    btnExport.innerHTML = "Export";
    btnExport.href = this.getBtnExportLink();

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

    let btnCustomExport = document.createElement('a');
    btnCustomExport.innerHTML = "Custom export";
    btnCustomExport.addEventListener('click', this.onBtnExportClick.bind(this));
    listItem.appendChild(btnCustomExport);

    this.insertAfter(menu, btnMore);

    new Garnish.MenuBtn(btnMore);
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

  getSiteId() {
    return Craft.siteId;
  }

  insertAfter(el, referenceNode) {
    referenceNode.parentNode.insertBefore(el, referenceNode.nextSibling);
  }
}