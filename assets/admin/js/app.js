let myApp = angular.module('myApp', [])

myApp.controller('AppCtrl', function ($scope, $http) {

    $scope.output = ''
    $scope.tab = 'output'
    $scope.processing = false
    $scope.phpPath = ''

    $scope.model = {
        code: 'new WP_User(1)'
    }

    document.getElementById('code').focus()

    Object.toparams = function ObjecttoParams(obj) {
        var p = [];
        for (var key in obj) {
            p.push(key + '=' + encodeURIComponent(obj[key]));
        }
        return p.join('&');
    }

    $scope.getOutput = getOutput
    $scope.copy = copyToClipboard
    $scope.queries = []

    $scope.listenEvent = function ($event) {
        if ($event.ctrlKey && $event.keyCode === 13 || $event.metaKey && $event.keyCode === 13) {
            $scope.getOutput($scope.model)
        }
    }

    $scope.setTab = function (tab) {
        $scope.tab = tab
    }

    function copyToClipboard(text, e) {
        let el = e.target
        let dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = text;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);

        el.innerHTML = 'Copied!'
        el.style.backgroundColor = 'green'
        el.style.color = 'white'

        setTimeout(() => {
            el.innerHTML = 'Copy'
            el.removeAttribute('style')
        }, 1000)
    }

    function getOutput(model) {
        let payload = {
            _wpnonce: document.querySelector('input[name="_wpnonce"]').value,
            action: 'wptinker_output',
            code: model.code
        }

        let config = {
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }

        $scope.processing = true
        $http.post(_snapcode.ajaxUrl, Object.toparams(payload), config)
            .success(function (res) {
                console.log(res)
                $scope.output = res.data

                $http.get(_snapcode.pluginUrl + 'tmp/query.json')
                    .success(function (data) {
                        $scope.queries = data;
                    })

                $scope.tab = 'output'
                $scope.processing = false
            })
    }

    $scope.saveConfig = function (phpPath) {
        if (!confirm('Are you sure?')) return;

        let payload = {
            _wpnonce_php_path: document.querySelector('input[name="_wpnonce_php_path"]').value,
            action: 'wptinker_save_config',
            php_path: phpPath
        }

        let config = {
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }

        $http.post(_snapcode.ajaxUrl, Object.toparams(payload), config)
            .success(function (res) {
                console.log(res)
                alert(res.message)
            })
    }

})