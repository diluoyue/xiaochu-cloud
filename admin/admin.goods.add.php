<?php
$title = '添加/编辑商品';
include 'header.php';
global $conf, $_QET;
$Gid = (empty($_QET['gid']) ? '' : $_QET['gid']);
?>
<div class="row" id="App" gid="<?= $Gid ?>">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body mdui-p-a-2">
                <div class="mdui-tab mdui-tab-full-width" mdui-tab>
                    <a href="#UnderlyingConfiguration" class="mdui-ripple mdui-tab-active">基础配置</a>
                    <a href="#DeliveryConfiguration" @click="InputCount()" class="mdui-ripple">发货配置</a>
                    <a href="#OrderInformation" @click="RuleGet()" class="mdui-ripple">输入框配置</a>
                    <a href="#PriceConfiguration" @click="UserLevelGet()" class="mdui-ripple">售价配置</a>
                    <a href="#ClassificationGoods" class="mdui-ripple">分类标签</a>
                    <a href="#sundry" class="mdui-ripple">扩展参数</a>
                </div>
                <div id="UnderlyingConfiguration" class="mdui-p-a-2">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">商品名称</label>
                        <input class="mdui-textfield-input" v-model="Form.name" type="text" required/>
                        <div class="mdui-textfield-error">商品名称不能为空</div>
                    </div>

                    <div class="mdui-textfield ImageUp">
                        <label class="mdui-textfield-label">商品图片 </label>
                        <textarea class="mdui-textfield-input" v-model="Form.image" type="text" required></textarea>
                        <div class="mdui-textfield-helper">可点击下方按钮上传或预览图片,一行一张图片!</div>
                    </div>
                    <button class="mdui-btn mdui-btn-icon mdui-text-color-orange" id="ImageUpload"
                            mdui-tooltip="{content: '上传图片至本地'}">
                        <i class="mdui-icon material-icons">&#xe864;</i>
                    </button>
                    <button @click="ImagePreview()" class="mdui-btn mdui-btn-icon mdui-text-color-teal"
                            mdui-tooltip="{content: '图片预览'}">
                        <i class="mdui-icon material-icons">&#xe413;</i>
                    </button>
                    <a href="http://cloud.79tian.com/" target="_blank"
                       class="mdui-btn mdui-btn-icon mdui-text-color-light-blue" mdui-tooltip="{content: '云储-官方免费网盘'}">
                        <i class="mdui-icon material-icons">&#xe2be;</i>
                    </a>
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">每份商品的发货数量</label>
                        <input class="mdui-textfield-input" v-model="Form.quantity" @change="inputChange($event)"
                               type="number" required/>
                        <div class="mdui-textfield-helper">当购买多份时,每份商品的发货数量是多少?</div>
                    </div>
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">商品剩余库存总数</label>
                        <input class="mdui-textfield-input" v-model="Form.quota" type="number" required/>
                        <div class="mdui-textfield-helper">商品总库存,每售出一份减少一份库存!</div>
                    </div>

                    <div v-show="Form.method[6]===true" class="mdui-textfield">
                        <label class="mdui-textfield-label">最低购买份数</label>
                        <input class="mdui-textfield-input" v-model="Form.min" type="number" required/>
                        <div class="mdui-textfield-helper">用户单次最低购买多少份?</div>
                    </div>

                    <div v-show="Form.method[6]===true" class="mdui-textfield">
                        <label class="mdui-textfield-label">最多购买份数</label>
                        <input class="mdui-textfield-input" v-model="Form.max" type="number" required/>
                        <div class="mdui-textfield-helper">用户单次最多购买多少份?</div>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数量单位名称</label>
                        <input class="mdui-textfield-input" v-model="Form.units" type="text" required/>
                        <div class="mdui-textfield-helper">可留空，可填写数量单位名称,如 个,张,天,默认为【个】</div>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">商品销量基数</label>
                        <input class="mdui-textfield-input" v-model="Form.sales" type="text"/>
                        <div class="mdui-textfield-helper">可留空或填0，默认为【<?= $conf['SalesSum'] ?>
                            】个，真实销量会从此参数开始叠加！，默认值可前往网站配置内设置，当填写0或留空时，按照默认销量计算！
                        </div>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">商品购买注意事项</label>
                        <textarea v-model="Form.alert" class="mdui-textfield-input"></textarea>
                        <div class="mdui-textfield-helper">可留空，用户查看商品时最先看到的提示信息,可回车换行</div>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">可留空，商品说明,支持图片或视频</label>
                        <div id="content"></div>
                    </div>
                    <span style="cursor:pointer;color: #0AAB89;font-size: 15px;margin-right:0.5em;"
                          @click="EmptyDocs();">清空商品说明</span>
                    <span style="cursor:pointer;color: red;font-size: 15px;" @click="HtmlDocs();">编辑原始代码</span>
                </div>
                <div id="OrderInformation" class="mdui-p-a-2">
                    <div class="mdui-list">
                        <label mdui-tooltip="{content: '商品规格适合实物多规格商品,可配置独立库存,价格等!'}"
                               class="mdui-list-item mdui-ripple mdui-shadow-1">
                            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-green">&#xe1db;</i>
                            <div class="mdui-list-item-content">启用商品多规格</div>
                            <label class="mdui-switch">
                                <input type="checkbox" v-model="Form.specification"/>
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </label>

                        <label v-show="Form.specification===true"
                               mdui-tooltip="{content: '开启后，商品规格参数将提交至对接的第三方货源，url等，关闭则只提交商品输入框的内容！'}"
                               class="mdui-list-item mdui-ripple mdui-shadow-1">
                            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-amber-500">&#xe01f;</i>
                            <div class="mdui-list-item-content" style="font-size:0.9em;">将用户的规格选择参数提交至对接站</div>
                            <label class="mdui-switch">
                                <input type="checkbox" v-model="Form.specification_type"/>
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </label>
                    </div>

                    <div v-show="Form.specification">
                        <div id="specification_spu" class="mdui-textfield">
                            <label class="mdui-textfield-label">商品规格名</label>
                            <textarea v-model="Form.specification_spu" class="mdui-textfield-input"></textarea>
                        </div>
                        <div id="specification_sku" class="mdui-textfield">
                            <label class="mdui-textfield-label">商品规格值</label>
                            <textarea v-model="Form.specification_sku" class="mdui-textfield-input"></textarea>
                        </div>
                        <button @click="SpecificationsGenerated()"
                                class="mdui-btn mdui-btn-raised mdui-color-deep-purple">
                            点击生成规格名和规格值
                        </button>
                    </div>

                    <div class="mdui-textfield" id="input">
                        <label class="mdui-textfield-label">输入框规格参数</label>
                        <textarea v-model="Form.input" class="mdui-textfield-input"></textarea>
                        <div class="mdui-textfield-helper">可留空,可点击下方按钮生成输入框,或预览生成效果，留空显示为：下单账号</div>
                    </div>
                    <button @click="InputAdd()" class="mdui-btn mdui-btn-icon mdui-text-color-orange"
                            mdui-tooltip="{content: '添加一个新的输入框'}">
                        <i class="mdui-icon material-icons">&#xe148;</i>
                    </button>
                    <button @click="InputPreview()" class="mdui-btn mdui-btn-icon mdui-text-color-teal"
                            mdui-tooltip="{content: '输入框预览'}">
                        <i class="mdui-icon material-icons">&#xe3b4;</i>
                    </button>
                </div>
                <div id="PriceConfiguration" class="mdui-p-a-2">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">每份商品的成本(元)</label>
                        <input class="mdui-textfield-input" v-model="Form.money" type="text" required/>
                        <div class="mdui-textfield-helper">商品成本决定了各等级用户看到的最终售价,每份发货数为：{{
                            Form.quantity===''?'未设置':Form.quantity }}{{ Form.units }}
                        </div>
                    </div>
                    <div @click="FreeGoods()">
                        <span class="layui-badge layui-bg-blue"
                              style="background-color:#41bf8b !important;">将此商品设置为免费商品</span>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">商品利润比(%)</label>
                        <input class="mdui-textfield-input" v-model="Form.profits" type="text" required/>
                        <div class="mdui-textfield-helper">利润比可用于微调商品价格,如减少利润,或提升利润!</div>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">支付接口费率(%)</label>
                        <input class="mdui-textfield-input" v-model="rate" type="text" required/>
                        <div class="mdui-textfield-helper">此参数不会保存,计算结果仅供参考!</div>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label mdui-text-color-red">价格计算结果小数位数(售价精度)</label>
                        <input class="mdui-textfield-input" v-model="Form.accuracy" type="text" required/>
                        <div class="mdui-textfield-helper">可根据商品实际售价设置小数位数，最多8位！</div>
                    </div>

                    <div class="mdui-table-fluid">
                        <table class="mdui-table mdui-table-hoverable">
                            <thead>
                            <tr>
                                <th>设置</th>
                                <th>级别</th>
                                <th>购买价格</th>
                                <th>兑换价格</th>
                                <th>每份预估收益</th>
                                <th>等级名称</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item,index) in UserLevel">
                                <td>
                                    <button @click="SellingPrice(index)"
                                            class="mdui-btn mdui-ripple mdui-color-white mdui-shadow-0 mdui-btn-icon"
                                            :class="SellingPriceDataType[index]!=1?' mdui-text-color-blue-grey':' mdui-text-color-red'">
                                        <i class="mdui-icon material-icons"></i></button>
                                </td>
                                <td>V{{ index + 1 }}</td>
                                <td>
                                    <span @click="SellingPrice(index)" class="badge badge-danger-lighten">{{ PriceCalculation(item.priceis,1,index) }}</span>
                                </td>
                                <td>
                                    <span @click="SellingPrice(index)" class="badge badge-primary-lighten">{{ PriceCalculation(item.pointsis,2,index) }}</span>
                                </td>
                                <td>
                                    <span class="badge "
                                          :class="profitArr[index]>=0?'badge-success-lighten':'badge-danger-lighten'">{{ profitArr[index]>=0?'约':'亏损' }}{{ profitArr[index] }}元</span>
                                </td>
                                <td>{{ item.name }}</td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="mdui-card mdui-text-center" style="width:100%">
                            <div class="mdui-card-actions" style="width:100%">
                                <button @click="PriceTips()" class="mdui-btn mdui-ripple mdui-ripple-white">注意事项
                                </button>
                                <button @click="UserLevelGet(2)" class="mdui-btn mdui-ripple mdui-ripple-white">更新等级数据
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="freightData!==false">
                        <div class="card mdui-m-b-0 mdui-m-t-2 mdui-shadow-1">
                            <div class="card-header mdui-p-a-1">
                                <a title="初始化" href="javascript:App.freightDataGet(2);"
                                   class="badge badge-warning-lighten"><i
                                            class="layui-icon layui-icon-refresh-3"></i></a>
                                运费模板配置
                            </div>
                        </div>
                        <div class="mdui-list">
                            <label class="mdui-list-item mdui-ripple">
                                <div class="mdui-list-item-avatar mdui-color-white">
                                    <img src="../assets/img/freight.png"/>
                                </div>
                                <div class="mdui-list-item-content">不使用运费模板</div>
                                <label class="mdui-radio">
                                    <input type="radio" v-model="Form.freight" value="-1" name="freight"/>
                                    <i class="mdui-radio-icon"></i>
                                    -1
                                </label>
                            </label>
                            <label class="mdui-list-item mdui-ripple" v-for="(item,index) in freightData">
                                <div class="mdui-list-item-avatar mdui-color-white">
                                    <img src="../assets/img/freight.png"/>
                                </div>
                                <div class="mdui-list-item-content">{{ item.name }}</div>
                                <label class="mdui-radio">
                                    <input type="radio" v-model="Form.freight" :value="item.id" name="freight"/>
                                    <i class="mdui-radio-icon"></i>
                                    ID:{{ item.id }}
                                </label>
                            </label>
                        </div>
                    </div>
                </div>
                <div id="ClassificationGoods" class="mdui-p-a-2">
                    <div class="mdui-textfield" id="label">
                        <label class="mdui-textfield-label">商品标签规则</label>
                        <textarea v-model="Form.label" class="mdui-textfield-input"></textarea>
                        <div class="mdui-textfield-helper">可留空或点击下方按钮生成标签,或预览生成效果</div>
                    </div>
                    <button @click="LabelAdd()" class="mdui-btn mdui-btn-icon mdui-text-color-orange"
                            mdui-tooltip="{content: '添加一个新的标签'}">
                        <i class="mdui-icon material-icons">&#xe148;</i>
                    </button>
                    <button @click="LabelPreview()" class="mdui-btn mdui-btn-icon mdui-text-color-teal"
                            mdui-tooltip="{content: '标签预览'}">
                        <i class="mdui-icon material-icons">&#xe3b4;</i>
                    </button>
                    <div class="card mdui-m-b-0 mdui-m-t-2">
                        <div class="card-header mdui-p-a-1">
                            <a title="初始化" href="javascript:App.ClassDataGet(2);" class="badge badge-warning-lighten"><i
                                        class="layui-icon layui-icon-refresh-3"></i></a>
                            选择商品分类
                        </div>
                    </div>
                    <div class="mdui-list">
                        <label class="mdui-list-item mdui-ripple" v-for="(item,index) in ClassData">
                            <div class="mdui-list-item-avatar mdui-color-white"><img :src="item.image"/></div>
                            <div class="mdui-list-item-content">{{ item.name }}</div>
                            <label class="mdui-radio">
                                <input type="radio" v-model="Form.cid" :value="item.cid" name="cid"/>
                                <i class="mdui-radio-icon"></i>
                                CID:{{ item.cid }}
                            </label>
                        </label>
                    </div>
                </div>
                <div id="DeliveryConfiguration" class="mdui-p-a-2">
                    <select id="deliver" v-model="Form.deliver" class="mdui-select">
                        <option value="-1">对接第三方内置对接货源</option>
                        <option value="1">自营商品,不做任何处理</option>
                        <option value="2">创建订单后自动访问URL</option>
                        <option value="3">发送卡密给购买用户</option>
                        <option value="4">购买成功后显示隐藏内容</option>
                        <option value="5">宝塔主机空间发货</option>
                    </select>

                    <div v-show="Form.deliver==-1">
                        <div v-if="Form.sqid==''||Form.sqid=='0'"
                             class="layui-row layui-col-space8 mdui-m-t-2">
                            <div class="card mdui-m-b-0 mdui-m-t-2">
                                <div class="card-header mdui-p-a-1" style="border-bottom:none !important;">
                                    <a title="初始化" href="javascript:App.DockingListGet(2);"
                                       class="badge badge-warning-lighten">
                                        <i class="layui-icon layui-icon-refresh-3"></i></a>
                                    请选择需要对接的货源
                                </div>
                            </div>
                            <div v-for="(item,index) in DockingList" @click="ChooseDocking(index)"
                                 class="layui-col-xs6 layui-col-sm4 layui-col-lg3">
                                <div class="card mdui-ripple">
                                    <div class="card-header layui-elip font-13">
                                        <button title="点击获取可对接商品列表" class="btn btn-outline-primary badge mr-1">
                                            {{ item.id }}
                                        </button>
                                        {{item.name}}
                                    </div>
                                    <div class="card-body">
                                        <div class="layui-row">
                                            <div class="layui-col-xs12 layui-col-sm4 text-center">
                                                <div class="layui-hide-xs"><img :src="item.image"
                                                                                style="width: 48px; height: 48px; border-radius: 0.5em; box-shadow: rgb(238, 238, 238) 3px 3px 16px;">
                                                </div>
                                                <div class="layui-hide-sm"><img :src="item.image"
                                                                                style="width: 78px; height: 78px; border-radius: 0.5em; box-shadow: rgb(238, 238, 238) 3px 3px 16px;">
                                                </div>
                                            </div>
                                            <div class="layui-col-xs12 layui-col-sm8" style="font-size: 80%;">
                                                <div class="layui-hide-sm mt-2"></div>
                                                <div class="layui-elip w-100">
                                                    {{ item.username }}
                                                </div>
                                                <div class="layui-elip w-100">关联商品：{{ item.count }}个</div>
                                                <div class="layui-elip w-100"><a target="_blank" :href="item.url">{{
                                                        item.url }}</a>
                                                </div>
                                            </div>
                                            <div class="layui-col-xs12 layui-col-sm12 mt-1 mdui-text-color-red"
                                                 v-if="item.annotation!=''&&item.annotation!=null">
                                                {{item.annotation}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div class="card mdui-m-b-0 mdui-m-t-2">
                                <div class="card-header mdui-p-a-1" style="border-bottom:none !important;">
                                    <div @click="Form.sqid = ''" class="badge badge-success mr-1"
                                         style="cursor:pointer;">
                                        <i class="layui-icon layui-icon-return"></i>
                                    </div>
                                    请选择需要对接的商品
                                </div>
                                <div class="card-body layui-form layui-form-pane">
                                    <div class="layui-form-item" v-for="(item,index) in DockIngInput">
                                        <label class="layui-form-label">{{ item.name }}</label>
                                        <div class="layui-input-block" v-if="item.type==2">
                                            <div style="width:80%;display:inline-block">
                                                <select :name="index" lay-search v-model="Form.extend[index]"
                                                        :lay-filter="index">
                                                    <option value="">请选择(可删除文字搜索下拉框内容)</option>
                                                </select>
                                            </div>
                                            <div style="width:20%;display:inline-block">
                                                <span class="input-group-text"
                                                      style="cursor: pointer;background-color: #f47e49;color: white;width: 100%;height: 100%;display: block;line-height: 200%"
                                                      @click="ListRequest(item,index)">{{ item.BtnName }}</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else class="layui-input-block">
                                            <input v-model="Form.extend[index]" type="text" :placeholder="item.reminder"
                                                   class="layui-input"/>
                                        </div>
                                        <div v-if="item.type==2">
                                            <span v-for="(v,i) in item.button">
                                                <span @click="ListRequest(item,index,v)" :class="v.class"
                                                      style="cursor:pointer;font-size:0.8em;font-weight:500;margin-right:0.3em;">{{v.name}}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-show="Form.deliver==2">
                        <div class="mdui-textfield">
                            <label class="mdui-textfield-label">需要访问的URL地址</label>
                            <textarea v-model="Form.extend.url" class="mdui-textfield-input"></textarea>
                        </div>
                        <div class="mdui-textfield">
                            <label class="mdui-textfield-label">请求头信息 Header内容</label>
                            <textarea v-model="Form.extend.header" class="mdui-textfield-input"></textarea>
                            <div class="mdui-textfield-helper">没有可留空，一行一规则，具体参考下方填写说明</div>
                        </div>
                        <div class="mdui-textfield">
                            <label class="mdui-textfield-label">请求Post数据</label>
                            <textarea v-model="Form.extend.post" class="mdui-textfield-input"></textarea>
                            <div class="mdui-textfield-helper">没有可留空，一行一规则，具体参考下方填写说明</div>
                        </div>

                        <div class="mdui-panel" mdui-panel>
                            <div class="mdui-panel-item">
                                <div class="mdui-panel-item-header">可用变量列表</div>
                                <div class="mdui-panel-item-body">
                                    <div class="mdui-table-fluid" style="white-space:nowrap;">
                                        <table class="mdui-table mdui-table-hoverable">
                                            <thead>
                                            <tr>
                                                <th>变量</th>
                                                <th>示例</th>
                                                <th>类型</th>
                                                <th>描述</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><span @click="copyToClip('[name]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [name]
                                                        </span>
                                                </td>
                                                <td>测试商品</td>
                                                <td>String</td>
                                                <td>当前商品名称</td>
                                            </tr>
                                            <tr>
                                                <td><span @click="copyToClip('[share]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [share]
                                                        </span>
                                                </td>
                                                <td>10</td>
                                                <td>Number</td>
                                                <td>商品购买份数</td>
                                            </tr>
                                            <tr>
                                                <td><span @click="copyToClip('[number]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [number]
                                                        </span>
                                                </td>
                                                <td>100</td>
                                                <td>Number</td>
                                                <td>每份商品的发货数量</td>
                                            </tr>
                                            <tr>
                                                <td><span @click="copyToClip('[total]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [total]
                                                        </span>
                                                </td>
                                                <td>1000</td>
                                                <td>Number</td>
                                                <td>发货总数，计算公式：购买份数 × 每份数量</td>
                                            </tr>
                                            <tr>
                                                <td><span @click="copyToClip('[order]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [order]
                                                        </span>
                                                </td>
                                                <td>2020042907071396669354</td>
                                                <td>Number</td>
                                                <td>本地订单号</td>
                                            </tr>
                                            <tr>
                                                <td><span @click="copyToClip('[price]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [price]
                                                        </span>
                                                </td>
                                                <td>1.5</td>
                                                <td>Float</td>
                                                <td>用户付款金额</td>
                                            </tr>
                                            <tr>
                                                <td><span @click="copyToClip('[url]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [url]
                                                        </span>
                                                </td>
                                                <td><?= href() ?></td>
                                                <td>String</td>
                                                <td>当前站点域名</td>
                                            </tr>
                                            <tr>
                                                <td><span @click="copyToClip('[gid]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [gid]
                                                        </span>
                                                </td>
                                                <td>100</td>
                                                <td>Number</td>
                                                <td>当前商品编号</td>
                                            </tr>
                                            <tr>
                                                <td><span @click="copyToClip('[time]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [time]
                                                        </span>
                                                </td>
                                                <td><?= time() ?></td>
                                                <td>Number</td>
                                                <td>当前时间戳</td>
                                            </tr>
                                            <tr>
                                                <td><span @click="copyToClip('[input]')"
                                                          style="color: #114ff8;cursor: pointer;">
                                                            [input]
                                                        </span>
                                                </td>
                                                <td>["输入框1","输入框2"]</td>
                                                <td>JSON</td>
                                                <td>订单的全部下单信息</td>
                                            </tr>
                                            <tr v-for="index in SumInput">
                                                <td>
                                                        <span @click="copyToClip('[input'+index+']')"
                                                              style="color: #114ff8;cursor: pointer;">
                                                            [input{{index}}]
                                                        </span>
                                                </td>
                                                <td>你好{{index}}</td>
                                                <td>String</td>
                                                <td>第{{index}}行输入框内容</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="mdui-panel-item">
                                <div class="mdui-panel-item-header">变量使用说明</div>
                                <div class="mdui-panel-item-body">
                                    <span class="mdui-text-color-pink">在【需要访问的URL地址】内使用：</span><br>
                                    填写：<b>http://baidu.com/?name=[name]</b><br>
                                    访问：<b>http://baidu.com/?name=商品名称</b><br>
                                    说明：在此输入框内使用，会直接输出对应变量，按需使用即可<br>
                                    <hr>
                                    <span class="mdui-text-color-pink">在【请求头信息 Header内容】内使用</span><br>
                                    填写格式：<b>name|[name]</b><br>
                                    请求头：<b>name:商品名称</b><br>
                                    <span class="mdui-text-color-green">可多行填写，一行一规则</span><br>
                                    多行填写示例(回车换行)：<br>
                                    <pre class="layui-code">token|123456
name|[name]
price|[price]</pre>
                                    Header请求头：<br>
                                    <pre class="layui-code">token: 123456
name: 商品名称
price: 订单付款金额</pre>
                                    <hr>
                                    <span class="mdui-text-color-pink">在【请求Post数据】内使用：</span><br>
                                    填写格式：<b>name|[name]</b><br>
                                    请求参数：<b>name:商品名称</b><br>
                                    <span class="mdui-text-color-green">可多行填写，一行一规则</span><br>
                                    多行填写示例(回车换行)：<br>
                                    <pre class="layui-code">token|123456
name|[name]
price|[price]</pre>
                                    body请求参数：<br>
                                    <pre class="layui-code">token: 123456
name: 商品名称
price: 订单付款金额</pre>
                                    <hr>
                                    请求变量支持在三个输入框内使用，第二三个输入框填写方式相同，一行一规则，每行键值用【|】分割！
                                </div>
                            </div>
                            <div class="mdui-panel-item">
                                <div class="mdui-panel-item-header">返回参数格式</div>
                                <div class="mdui-panel-item-body">
                                    <b>订单处理状态判断参数：</b><br>
                                    <span class="mdui-text-color-red">state，code，status</span> <br>
                                    状态参数状态码大于或等于 0 均算为订单处理成功！
                                    <hr>
                                    <b>订单处理结果判断参数：</b><br>
                                    <span class="mdui-text-color-red">msg，message，result</span> <br>
                                    以上参数均可返回订单处理结果的文字说明
                                    <hr>
                                    <b>其他杂项返回参数：</b><br>
                                    <b>order</b>：可返回对接方的订单号，会存储至本地订单信息内，方便后续查单！<br>
                                    <b>money</b>：可返回当前对接账户的余额<br>
                                    <b>price</b>：可返回处理当前订单任务消耗的金额,会写入至本地订单成本内！<br>
                                    <b>kami</b>：可返回相关的卡密信息，数组格式，一行一卡，如：<span class="mdui-text-color-pink">["卡1","卡2","卡3"]</span><br>
                                    杂项参数不会强制要求返回，如果没有，则本地无法查看参数的对应信息，当返回卡密参数后，卡密内容会写入到本地卡密库存内容，供用户查询！
                                    <pre class="layui-code">
{
    "code": 1,
    "message": "商品购买成功！",
    "order": 2.0200502032807247e+21,
    "money": 1.5,
    "price": 0.5,
    "kami": [
        "卡1",
        "卡2"
    ]
}
</pre>
                                    <pre class="layui-code">
{
    "code": -1,
    "message": "商品购买失败，余额不足！",
}
</pre>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div v-show="Form.deliver==3">
                        <div class="card mdui-m-t-2">
                            <div class="card-body">
                                <span class="font-13">1、如果配置了邮件通知插件(应用商店内安装)，则用户购买卡密后，如果发卡成功，则进行邮件发卡</span>
                                <span class="font-13"><br><br>2、如果未配置邮件通知，则用户需主动前往订单列表内查询卡密！</span>
                                <span class="font-13"><br><br>3、卡密商品添加成功后，可在商品列表 <b>发货方法</b>列点击
                                    <a href="admin.goods.token.php" title="点击补卡" class="badge badge-warning-lighten"
                                       target="_blank">自动发卡</a> 管理卡密库存！</span>
                            </div>
                        </div>
                    </div>

                    <div v-show="Form.deliver==4">
                        <div class="mdui-textfield">
                            <label class="mdui-textfield-label">需显示的隐藏内容</label>
                            <textarea v-model="Form.explain" class="mdui-textfield-input"></textarea>
                            <div class="mdui-textfield-helper">支持输入HTML代码，此内容用户购买商品后可见</div>
                        </div>
                    </div>

                    <div v-show="Form.deliver==5">
                        <div v-if="ServerData.length===0">
                            空空如也
                        </div>
                        <div v-else
                             class="mdui-row-xs-2 mdui-row-sm-4 mdui-row-md-5 mdui-row-lg-6 mdui-row-xl-7 mdui-grid-list mdui-m-b-2">
                            <div class="mdui-card mdui-m-t-2 mdui-col mdui-ripple" v-for="(item,index) in ServerData">
                                <div class="mdui-card-primary">
                                    <div class="mdui-card-primary-title mdui-text-truncate">{{item.name}}</div>
                                    <div class="mdui-card-primary-subtitle mdui-text-truncate">
                                        <a :href="item.url" target="_blank" style="color: #6015ac;">{{item.url}}</a>
                                    </div>
                                </div>
                                <div class="mdui-card-actions mdui-text-center">
                                    <button v-if="ServerDataType!==item.id"
                                            @click="ServerDataType = item.id;Form.extend.id = item.id;"
                                            class="mdui-btn mdui-ripple">选择
                                    </button>
                                    <button v-else @click="ServerDataType = false;Form.extend.id = false;"
                                            class="mdui-btn mdui-ripple mdui-color-deep-purple-accent">取消
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-show="1!==1" class="mdui-textfield">
                            <label class="mdui-textfield-label">服务器节点ID</label>
                            <input v-model="Form.extend.id" class="mdui-textfield-input" disabled/>
                        </div>
                        <div class="mdui-panel" mdui-panel>
                            <div class="mdui-panel-item mdui-panel-item-open">
                                <div class="mdui-panel-item-header">
                                    <div class="mdui-panel-item-title">主机规格参数配置说明</div>
                                    <i class="mdui-panel-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
                                </div>
                                <div class="mdui-panel-item-body">
                                    <p>1、打开输入框配置，点击启用商品多规格，点击生成规格名和规格值按钮！</p>
                                    <p>2、打开后，点击紫色的 <font color="red">主机规格</font> 按钮 导入内置规格配置！</p>
                                    <p>3、导入成功后，设置成本低价，和规格值的价格等，可以根据内置模板调整参数！</p>
                                    <p>4、配置完成后，点击上方的两个输入框，复制内容，然后最小化窗口，将复制的内容，填写到对应的输入框内！</p>
                                    <h4 style="font-weight:100;">参数说明：</h4>
                                    <p>并发总数：主机空间同一时间最大可访问人数</p>
                                    <p>上行流量：主机空间最大可使用的流量上行限制！</p>
                                    <p>上传限制：主机空间后台文件管理界面最大可上传文件限制!</p>
                                    <p>域名绑定：主机空间最大可绑定域名数量限制！一个域名就可以创建一个站点！</p>
                                    <h4 style="font-weight:100;">主机开通时长说明：</h4>
                                    <p>可通过配置【每份商品的发货数量】来调整，1=1月(30天)，用户可通过购买多份，来选择开通时长，以及价格等！</p>
                                    <p>主机每月续期价格计算公式为：订单付款金额 ÷ 发货月数，取均价！</p>
                                    <p>主机发货类商品，暂时无法使用积分兑换方式购买！</p>
                                    <div class="mdui-panel-item-actions">
                                        <a href="../HostAdmin" target="_blank"
                                           class="mdui-btn mdui-ripple mdui-color-amber">打开主机管理后台</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div id="sundry" class="mdui-p-a-2">
                    <div class="mdui-list">
                        <label mdui-tooltip="{content: '是否允许用户通过在线付款方式购买此商品,在线付款包含微信,QQ,支付宝等付款方式'}"
                               class="mdui-list-item mdui-ripple">
                            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-orange">payment</i>
                            <div class="mdui-list-item-content">在线付款购买商品</div>
                            <label class="mdui-switch">
                                <input type="checkbox" v-model="Form.method[0]"/>
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </label>
                        <label mdui-tooltip="{content: '是否允许用户通过充值的余额购买此商品,关闭后将无法使用余额付款'}"
                               class="mdui-list-item mdui-ripple">
                            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-cyan">monetization_on</i>
                            <div class="mdui-list-item-content">余额付款购买商品</div>
                            <label class="mdui-switch">
                                <input type="checkbox" v-model="Form.method[1]"/>
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </label>
                        <label mdui-tooltip="{content: '是否允许用户通过积分兑换此商品？'}" class="mdui-list-item mdui-ripple">
                            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-green">brightness_high</i>
                            <div class="mdui-list-item-content">平台积分兑换商品</div>
                            <label class="mdui-switch">
                                <input type="checkbox" v-model="Form.method[2]"/>
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </label>
                        <label mdui-tooltip="{content: '是否允许此商品被其他站点对接,开启后,他人可通过API对接方式购买你的商品'}"
                               class="mdui-list-item mdui-ripple">
                            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-indigo">swap_horiz</i>
                            <div class="mdui-list-item-content">允许此商品被对接</div>
                            <label class="mdui-switch">
                                <input type="checkbox" v-model="Form.method[3]"/>
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </label>
                        <label mdui-tooltip="{content: '若对接的是其他站点,并且该站点支持价格监控的话,则可以通过监控同步库存,价格,商品状态等'}"
                               class="mdui-list-item mdui-ripple">
                            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-light-blue">settings_input_antenna</i>
                            <div class="mdui-list-item-content">开启商品价格监控</div>
                            <label class="mdui-switch">
                                <input type="checkbox" v-model="Form.method[4]"/>
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </label>
                        <label mdui-tooltip="{content: '当你主动为他人提供克隆密钥时,可控制单个商品是否支持被克隆,如果关闭,则不克隆此商品'}"
                               class="mdui-list-item mdui-ripple">
                            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-green">compare_arrows</i>
                            <div class="mdui-list-item-content">允许被其他站克隆</div>
                            <label class="mdui-switch">
                                <input type="checkbox" v-model="Form.method[5]"/>
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </label>
                        <label mdui-tooltip="{content: '是否允许用户单次购买时,一次购买多份商品?,关闭后最低/高购买份数参数将无效!'}"
                               class="mdui-list-item mdui-ripple">
                            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-red-accent">exposure</i>
                            <div class="mdui-list-item-content">允许一次购买多份</div>
                            <label class="mdui-switch">
                                <input type="checkbox" v-model="Form.method[6]"/>
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </label>
                    </div>
                </div>

                <div class="mdui-p-a-2 mdui-p-t-0" @click="StoreData()">
                    <button class="mdui-btn mdui-btn-block mdui-text-color-white mdui-ripple mdui-btn-raised"
                            :class="gid===''?' mdui-color-deep-purple-accent':' mdui-color-indigo-a400'">
                        {{ gid===''?'新增商品':'保存商品[ '+gid+' ]数据' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .mdui-select-position-auto {
        width: 100%;
    }
</style>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/js/wangEditor.min.js"></script>
<script src="../assets/admin/js/goodsadd.js?vs=<?= $accredit['versions'] ?>"></script>
