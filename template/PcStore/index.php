<?php
if (!defined('IN_CRONLITE')) die;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <link rel="icon" href="./assets/favicon.ico"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= $conf['sitename'] . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <script type="module" crossorigin
            src="./assets/template/PcStore/assets/index.379deaad.js"></script>
    <link rel="modulepreload" href="./assets/template/PcStore/assets/vendor.caf54732.js">
    <link rel="stylesheet" href="./assets/template/PcStore/assets/index.415d6363.css">
    <link rel="stylesheet" href="./assets/css/Global.css?t=1">
    <script type="module">!function () {
            try {
                new Function("m", "return import(m)")
            } catch (o) {
                console.warn("vite: loading legacy build because dynamic import is unsupported, syntax error above should be ignored");
                var e = document.getElementById("vite-legacy-polyfill"), n = document.createElement("script");
                n.src = e.src, n.onload = function () {
                    System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))
                }, document.body.appendChild(n)
            }
        }();</script>
</head>
<body>
<div id="app"></div>
<div style="font-size: 0.5rem;text-align: center;position: fixed;bottom: -10rem;left: 0;width: 100%;z-index: 0;"><?= $conf['statistics'] ?></div>

<script nomodule>!function () {
        var e = document, t = e.createElement("script");
        if (!("noModule" in t) && "onbeforeload" in t) {
            var n = !1;
            e.addEventListener("beforeload", (function (e) {
                if (e.target === t) n = !0; else if (!e.target.hasAttribute("nomodule") || !n) return;
                e.preventDefault()
            }), !0), t.type = "module", t.src = ".", e.head.appendChild(t), t.remove()
        }
    }();</script>
<script nomodule id="vite-legacy-polyfill"
        src="./assets/template/PcStore/assets/polyfills-legacy.f61f6db6.js"></script>
<script nomodule id="vite-legacy-entry"
        data-src="./assets/template/PcStore/assets/index-legacy.b2f7d3f9.js">System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))</script>
</body>
</html>
