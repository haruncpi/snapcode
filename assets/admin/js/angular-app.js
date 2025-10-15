/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/admin/src/utils.js":
/*!***********************************!*\
  !*** ./assets/admin/src/utils.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, exports) => {

exports.compareVersion = function (v1, comparator, v2) {
  "use strict";

  var comparator = comparator == "=" ? "==" : comparator;
  if (["==", "===", "<", "<=", ">", ">=", "!=", "!=="].indexOf(comparator) == -1) {
    throw new Error("Invalid comparator. " + comparator);
  }
  var v1parts = v1.split("."),
    v2parts = v2.split(".");
  var maxLen = Math.max(v1parts.length, v2parts.length);
  var part1, part2;
  var cmp = 0;
  for (var i = 0; i < maxLen && !cmp; i++) {
    part1 = parseInt(v1parts[i], 10) || 0;
    part2 = parseInt(v2parts[i], 10) || 0;
    if (part1 < part2) cmp = 1;
    if (part1 > part2) cmp = -1;
  }
  return eval("0" + comparator + cmp);
};
exports.textToJSON = function (text) {
  const jsonObject = {};
  const lines = text.split("\n");
  lines.forEach(line => {
    const [key, value] = line.split(/:(.*)/s).map(item => item.trim());
    if (key) {
      jsonObject[key] = isNaN(value) ? value : Number(value);
    }
  });
  return jsonObject;
};
exports.isValidJSON = function (str) {
  try {
    JSON.parse(str);
    return true;
  } catch (e) {
    return false;
  }
};
exports.toFormData = function (obj) {
  let formData = new FormData();
  for (let [key, val] of Object.entries(obj)) {
    formData.append(key, val);
  }
  return formData;
};
exports.prettySql = function (query) {
  if (!query) return "";
  const strings = [];
  let sql = query.replace(/'([^'\\]|\\.)*'/g, m => {
    strings.push(m);
    return `__STR${strings.length - 1}__`;
  });
  const KEYWORDS = ["SELECT", "FROM", "WHERE", "GROUP BY", "ORDER BY", "HAVING", "LIMIT", "OFFSET", "JOIN", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN", "FULL JOIN", "CROSS JOIN", "ON", "UNION", "UNION ALL", "INTERSECT", "EXCEPT"];
  const kwPattern = new RegExp("(?<![\\w_])(" + KEYWORDS.map(k => k.replace(/\\s+/g, "\\s+")).join("|") + ")(?![\\w_])", "gi");
  sql = sql.replace(/\s+/g, " ").replace(kwPattern, "\n$1").replace(/\n{2,}/g, "\n").trim();
  sql = sql.split("\n").map(line => {
    const t = line.trim();
    if (/^(AND|OR|ON)\b/i.test(t)) return " " + t;
    return t;
  }).join("\n");
  sql = sql.replace(/__STR(\d+)__/g, (_, i) => strings[Number(i)]);
  return sql;
};

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*****************************************!*\
  !*** ./assets/admin/src/angular-app.js ***!
  \*****************************************/
