<?php

/**
 * 站点编辑
 */
$title = '站点编辑';
include 'header.php';
global $conf;
$method = explode(',', $conf['CartPaySet']);
?>
<div class="card">
    <div class="card-body">
        <div class="mdui-tab mdui-tab-scrollable mdui-tab-centered mdui-p-l-0" mdui-tab>
            <a href="#Se1" class="mdui-ripple">全局基础配置</a>
            <a href="#Se6" class="mdui-ripple">首页功能配置</a>
            <a href="#Se2" class="mdui-ripple">客服相关配置</a>
            <a href="#Se3" class="mdui-ripple">订单相关配置</a>
            <a href="#Se4" class="mdui-ripple">安全相关配置</a>
            <a href="#Se5" class="mdui-ripple">其他杂项配置</a>
        </div>
    </div>
    <div class="card-body mdui-p-a-0">
        <div id="Se1" class="mdui-p-a-2 layui-form">
            <div class="form-group mb-3">
                <label for="example-input-normal">站点名称</label>
                <input type="text" name="sitename" lay-verify="required" class="form-control"
                       value="<?= $conf['sitename'] ?>" placeholder="请输入站点名称">
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">站点副标题</label>
                <input type="text" name="title" lay-verify="required" class="form-control" value="<?= $conf['title'] ?>"
                       placeholder="请输入网址副标题">
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">站点关键词[SEO]</label>
                <input type="text" name="keywords" lay-verify="required" class="form-control"
                       value="<?= $conf['keywords'] ?>" placeholder="请输入站点名称">
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">站点描述语[SEO]</label>
                <input type="text" name="description" lay-verify="required" class="form-control"
                       value="<?= $conf['description'] ?>" placeholder="请输入站点名称">
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">网站货币名称</label>
                <input type="text" name="currency" lay-verify="required" class="form-control"
                       value="<?= $conf['currency'] ?>" placeholder="请输入货币名称，如：积分">
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">logo地址</label>
                <div class="input-group">
                    <input type="text" lay-verify="required" lay-verType="tips" class="form-control" name="logo"
                           id="image" value="<?= $conf['logo'] ?>" placeholder="站点logo的地址"/>
                    <div class="input-group-append">
                        <span class="input-group-text" id="upload" style="cursor: pointer">上传</span>
                    </div>
                    <div class="input-group-append">
                        <span class="input-group-text"
                              onclick="layer.alert('<img src=\''+$('#image').val()+'\' style=width:100%  />')"
                              style="cursor: pointer;background-color: slateblue;color: white">预览</span>
                    </div>
                    <div class="input-group-append">
                        <a href="http://cloud.79tian.com/s/e4KSo" target="_blank" class="input-group-text"
                           style="cursor: pointer;background-color: #a8f46b;color: white">图库</a>
                    </div>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">APP下载地址（留空不显示）</label>
                <input type="text" name="appurl" class="form-control" value="<?= $conf['appurl'] ?>"
                       placeholder="请输入APP下载链接！">
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">主站后台导航栏样式</label>
                <select class="custom-select mt-3" name="AdminHeaderTem">
                    <option <?= $conf['AdminHeaderTem'] == 1 ? 'selected' : '' ?> value="1">使用顶部导航栏[适合PC端]
                    </option>
                    <option <?= $conf['AdminHeaderTem'] == 2 ? 'selected' : '' ?> value="2">使用侧边导航栏[通用]
                    </option>
                </select>
            </div>
            <button type="submit" lay-submit lay-filter="Web_editor" class="btn btn-block btn-xs btn-success">保存内容
            </button>
        </div>
        <div id="Se2" class="mdui-p-a-2 layui-form">
            <div class="form-group mb-3">
                <label for="example-input-normal">客服二维码，用于联系客服，可上传微信或QQ的二维码（留空显示添加QQ好友的二维码哦）</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="ServiceImage" id="image2"
                           value="<?= $conf['ServiceImage'] ?>" placeholder="客服二维码"/>
                    <div class="input-group-append">
                        <span class="input-group-text" id="upload2" style="cursor: pointer">上传</span>
                    </div>
                    <div class="input-group-append">
                        <span class="input-group-text"
                              onclick="layer.alert('<img src=\''+$('#image2').val()+'\' style=width:100%  />')"
                              style="cursor: pointer;background-color: slateblue;color: white">预览</span>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">腾讯云智服sign
                </label>
                <input type="text" name="YzfSign" class="form-control" value="<?= $conf['YzfSign'] ?>"
                       placeholder="留空不显示！">
                <div class="mdui-m-t-1">
                    <font color="red">
                        留空不显示</font>
                    <a href="https://yzf.qq.com/" target="_blank">获取地址</a>
                    <a href="javascript:layer.alert('第一步：点击旁边的获取地址进入云智服官网<br>第二步：注册,注册需要微信<br>第三步：登陆后台,登陆后可看提示操作<br>第四步：点击顶部设置按钮,渠道按钮选择站点渠道<br>第五步：点击新增站点按钮填写信息,填写完毕后可看到部署界面！<br>第六步：在链接地址里面找到你自己的sign！<br>如：https://yzf.qq.com/xv/web/static/chat/index.html?sign=长长的混合字符串<br>把sign后面的字符串复制下来,填在这里就行了！',{title:'云智服sign获取教程'})"
                       style="color: #0bedcf">获取教程</a>
                    <a href="https://yzf.qq.com/xv/html/admin/chat/home" target="_blank">会话管理地址</a>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">客服QQ，用于添加客服QQ好友，方便售后</label>
                <input type="text" name="kfqq" lay-verify="required" class="form-control" value="<?= $conf['kfqq'] ?>"
                       placeholder="请输入客服QQ">
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">官方群链接</label>
                <input type="text" name="Communication" class="form-control"
                       value="<?= $conf['Communication'] ?>" placeholder="请输入官方群链接">
            </div>
            <button type="submit" lay-submit lay-filter="Web_editor" class="btn btn-block btn-xs btn-success">保存内容
            </button>
        </div>
        <div id="Se6" class="mdui-p-a-2 layui-form">
            <div class="form-group mb-3">
                <label for="example-input-normal">站点动态消息通知提醒开关,如用户xxx于xxx购买了商品</label>
                <select class="custom-select mt-3" lay-search name="DynamicMessage">
                    <option <?= $conf['DynamicMessage'] == -1 ? 'selected' : '' ?> value="-1">关闭动态消息通知
                    </option>
                    <option <?= $conf['DynamicMessage'] == 1 ? 'selected' : '' ?> value="1">开启动态消息通知
                    </option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">等级价格列表展示</label>
                <select class="custom-select mt-3" lay-search name="LevelDisplay">
                    <option <?= $conf['LevelDisplay'] == -1 ? 'selected' : '' ?> value="-1">关闭价格展示
                    </option>
                    <option <?= $conf['LevelDisplay'] == 1 ? 'selected' : '' ?> value="1">开启价格展示
                    </option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">主页商品排序规则[分类内会自动切换为默认]</label>
                <select class="custom-select mt-3" lay-search name="SortingRules">
                    <option <?= $conf['SortingRules'] == 'sort' ? 'selected' : '' ?> value="sort">默认排序[sort参数]
                    </option>
                    <option <?= $conf['SortingRules'] == 'update_dat' ? 'selected' : '' ?>
                            value="update_dat">商品修改时间排序[update_dat]
                    </option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">同分类商品推荐</label>
                <select class="custom-select mt-3" lay-search name="SimilarRecommend">
                    <option <?= $conf['SimilarRecommend'] == -1 ? 'selected' : '' ?> value="-1">关闭推荐
                    </option>
                    <option <?= $conf['SimilarRecommend'] == 1 ? 'selected' : '' ?> value="1">开启推荐
                    </option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">商品价格波动展示</label>
                <select class="custom-select mt-3" lay-search name="FluctuationsPrices">
                    <option <?= $conf['FluctuationsPrices'] == 1 ? 'selected' : '' ?> value="1">开启价格波动展示
                    </option>
                    <option <?= $conf['FluctuationsPrices'] == -1 ? 'selected' : '' ?> value="-1">关闭价格波动展示
                    </option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">商品推荐开关（仅在购物车,商品详情,订单详情等界面下方出现哦）</label>
                <select class="custom-select mt-3" name="GoodsRecommendation">
                    <option <?= $conf['GoodsRecommendation'] == 1 ? 'selected' : '' ?> value="1">开启
                    </option>
                    <option <?= $conf['GoodsRecommendation'] == 2 ? 'selected' : '' ?> value="2">关闭
                    </option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">前台商品,分类,文章每页数据数,默认12，建议不要低于12,否则可能会出现异常</label>
                <input type="text" name="HomeLimit" class="form-control" value="<?= $conf['HomeLimit'] ?>"
                       placeholder="请输入前台每页展现的数据数量！">
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">关闭站点首页</label>
                <input type="text" name="CloseWebsite" class="form-control" value="<?= $conf['CloseWebsite'] ?>"
                       placeholder="关闭站点的原因,留空不关站">
                <div class="mdui-m-t-1">
                    <font color="red">
                        可填写关闭站点的原因,留空不关闭站点，只影响商城首页！</font>
                </div>
            </div>

            <div class="form-group mb-3" style="">
                <label for="example-input-normal" style="position: relative;bottom: 0;left: 0">自定义导航（格式,跳转名称,跳转链接|跳转名称,跳转链接）</label>
                <textarea class="form-control" name="navigation" lay-verify="required" rows="6"
                          placeholder="格式,跳转名称,跳转链接|跳转名称,跳转链接"><?= $conf['navigation'] ?></textarea>
            </div>

            <button type="submit" lay-submit lay-filter="Web_editor" class="btn btn-block btn-xs btn-success">保存内容
            </button>
        </div>
        <div id="Se3" class="mdui-p-a-2 layui-form">
            <div class="form-group mb-3">
                <label for="example-input-normal">免费商品或积分商品每日兑换限制<font color="red">(单一用户)</font></label>
                <input type="text" name="getinreturn" lay-verify="required" class="form-control"
                       value="<?= $conf['getinreturn'] ?>" placeholder="每日单一用户可用积分兑换商品的次数(包含免费商品)">
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">免费商品或积分商品每日兑换限制<font color="red">(整个站点)</font></label>
                <input type="text" name="getinreturn_all" lay-verify="required" class="form-control"
                       value="<?= $conf['getinreturn_all'] ?>" placeholder="每日整个站点可用积分兑换商品的次数(包含免费商品)">
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">下单违禁词</label>
                <input type="text" name="blacklist" class="form-control" value="<?= $conf['blacklist'] ?>"
                       placeholder="请使用英文逗号分割！">
                <div class="mdui-m-t-1">
                    <font color="red">若下单信息中包含下列关键词则无法购买商品,多个请使用英文逗号分割</font><br>
                    如：违禁词1,违禁词2,违禁词3
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">未完成订单下单信息自助修改</label>
                <select class="custom-select mt-3" name="OrderModification">
                    <option <?= $conf['OrderModification'] == 1 ? 'selected' : '' ?> value="1">开启自助修改
                    </option>
                    <option <?= $conf['OrderModification'] == 2 ? 'selected' : '' ?> value="2">关闭自助修改
                    </option>
                </select>
                <div class="mdui-m-t-1">
                    开启后，用户可以在订单详情内修改自己的下单信息，仅限于待处理，异常中的订单！
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">商品分享海报获取方式</label>
                <select class="custom-select mt-3" name="share_image">
                    <option <?= $conf['share_image'] == 1 ? 'selected' : '' ?> value="1">本地生成(单一样式)
                    </option>
                    <option <?= $conf['share_image'] == 2 ? 'selected' : '' ?> value="2">云端生成(多种随机样式)
                    </option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">启用订单队列 </label>
                <select class="custom-select mt-3" name="OredeQueue">
                    <option <?= $conf['OredeQueue'] == 1 ? 'selected' : '' ?> value="1">不启用订单队列,下单后直接对接到货源
                    </option>
                    <option <?= $conf['OredeQueue'] == 2 ? 'selected' : '' ?> value="2">启用订单队列,先加入队列后对接货源
                    </option>
                </select>
                <div class="mdui-m-t-1">
                    <font color="red">订单队列可有效缓解服务器压力,购物车付款默认开启！ </font>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">防重复订单配置(秒数限制) </label>
                <input type="text" name="OrderAstrict" lay-verify="required" class="form-control"
                       value="<?= $conf['OrderAstrict'] ?>" placeholder="同信息商品下单间隔秒数(商品ID,下单信息,下单份数相同的订单)">
                <div class="mdui-m-t-1">
                    <font color="red">商品ID,下单信息,下单份数,全部相同则判断为同一订单!防止重复写入创建重复订单!</font>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">购物车开关</label>
                <select class="custom-select mt-3" name="CartState">
                    <option <?= $conf['CartState'] == 1 ? 'selected' : '' ?> value="1">开启购物车
                    </option>
                    <option <?= $conf['CartState'] == 2 ? 'selected' : '' ?> value="2">关闭购物车
                    </option>
                </select>
                <div class="mdui-m-t-1">
                    <font color="red">
                        关闭后用户无法向购物车添加商品 </font>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">下单失败后状态 </label>
                <select class="custom-select mt-3" name="SubmitState">
                    <option <?= $conf['SubmitState'] == 1 ? 'selected' : '' ?> value="1">已完成
                    </option>
                    <option <?= $conf['SubmitState'] == 2 ? 'selected' : '' ?> value="2">待处理
                    </option>
                    <option <?= $conf['SubmitState'] == 3 ? 'selected' : '' ?> value="3">异常中
                    </option>
                    <option <?= $conf['SubmitState'] == 4 ? 'selected' : '' ?> value="4">正在处理
                    </option>
                    <option <?= $conf['SubmitState'] == 5 ? 'selected' : '' ?> value="5">退款
                    </option>
                    <option <?= $conf['SubmitState'] == 6 ? 'selected' : '' ?> value="6">已评价(完结订单,无法申请售后)
                    </option>
                </select>
                <div class="mdui-m-t-1">
                    <font color="red">
                        包括但不限于,货源对接失败,自营商品下单,卡密商品发卡失败,api商品对接失败等所有失败状态！ </font>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">下单成功后状态</label>
                <select class="custom-select mt-3" name="SubmitStateSuccess">
                    <option <?= $conf['SubmitStateSuccess'] == 1 ? 'selected' : '' ?> value="1">已完成(点击状态可查看订单)
                    </option>
                    <option <?= $conf['SubmitStateSuccess'] == 2 ? 'selected' : '' ?> value="2">待处理(点击状态可补单)
                    </option>
                    <option <?= $conf['SubmitStateSuccess'] == 3 ? 'selected' : '' ?> value="3">异常中(点击状态可填写订单处理结果)
                    </option>
                    <option <?= $conf['SubmitStateSuccess'] == 4 ? 'selected' : '' ?> value="4">正在处理(点击状态可填写订单处理结果)
                    </option>
                    <option <?= $conf['SubmitStateSuccess'] == 5 ? 'selected' : '' ?> value="5">退款(点击状态可填写订单处理结果)
                    </option>
                    <option <?= $conf['SubmitStateSuccess'] == 6 ? 'selected' : '' ?> value="6">已评价(点击状态可查看订单)
                    </option>
                </select>
                <div class="mdui-m-t-1">
                    <font color="red">包括但不限于,货源对接失败,自营商品下单,卡密商品发卡成功,api商品对接成功等所有成功状态！ </font>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">保底销量</label>
                <input type="text" name="SalesSum" class="form-control" value="<?= $conf['SalesSum'] ?>"
                       placeholder="销量数量计算规则：保底销量+真实销量">
                <div class="mdui-m-t-1">
                    <font color="red">销量数量计算规则：保底销量+真实销量</font>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">商品默认标签名称,单文字1-4字即可，多个可用【|】分割！</label>
                <input type="text" name="DefaultLabel" class="form-control" value="<?= $conf['DefaultLabel'] ?>"
                       placeholder="商品默认标签名称,单文字1-4字即可">
                <div style="padding: 0.2em;color: #1774ff">
                    如果有需要可在文字后面设置背景颜色，如：官方,#ff0|质量,#00f，以此类推
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">物流单号查询接口
                    <a href="javascript:"
                       onclick="layer.alert('<font color=red>问：在什么地方会用到？</font><br>答：首页用户订单查询管理界面<br><font color=red>问：如何设置订单的物流单号?</font><br>答：在站长后台，订单管理界面，点击订单最开头的备注按钮，按照格式填写即可识别出单号，如：物流单号|xxxxxxxxxxxxx（xxx是单号）<br><font color=red>Ps：默认为快递110的查询地址，可自定义，拼接格式：接口地址+物流单号</font>')">说明</a>
                </label>
                <input type="text" name="Tracking" class="form-control" value="<?= $conf['Tracking'] ?>"
                       placeholder="拼接方式：<?= $conf['Tracking'] ?>识别的单号">
            </div>

            <button type="submit" lay-submit lay-filter="Web_editor" class="btn btn-block btn-xs btn-success">保存内容
            </button>
        </div>
        <div id="Se4" class="mdui-p-a-2 layui-form">
            <div class="form-group mb-3">
                <label for="example-input-normal">安全中心</label>
                <select class="custom-select mt-3" name="SecurityCenter">
                    <option <?= $conf['SecurityCenter'] == 1 ? 'selected' : '' ?> value="1">开启整站日志监控
                    </option>
                    <option <?= $conf['SecurityCenter'] == -1 ? 'selected' : '' ?> value="-1">关闭整站日志监控
                    </option>
                </select>
                <div class="mdui-m-t-1">
                    <font color="red">关闭后不再记录用户访问日志,需前往应用商店安装对应插件</font>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">是否允许在QQ或微信内打开站点?</label>
                <select class="custom-select mt-3" name="WeixQqValidation">
                    <option <?= $conf['WeixQqValidation'] == 1 ? 'selected' : '' ?> value="1">不允许
                    </option>
                    <option <?= $conf['WeixQqValidation'] == 2 ? 'selected' : '' ?> value="2">允许
                    </option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">目录保护,开启后可使用账号密码登陆</label>
                <input type="text" name="Protect" class="form-control" value="<?= $conf['Protect'] ?>"
                       placeholder="目录保护后缀，留空不保护！">
                <div class="mdui-m-t-1">
                    <font color="red">设置后，登陆站长后台需填写目录保护密码，请牢记！</font>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">首页加密访问密码</label>
                <input type="text" name="PasswordAccess" class="form-control" value="<?= $conf['PasswordAccess'] ?>"
                       placeholder="留空不开启首页加密访问，可输入数字加字母组合密码！">
                <div class="mdui-m-t-1">
                    <font color="red">设置后，打开网站首页需要输入密码才可访问，不影响用户后台登陆！</font>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">规避词替换内容</label>
                <input type="text" name="GuardStr" class="form-control" value="<?= $conf['GuardStr'] ?>"
                       placeholder="留空默认将规避词替换为 * 符号">
                <div class="mdui-m-t-1">
                    <font color="red">用于替换规避词内容，默认为 * ，此功能默认开启，用于减少域名被墙的概率，规避字段由服务端统一配置！</font>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">加密访问提示信息</label>
                <input type="text" name="PasswordAccessTips" class="form-control"
                       value="<?= $conf['PasswordAccessTips'] ?>"
                       placeholder="请输入提示内容！">
                <div class="mdui-m-t-1">
                    <font color="red">只有开启了加密访问才会看到提示，如密码获取方式等</font>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">站点验证码类型</label>
                <select class="custom-select mt-3" name="CaptchaType">
                    <option <?= $conf['CaptchaType'] == 1 ? 'selected' : '' ?> value="1">纯数字验证码
                    </option>
                    <option <?= $conf['CaptchaType'] == 2 ? 'selected' : '' ?> value="2">纯字母验证码
                    </option>
                    <option <?= $conf['CaptchaType'] == 3 ? 'selected' : '' ?> value="3">字母+数字混合验证码
                    </option>
                    <option <?= $conf['CaptchaType'] == 4 ? 'selected' : '' ?> value="4">中文汉字验证码
                    </option>
                    <option <?= $conf['CaptchaType'] == 5 ? 'selected' : '' ?> value="5">运算符验证码(+ - × ÷)
                    </option>
                    <option <?= $conf['CaptchaType'] == 6 ? 'selected' : '' ?> value="6">随机验证码(上方5种随机)
                    </option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">初级反爬虫</label>
                <select class="custom-select mt-3" name="AntiReptile">
                    <option <?= $conf['AntiReptile'] == 1 ? 'selected' : '' ?> value="1">开启首页反爬虫
                    </option>
                    <option <?= $conf['AntiReptile'] == -1 ? 'selected' : '' ?> value="-1">关闭首页反爬虫
                    </option>
                </select>
                <div class="mdui-m-t-1">
                    <font color="red">开启后可能会影响某些功能，另外，开启此功能后可能会影响搜索引擎收录！</font>
                </div>
            </div>
            <button type="submit" lay-submit lay-filter="Web_editor" class="btn btn-block btn-xs btn-success">保存内容
            </button>
        </div>
        <div id="Se5" class="mdui-p-a-2 layui-form">
            <div class="form-group mb-3">
                <label for="example-input-normal">上传图片压缩比例</label>
                <input type="text" name="compression" lay-verify="required" class="form-control"
                       value="<?= $conf['compression'] ?>" placeholder="图片上传压缩比例,0.1=压缩到10%,1=不压缩">
                <div class="mdui-m-t-1">
                    <font color="red">0.1=压缩到10%,1=不压缩，如果图片为透明背景，则会变为黑色！,此功能除了用于减少图片大小，还可用于防止图片木马等一系列上传文件相关漏洞！</font>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">API对接密钥</label>
                <input type="text" name="secret" lay-verify="required" class="form-control"
                       value="<?= $conf['secret'] ?>" placeholder="调用后台接口时使用！">
            </div>
            <div class="form-group mb-3">
                <label for="example-input-normal">首页数据缓存周期</label>
                <input type="text" name="Homecaching" lay-verify="required" class="form-control"
                       value="<?= $conf['Homecaching'] ?>" placeholder="首页分类,商品详细信息等杂七杂八的数据缓存周期">
                <div class="mdui-m-t-1">
                    <font color="red">整站每隔多少秒获取一次最新数据?,商品价格监控不受影响</font>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal">新订单通知(邮件,是通知你，不是通知用户！) <a href="javascript:"
                                                                            onclick="layer.alert('若已经在小程序或APP绑定了可以正常接收邮件的QQ或者邮箱，即可收到通知！<br>若服务器速度较慢,可能会影响下单提交速度！')">说明</a></label>
                <select class="custom-select mt-3" lay-search name="weix_notice">
                    <option <?= $conf['weix_notice'] == -1 ? 'selected' : '' ?> value="-1">关闭新订单通知
                    </option>
                    <option <?= $conf['weix_notice'] == 1 ? 'selected' : '' ?> value="1">开启新订单通知
                    </option>
                </select>
            </div>

            <div class="form-group mb-2">
                <label for="example-input-normal">购物车支持的付款方式</label>
                <div class="">
                    <?php
                    $met = 0;
                    $method_name = ['支付宝', '微信', 'QQ钱包', '余额', '积分兑换'];
                    foreach ($method_name as $v) {
                        ?>
                        <input type="checkbox" name="CartPaySet[<?= ($met) ?>]" lay-skin="primary"
                               value="<?= ($met + 1) ?>"
                               title="<?= $method_name[$met] ?>" <?= in_array(($met + 1), $method) ? ' checked=""' : '' ?>>
                        <?php $met++;
                    } ?>
                </div>
            </div>

            <div class="form-group mb-3" style="">
                <label for="example-input-normal" style="position: relative;bottom: 0;left: 0">购物车收银台公告 (纯文字即可)</label>
                <textarea class="form-control" name="CartNotice" id="CartNotice" rows="6"
                          placeholder="请输入购物车收银台公告"><?= $conf['CartNotice'] ?></textarea>
            </div>

            <button type="submit" lay-submit lay-filter="Web_editor" class="btn btn-block btn-xs btn-success">保存内容
            </button>
        </div>
    </div>
