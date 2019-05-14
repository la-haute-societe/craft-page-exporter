/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./js/CraftpageexporterExportModal.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./js/CraftpageexporterExportModal.js":
/*!********************************************!*\
  !*** ./js/CraftpageexporterExportModal.js ***!
  \********************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_GarnishExportModal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/GarnishExportModal */ "./js/components/GarnishExportModal.js");
/* harmony import */ var _components_GarnishExportModal__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_components_GarnishExportModal__WEBPACK_IMPORTED_MODULE_0__);


/***/ }),

/***/ "./js/components/GarnishExportModal.js":
/*!*********************************************!*\
  !*** ./js/components/GarnishExportModal.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/** global: Craft */

/** global: Garnish */

/** global: $ */

/**
 * Craftpageexporter Export Modal
 */
Craft.CraftpageexporterExportModal = Garnish.Modal.extend({
  $spinner: null,
  requestId: 0,

  /**
   * Initialize the modal
   * entryIds is a comma separated list of the entry ids to export
   *
   * @string entryIds
   */
  init: function init(entryIds) {
    Craft.CraftpageexporterExportModal.openInstance = this;
    var settings = {};
    settings.onHide = this._onHide.bind(this);
    this.$container = $('<div id="select-fields-modal" class="modal loading"/>').appendTo(Garnish.$bod);
    this.base(this.$container, $.extend({
      resizable: false
    }, settings)); // Cut the flicker, just show the nice person the preview.

    if (this.$container) {
      this.$container.velocity('stop');
      this.$container.show().css('opacity', 1);
      this.$shade.velocity('stop');
      this.$shade.show().css('opacity', 1);
    }

    this.loadModalContent(entryIds);
  },
  loadModalContent: function loadModalContent(entryIds) {
    this._initSpinner();

    this.requestId++;
    Craft.postActionRequest('craft-page-exporter/default/get-export-modal-content', {
      entryIds: entryIds,
      requestId: this.requestId
    }, function (response, textStatus) {
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
  _initSpinner: function _initSpinner() {
    this.$container.addClass('loading');
    this.$spinner = $('<div class="spinner centeralign"></div>').appendTo(this.$container);
    var top = this.$container.height() / 2 - this.$spinner.height() / 2 + 'px',
        left = this.$container.width() / 2 - this.$spinner.width() / 2 + 'px';
    this.$spinner.css({
      left: left,
      top: top,
      position: 'absolute'
    });
  },
  _onHide: function _onHide() {
    Craft.CraftpageexporterExportModal.openInstance = null;
    this.$shade.remove();
    return this.destroy();
  }
}, {
  defaultSettings: {}
});

/***/ })

/******/ });
//# sourceMappingURL=CraftpageexporterExportModal.js.map