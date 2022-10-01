<?php
/**
 * 全局底部调用
 */
global $conf, $cdnserver;
?>
    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    2019 - 2022 © All rights reserved - <?= $conf['sitename'] ?>
                </div>
                <div class="col-md-6">
                    <div class="text-md-right footer-links d-none d-md-block">
                        <a href="<?= $conf['Communication'] ?>" target="_blank">官方交流群</a>
                        <a href="http://wpa.qq.com/msgrd?v=3&uin=<?= $conf['kfqq'] ?>&site=qq&menu=yes"
                           target="_blank">联系客服</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- bundle -->
    <script src="../assets/js/app.min.js"></script>
    <!-- third party js -->
    <script src="../assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="../assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <script src="../assets/layui/layui.all.js"></script>
    <script src="../assets/js/vue.js"></script>
    <script src="../assets/mdui/js/mdui.min.js"></script>
    <!-- third party js ends -->
    <script>
        layui.use('flow', function () {
            var flow = layui.flow;
            flow.lazyimg();
        });
        window.onload = function () {
            let loading = document.getElementById('loading');
            let jis = 100;
            for (let i = jis; i >= 0; i--) {
                setTimeout(function () {
                    let sum = loading.style.opacity - 0;
                    if (sum > 0.5) {
                        loading.style.opacity = '' + i / 100 + '';
                    } else {
                        loading.style.display = 'none';
                    }
                }, 5 * (jis - i));
            }
        }
    </script>
<?php if (!empty($conf['YzfSign']) && (int)$conf['YzfSign'] !== -1) { ?>
    <script src="https://yzf.qq.com/xv/web/static/chat_sdk/yzf_chat.min.js"></script>
    <script>
        window.yzf && window.yzf.init({
            sign: '<?=$conf['YzfSign']?>',
            uid: '',
            data: {
                c1: '',
                c2: '',
                c3: '',
                c4: '',
                c5: ''
            },
            selector: '',
            callback: function (type, data) {
            }
        });
    </script>
    <style>
        .main-contact {
            z-index: 9 !important
        }

        .chat-btn, .main-contact {
            border-radius: 1.1em !important;
        }
    </style>
<?php } ?>