</div>


<?php include 'bottom.php'; ?>

<script>
    layui.use('form', function () {
        var form = layui.form;
        form.on('submit(Web_editor)', function (data) {
            let a = '';
            const array = [];
            $.each(data.field, function (key, val) {
                if (key.indexOf('CartPaySet') !== -1) {
                    if (this[0] == undefined) return;
                    a += ',' + this[0]
                }
            });
            if (a != '') {
                a = a.substr(1, a.length);
            }
            if (data.field.Homecaching !== undefined && data.field.Homecaching !== null && data.field.Homecaching !== '') {
                data.field['CartPaySet'] = a;
            }

            console.log(data.field);
            layer.alert('是否要执行当前操作？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function (layero, index) {
                    let is = layer.msg('保存中，请稍后...', {
                        icon: 16,
                        time: 9999999
                    });
                    $.ajax({
                        type: "POST",
                        url: './ajax.php?act=config_set',
                        data: data.field,
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1,
                                    btn1: function () {
                                        location.reload();
                                    }
                                });
                            } else {
                                layer.alert(res.msg, {
                                    icon: 2
                                });
                            }
                        },
                        error: function () {
                            layer.msg('服务器异常！');
                        }
                    });
                }
            });
            return false;
        })
    });
    layui.use(['upload', 'form'], function () {
        var upload = layui.upload;
        var uploadInst = upload.render({
            elem: '#upload' //绑定元素
            ,
            url: 'ajax.php?act=image_up' //上传接口
            ,
            done: function (res, index, upload) {
                layer.msg('图片上传成功');
                $("#image").val(res.src);
            },
            error: function () {
                layer.msg('图片上传失败!')
            }
        });

        var uploadInst = upload.render({
            elem: '#upload2' //绑定元素
            ,
            url: 'ajax.php?act=image_up' //上传接口
            ,
            done: function (res, index, upload) {
                layer.msg('图片上传成功');
                $("#image2").val(res.src);
            },
            error: function () {
                layer.msg('图片上传失败!')
            }
        });
    });
</script>
