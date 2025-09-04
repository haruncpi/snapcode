let myApp = angular.module("myApp", []);

myApp.controller("AppCtrl", function ($scope, $http) {
  const { nonceKey, nonceValue, ajaxUrl } = _snapcode;

  $scope.output = "";
  $scope.tab = "output";
  $scope.processing = false;
  $scope.phpPath = "";

  const snapcodeSavedData = localStorage.getItem("snapcode")
    ? JSON.parse(localStorage.getItem("snapcode"))
    : {};
  $scope.model = {
    theme: snapcodeSavedData.theme
      ? snapcodeSavedData.theme
      : "ace/theme/clouds",
    code: snapcodeSavedData.code ? snapcodeSavedData.code : "new WP_User(1)",
  };

  const editor = ace.edit("editor");
  const beautify = ace.require("ace/ext/beautify");
  const langTools = ace.require("ace/ext/language_tools");
  const themeList = ace.require("ace/ext/themelist").themes;

  $scope.model.themes = themeList;

  editor.container.classList.add("snapcode_editor");
  editor.setShowPrintMargin(false);
  editor.setOptions({
    fontFamily: "FiraCode",
    fontSize: "12pt",
    // enableBasicAutocompletion: true,
    // enableLiveAutocompletion: true,
    // enableSnippets: true
  });

  editor.setTheme($scope.model.theme);
  editor.session.setMode({
    path: "ace/mode/php",
    inline: true,
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
    if (
      ($event.ctrlKey && $event.keyCode === 13) ||
      ($event.metaKey && $event.keyCode === 13)
    ) {
      $scope.getOutput($scope.model);
    }
  };

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

  function prettySql(query) {
    if (!query) return "";

    const strings = [];
    let sql = query.replace(/'([^'\\]|\\.)*'/g, (m) => {
      strings.push(m);
      return `__STR${strings.length - 1}__`;
    });

    const KEYWORDS = [
      "SELECT",
      "FROM",
      "WHERE",
      "GROUP BY",
      "ORDER BY",
      "HAVING",
      "LIMIT",
      "OFFSET",
      "JOIN",
      "INNER JOIN",
      "LEFT JOIN",
      "RIGHT JOIN",
      "FULL JOIN",
      "CROSS JOIN",
      "ON",
      "UNION",
      "UNION ALL",
      "INTERSECT",
      "EXCEPT",
    ];

    const kwPattern = new RegExp(
      "(?<![\\w_])(" +
        KEYWORDS.map((k) => k.replace(/\\s+/g, "\\s+")).join("|") +
        ")(?![\\w_])",
      "gi"
    );

    sql = sql
      .replace(/\s+/g, " ")
      .replace(kwPattern, "\n$1")
      .replace(/\n{2,}/g, "\n")
      .trim();

    sql = sql
      .split("\n")
      .map((line) => {
        const t = line.trim();
        if (/^(AND|OR|ON)\b/i.test(t)) return " " + t;
        return t;
      })
      .join("\n");

    sql = sql.replace(/__STR(\d+)__/g, (_, i) => strings[Number(i)]);

    return sql;
  }

  $scope.openSettings = function () {
    let url = "#TB_inline?width=600&height=150&inlineId=snapcode-settings";
    tb_show("Settings", url, false);
  };

  $scope.saving = false;
  $scope.saveSettings = function (settings) {
    $scope.saving = true;
    let payload = {
      [nonceKey]: nonceValue,
      action: "snapcode_save_settings",
      settings: JSON.stringify(settings),
    };

    let config = {
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    };

    $http
      .post(_snapcode.ajaxUrl, Object.toparams(payload), config)
      .success(function (res) {
        $scope.saving = false;
        tb_remove();
      });
  };

  function getOutput() {
    const code = editor.getSelectedText() || editor.getValue();

    let payload = {
      [nonceKey]: nonceValue,
      action: "snapcode_output",
      code: code,
    };

    let config = {
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    };

    $scope.processing = true;
    localStorage.setItem("snapcode", JSON.stringify({ code: code }));
    $http
      .post(_snapcode.ajaxUrl, Object.toparams(payload), config)
      .success(function (res) {
        console.log(res);
        $scope.output = res.data;

        $http
          .get(_snapcode.pluginUrl + "tmp/query.json")
          .success(function (data) {
            $scope.queries = data;
          });

        $scope.tab = "output";
        $scope.processing = false;
      });
  }

  /**
   * Update plugin
   */
  $scope.pluginInfo = {
    updateUrl: _snapcode.updateUrl,
    currentVersion: _snapcode.version,
    newVersion: null,
    updateAvailable: false,
  };

  function compareVersion(v1, comparator, v2) {
    "use strict";
    var comparator = comparator == "=" ? "==" : comparator;
    if (
      ["==", "===", "<", "<=", ">", ">=", "!=", "!=="].indexOf(comparator) == -1
    ) {
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
  }

  $scope.checkUpdate = function () {
    $http.get($scope.pluginInfo.updateUrl).success(function (res) {
      let newVersion = res.version;

      $scope.pluginInfo.newVersion = newVersion;
      $scope.pluginInfo.updateAvailable = compareVersion(
        $scope.pluginInfo.currentVersion,
        "<",
        newVersion
      );
      // $scope.pluginInfo.updateAvailable = true;
    });
  };

  $scope.checkUpdate();

  $scope.updating = false;
  $scope.updatePlugin = function () {
    let data = {
      plugin: "ajax/ajax.php",
      slug: "ajax",
      action: "update-plugin",
      _ajax_nonce: _snapcode.pluginUpdateNonce,
    };

    $scope.updating = true;
    $http
      .post(_snapcode.ajaxUrl, toFormData(data), config)
      .success(function (res) {
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