const {
  compareVersion,
  toFormData,
  prettySql
} = __webpack_require__(/*! ./utils */ "./assets/admin/src/utils.js");
let myApp = angular.module("myApp", []);
myApp.controller("AppCtrl", function ($scope, $http, $sce) {
  const {
    nonceKey,
    nonceValue
  } = _snapcode;
  $scope.output = "";
  $scope.tab = "output";
  $scope.processing = false;
  $scope.phpPath = "";
  const snapcodeSavedData = localStorage.getItem("snapcode") ? JSON.parse(localStorage.getItem("snapcode")) : {};
  $scope.model = {
    theme: snapcodeSavedData.theme ? snapcodeSavedData.theme : "ace/theme/clouds",
    code: snapcodeSavedData.code ? snapcodeSavedData.code : "new WP_User(1)"
  };
  const editor = ace.edit("editor");
  const beautify = ace.require("ace/ext/beautify");
  const langTools = ace.require("ace/ext/language_tools");
  const themeList = ace.require("ace/ext/themelist").themes;
  $scope.model.themes = themeList;
  $scope.screenMode = localStorage.getItem("screenMode") ? localStorage.getItem("screenMode") === 'horizontal' ? 'horizontal' : 'vertical' : 'vertical';
  $scope.toggleScreenMode = function () {
    document.startViewTransition(function () {
      $scope.screenMode = $scope.screenMode === 'horizontal' ? 'vertical' : 'horizontal';
      localStorage.setItem("screenMode", $scope.screenMode);
      $scope.$apply();
    });
  };
  editor.container.classList.add("snapcode_editor");
  editor.setShowPrintMargin(false);
  editor.setOptions({
    fontFamily: "FiraCode",
    fontSize: "12pt"
    // enableBasicAutocompletion: true,
    // enableLiveAutocompletion: true,
    // enableSnippets: true
  });
  editor.setTheme($scope.model.theme);
  editor.session.setMode({
    path: "ace/mode/php",
    inline: true
  });
  editor.setValue($scope.model.code);
  editor.navigateFileEnd();
  // setTimeout(() => beautify.beautify(editor.session), 100)

  $scope.changeTheme = function (theme) {
    editor.setTheme(theme);
  };
  Object.toparams = function ObjecttoParams(obj) {
    var p = [];
    for (var key in obj) {
      p.push(key + "=" + encodeURIComponent(obj[key]));
    }
    return p.join("&");
  };
  $scope.getOutput = getOutput;
  $scope.prettySql = prettySql;
  $scope.copy = copyToClipboard;
  $scope.queries = [];
  $scope.listenEvent = function ($event) {
    if (($event.ctrlKey || $event.metaKey) && $event.keyCode === 13) {
      $scope.getOutput();
    }
  };
  document.addEventListener("keydown", $scope.listenEvent);
  $scope.setTab = function (tab) {
    $scope.tab = tab;
  };
  function copyToClipboard(text, e) {
    let el = e.target;
    let dummy = document.createElement("textarea");
    document.body.appendChild(dummy);
    dummy.value = text;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
    el.innerHTML = "Copied!";
    el.style.backgroundColor = "green";
    el.style.color = "white";
    setTimeout(() => {
      el.innerHTML = "Copy";
      el.removeAttribute("style");
    }, 1000);
  }
  $scope.openInfo = function () {
    let url = "#TB_inline?width=400&height=175&inlineId=snapcode-info";
    tb_show("SnapCode", url, false);
  };
  $scope.saving = false;
  $scope.saveSettings = function (settings) {
    $scope.saving = true;
    let payload = {
      [nonceKey]: nonceValue,
      action: "snapcode_save_settings",
      settings: JSON.stringify(settings)
    };
    let config = {
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      }
    };
    $http.post(_snapcode.ajaxUrl, Object.toparams(payload), config).success(function (res) {
      $scope.saving = false;
      tb_remove();
    });
  };
  function getOutput() {
    const code = editor.getSelectedText() || editor.getValue();
    let payload = {
      [nonceKey]: nonceValue,
      action: "snapcode_output",
      code: code
    };
    let config = {
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      }
    };
    $scope.processing = true;
    localStorage.setItem("snapcode", JSON.stringify({
      code: code
    }));
    $http.post(_snapcode.ajaxUrl, Object.toparams(payload), config).success(function (res) {
      $scope.outputResponse = res;
      $scope.output = res.success ? res.data : $sce.trustAsHtml(res.message);
      $scope.queries = res.queries;
      $scope.tab = "output";
      $scope.processing = false;
    });
  }

  // save to local storage for full screen named snapcode-full-screen

  $scope.isFullScreen = localStorage.getItem("snapcode-full-screen") === "true";
  let fullScreenStyle = `
      #adminmenumain, #wpfooter, .notice, #tutor-page-wrap { display: none !important; }
      #wpcontent { margin: 0 !important; padding: 0 !important; }
      #wpbody-content { padding-bottom: 0px !important; float: none; }
      #wpadminbar { display: none !important; }
      html { padding-top: 0 !important; }
  `;
  function maximizeScreen() {
    let head = document.getElementsByTagName("head")[0];
    let style = document.createElement("style");
    style.id = "full-screen-style";
    style.innerHTML = fullScreenStyle;
    head.appendChild(style);
  }
  function minimizeScreen() {
    let style = document.getElementById("full-screen-style");
    if (style) {
      style.parentNode.removeChild(style);
    }
  }
  console.log($scope.isFullScreen);
  $scope.isFullScreen ? maximizeScreen() : minimizeScreen();

  // rewrite toggle full screen based on isFullScreen toggle the screen accordingly
  $scope.toggleFullScreen = function () {
    if ($scope.isFullScreen) {
      minimizeScreen();
    } else {
      maximizeScreen();
    }
    $scope.isFullScreen = !$scope.isFullScreen;
    localStorage.setItem("snapcode-full-screen", $scope.isFullScreen);
  };

  /**
   * Update plugin
   */
  $scope.pluginInfo = {
    updateUrl: _snapcode.updateUrl,
    currentVersion: _snapcode.version,
    newVersion: null,
    updateAvailable: false
  };
  $scope.checkUpdate = function () {
    $http.get($scope.pluginInfo.updateUrl).success(function (res) {
      let newVersion = res.version;
      $scope.pluginInfo.newVersion = newVersion;
      $scope.pluginInfo.updateAvailable = compareVersion($scope.pluginInfo.currentVersion, "<", newVersion);
      // $scope.pluginInfo.updateAvailable = true;
    });
  };
  $scope.checkUpdate();
  $scope.updating = false;
  $scope.updatePlugin = function () {
    let data = {
      plugin: "snapcode/snapcode.php",
      slug: "snapcode",
      action: "update-plugin",
      _ajax_nonce: _snapcode.pluginUpdateNonce
    };
    let config = {
      transformRequest: angular.identity,
      headers: {
        "Content-Type": undefined
      }
    };
    $scope.updating = true;
    $http.post(_snapcode.ajaxUrl, toFormData(data), config).success(function (res) {
      $scope.updating = false;
      if (res.success) {
        window.location.reload();
      } else {
        alert(res.data.errorMessage);
      }
    });
  };
  // End plugin update.
});
})();

/******/ })()
;
//# sourceMappingURL=angular-app.js.map