/** global: Craft */
/** global: Garnish */
/** global: $ */

/**
 * Craftpageexporter Export Modal
 */
Craft.CraftpageexporterExportModal = Garnish.Modal.extend(
  {
    $spinner: null,
    requestId: 0,

    /**
     * Initialize the modal
     * entryIds is a comma separated list of the entry ids to export
     *
     * @string entryIds
     */
    init: function(entryIds) {
      Craft.CraftpageexporterExportModal.openInstance = this;
      let settings = {};
      settings.onHide = this._onHide.bind(this);
      this.$container = $('<div id="select-fields-modal" class="modal loading"/>').appendTo(Garnish.$bod);
      this.base(this.$container, $.extend({
        resizable: false
      }, settings));

      // Cut the flicker, just show the nice person the preview.
      if (this.$container) {
        this.$container.velocity('stop');
        this.$container.show().css('opacity', 1);

        this.$shade.velocity('stop');
        this.$shade.show().css('opacity', 1);
      }
      this.loadModalContent(entryIds);
    },

    loadModalContent: function(entryIds) {
      this._initSpinner();
      this.requestId++;

      Craft.postActionRequest('craft-page-exporter/default/get-export-modal-content', {entryIds: entryIds, requestId: this.requestId}, function(response, textStatus) {
        if (textStatus === 'success') {
          if (response.success) {
            if (response.requestId != this.requestId) {
              return;
            }
            this.$container.removeClass('loading');
            this.$spinner.remove();
            this.loaded = true;
            this.$container.append(response.modalHtml);
            Craft.initUiElements(this.$container);
          } else {
            alert(response.error);
            this.hide();
          }
        }
      }.bind(this));
    },


    _initSpinner: function () {
      this.$container.addClass('loading');
      this.$spinner = $('<div class="spinner centeralign"></div>').appendTo(this.$container);
      var top = (this.$container.height() / 2 - this.$spinner.height() / 2) + 'px',
        left = (this.$container.width() / 2 - this.$spinner.width() / 2) + 'px';

      this.$spinner.css({left: left, top: top, position: 'absolute'});
    },

    _onHide: function () {
      Craft.CraftpageexporterExportModal.openInstance = null;
      this.$shade.remove();
      return this.destroy();
    },
  },
  {
    defaultSettings: {}
  }
);
