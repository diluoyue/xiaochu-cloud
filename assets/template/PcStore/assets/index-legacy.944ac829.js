!function(){var e=document.createElement("style");e.innerHTML=".Banner{background-color:#fff;padding:0;border-radius:1em;overflow:hidden;box-shadow:0 2px 4px #00000005}.box-card{width:100%;height:100%;border-radius:1em;box-shadow:0 2px 4px #00000005!important;padding:0;border:none!important}.card-header{background-image:url(./banner-1.87603609.svg);background-repeat:repeat-x;background-size:100%;height:46px;display:flex;align-items:center;color:#fff;padding:0 16px}.Price-Box .el-card__header,.Price-Box .el-card__body,.Price-Box .el-empty{padding:0}.Price-Box-Div{width:100%;height:2em;line-height:2em;margin-bottom:.5em;display:block;font-size:14px;font-weight:500;color:#333;margin-top:.2em}.imagebodong{margin-right:.5em}.Price{height:20px;line-height:20px;font-size:20px;font-weight:500;align-items:baseline;color:#ff6650}.PriceSales{float:right;margin-top:-20px;color:#b1b1b1;font-size:.8em}\n",document.head.appendChild(e),System.register(["./vendor-legacy.839ca20f.js"],(function(e){"use strict";var t,i,o,a,l,s,n,d,r,c,p,m,y,h,f;return{setters:[function(e){t=e.r,i=e.k,o=e.o,a=e.b,l=e.d,s=e.w,n=e.F,d=e.m,r=e.n,c=e.e,p=e.p,m=e.q,y=e.t,h=e.g,f=e.s}],execute:function(){const u=e("default",{data:()=>({Banner:[],CommodityVolatility:{Data:[],List:[]},CommodityVolatilityName:-1,CommodityVolatilityState:!0,Class:[],CouponData:[],GoodsList:[],cid:-1,GoodsState:!1,Page:1,GoodsType:!0,Content:"",State:!1,ActivitiesGoods:0,Prices:[],svg:'\n\t\t\t          <path class="path" d="\n\t\t\t            M 30 15\n\t\t\t            L 28 17\n\t\t\t            M 25.61 25.61\n\t\t\t            A 15 15, 0, 0, 1, 15 30\n\t\t\t            A 15 15, 0, 1, 1, 27.99 7.5\n\t\t\t            L 15 15\n\t\t\t          " style="stroke-width: 4px; fill: rgba(0, 0, 0, 0)"/>\n\t\t\t        '}),created(){this.BannerGet(),this.CommodityVolatilityGet(),this.ClassList(),this.GoodsListGet(),this.ActivitiesGoodsGet(),""!==this.$ConfData.Conf.PopupNotice&&void 0!==this.$ConfData.Conf.PopupNotice&&null!==this.$ConfData.Conf.PopupNotice&&(this.Content=this.$ConfData.Conf.PopupNotice,this.State=!0)},methods:{Open(e){this.$router.push({path:"goods",query:{gid:e}})},SelectCategories(e=-1){e!==this.cid&&(this.cid=e,this.Page=1,this.GoodsType=!0,this.GoodsList=[],-2==e?this.ActivitiesGoodsGet():this.GoodsListGet())},GoodsLoad(e){!0===this.GoodsType&&(++this.Page,this.GoodsListGet())},Money(e){var t;return t=this.Price(e).price,(t-=t*(e.Seckill.depreciate/100)).toFixed(e.accuracy)-0},Price(e){var t,i,o;return void 0!==this.Prices[e.gid]||(1==e.method?0==e.price||0==e.points?(t="#43A047;font-size: 90%;",i="免费领取",o=2):(t="#ff0000",i=e.price,o=1):2==e.method?0==e.price?(t="#43A047;font-size: 90%;",i="免费领取",o=2):(t="#ff0000",i=e.price,o=1):3==e.method&&(0==e.points?(t="#43A047;font-size: 90%;",i="免费领取",o=2):(t="#ff0000",i=e.points,o=3)),this.Prices[e.gid]={color:t,price:i,state:o}),this.Prices[e.gid]},ClassList(){let e=this;this.$ajax.post("main.php?act=class",{num:9999}).then((function(t){t.code>=0&&(e.Class=t.data)}))},ActivitiesGoodsGet(){var e=this;this.$ajax.post("main.php?act=ActivitiesGoods").then((t=>{if(t.code>=0){if(e.GoodsState=!1,t.code>=0){for(let i=0;i<t.data.length;i++)e.GoodsList.push(t.data[i]);0===e.ActivitiesGoods&&(e.ActivitiesGoods=t.Seckill),0===t.data.length&&(e.GoodsType=!1)}}else e.ActivitiesGoods=[]}))},GoodsListGet(){this.GoodsState=!0;let e=this;this.$ajax.post("main.php?act=GoodsList",{page:this.Page,SortingType:0,Sorted:1,cid:this.cid}).then((function(t){if(e.GoodsState=!1,t.code>=0){for(let i=0;i<t.data.length;i++)e.GoodsList.push(t.data[i]);0===t.data.length&&(e.GoodsType=!1)}}))},BannerGet(){let e=this;this.$ajax.post("main.php?act=banner").then((function(t){t.code>=0&&(e.Banner=t.data,document.title=t.title)}))},CommodityVolatilityGet(e=""){let t=this;""!=e&&(t.CommodityVolatilityName=e),this.CommodityVolatilityState=!0,this.$ajax.post("main.php?act=ChangesCommodityPrices",{name:t.CommodityVolatilityName}).then((function(e){t.CommodityVolatilityState=!1,e.code>=0&&(t.CommodityVolatility=e.data,t.CommodityVolatilityName=e.ListName)}))}}}),g=["href"],x={key:1,style:{width:"100%",height:"300px"}},k={class:"card-header"},v=c("img",{src:"/assets/template/PcStore/assets/bodong-1.04004567.svg",class:"imagebodong"},null,-1),_=c("span",null,"价格波动",-1),C=c("i",{class:"el-icon-arrow-down el-icon--right"},null,-1),b={key:0},P={style:{width:"100%"}},G={style:{width:"100%","text-align":"center"}},w=h("上架中"),z=h("已下架"),S={key:1,style:{height:"254px"}},L=h(" 全部商品 "),F=h(" 限购秒杀 "),V={style:{padding:"14px 0 0 0"}},N={key:0},A={key:0},B={style:{color:"#9e9e9e","text-decoration":"line-through","font-size":"8px","margin-left":"4px"}},D={key:1},$={key:2},T={style:{"font-size":"13px",color:"#f4a300"}},M={style:{color:"#9e9e9e","text-decoration":"line-through","font-size":"8px","margin-left":"4px"}},q={style:{"font-size":"10px",color:"#f4a300","margin-left":"4px"}},j={key:1},U={key:0},H={key:1},O={key:2},E={style:{"font-size":"13px",color:"#f4a300"}},I={style:{"font-size":"10px",color:"#f4a300","margin-left":"4px"}},J={key:2,class:"PriceSales"},K={style:{"margin-top":"0.5em"}},Q=h("库存充足"),R=h("暂无库存"),W={key:1,style:{width:"76%"}},X={key:2,style:{"text-align":"center","margin-top":"1em",color:"#999"}},Y=h(" 载入中 "),Z={key:1,style:{"background-color":"#FFFFFF"}},ee=["innerHTML"],te={class:"dialog-footer"},ie=h("我已知晓");u.render=function(e,u,oe,ae,le,se){const ne=t("el-image"),de=t("el-carousel-item"),re=t("el-carousel"),ce=t("el-col"),pe=t("el-tooltip"),me=t("el-dropdown-item"),ye=t("el-dropdown-menu"),he=t("el-dropdown"),fe=t("n-ellipsis"),ue=t("el-tag"),ge=t("el-row"),xe=t("el-descriptions-item"),ke=t("el-descriptions"),ve=t("el-popover"),_e=t("el-scrollbar"),Ce=t("el-empty"),be=t("el-card"),Pe=t("el-menu-item"),Ge=t("el-menu"),we=t("el-aside"),ze=t("el-icon"),Se=t("el-main"),Le=t("el-container"),Fe=t("el-button"),Ve=t("el-dialog"),Ne=i("loading"),Ae=i("infinite-scroll");return o(),a(n,null,[l(ge,{style:{padding:"0",margin:"0"},gutter:20},{default:s((()=>[l(ce,{span:17,class:"Banner",style:{padding:"0"}},{default:s((()=>[le.Banner.length>=1?(o(),d(re,{key:0,height:"300px",style:{width:"100%",padding:"0"}},{default:s((()=>[(o(!0),a(n,null,r(le.Banner,((e,t)=>(o(),d(de,{key:t},{default:s((()=>[c("a",{href:e.url,target:"_blank"},[l(ne,{style:{height:"300px",width:"100%"},src:e.image},null,8,["src"])],8,g)])),_:2},1024)))),128))])),_:1})):p((o(),a("div",x,null,512)),[[Ne,!0]])])),_:1}),l(ce,{span:7,style:{padding:"0 0 0 1em"}},{default:s((()=>[p(l(be,{class:"box-card Price-Box"},{header:s((()=>[c("div",k,[v,_,-1!=le.CommodityVolatilityName&&le.CommodityVolatility.List.length>=1?(o(),d(he,{key:0,trigger:"click",style:{color:"#FFFFFF",position:"absolute",right:"1em"}},{dropdown:s((()=>[l(ye,null,{default:s((()=>[(o(!0),a(n,null,r(le.CommodityVolatility.List,((e,t)=>(o(),d(me,{onClick:t=>se.CommodityVolatilityGet(e),key:t},{default:s((()=>[c("span",{style:m(e===le.CommodityVolatilityName?"color:red":"")},y(e),5)])),_:2},1032,["onClick"])))),128))])),_:1})])),default:s((()=>[l(pe,{effect:"light",content:"点击选择日期",placement:"left"},{default:s((()=>[c("span",null,[h(y(le.CommodityVolatilityName)+" ",1),C])])),_:1})])),_:1})):f("",!0)])])),default:s((()=>[le.CommodityVolatility.Data.length>=1?(o(),a("div",b,[l(_e,{height:"254px;"},{default:s((()=>[(o(!0),a(n,null,r(le.CommodityVolatility.Data,((e,t)=>(o(),a("div",{class:"Price-Box-Div",key:t},[l(ve,{placement:"left",width:"350px",trigger:"hover"},{reference:s((()=>[l(ge,{gutter:20,onClick:t=>se.Open(e.Gid),style:{width:"100%",margin:"auto",cursor:"pointer"},title:"查看详情"},{default:s((()=>[l(ce,{span:15},{default:s((()=>[l(fe,{style:{"max-width":"100%"}},{default:s((()=>[h(y(e.Name),1)])),_:2},1024)])),_:2},1024),l(ce,{span:9,style:{"text-align":"right","padding-right":"0.5em"}},{default:s((()=>[1===e.type?(o(),d(ue,{key:0,size:"mini",effect:"plain","disable-transitions":"",type:"danger"},{default:s((()=>[h(" 涨价 "+y((e.NewPrice-e.UsedPrice).toFixed(3))+"元 ",1)])),_:2},1024)):(o(),d(ue,{key:1,size:"mini",effect:"plain","disable-transitions":"",type:"success"},{default:s((()=>[h(" 降价 "+y((e.UsedPrice-e.NewPrice).toFixed(3))+"元 ",1)])),_:2},1024))])),_:2},1024)])),_:2},1032,["onClick"])])),default:s((()=>[c("div",P,[c("div",G,[l(ne,{style:{width:"100px",height:"100px"},src:e.image},null,8,["src"])]),c("div",null,[l(ke,{title:e.Name,size:"mini",direction:"vertical",column:4,border:""},{default:s((()=>[l(xe,{label:"商品编号"},{default:s((()=>[h(y(e.Gid),1)])),_:2},1024),l(xe,{label:"商品状态"},{default:s((()=>[1==e.state?(o(),d(ue,{key:0,type:"success",size:"mini",effect:"dark"},{default:s((()=>[w])),_:1})):(o(),d(ue,{key:1,type:"danger",size:"mini",effect:"dark"},{default:s((()=>[z])),_:1}))])),_:2},1024),l(xe,{label:"当前价格"},{default:s((()=>[l(ue,{size:"mini",effect:"dark"},{default:s((()=>[h(y(e.NewPrice)+"元",1)])),_:2},1024)])),_:2},1024),l(xe,{label:"历史价格"},{default:s((()=>[l(ue,{type:"info",size:"mini",effect:"dark"},{default:s((()=>[h(y(e.UsedPrice)+"元",1)])),_:2},1024)])),_:2},1024),l(xe,{label:"波动状态"},{default:s((()=>[1===e.type?(o(),d(ue,{key:0,size:"mini","disable-transitions":"",type:"danger"},{default:s((()=>[h(" 涨价 "+y((e.NewPrice-e.UsedPrice).toFixed(3))+"元 ",1)])),_:2},1024)):(o(),d(ue,{key:1,size:"mini","disable-transitions":"",type:"success"},{default:s((()=>[h("降价 "+y((e.UsedPrice-e.NewPrice).toFixed(3))+"元",1)])),_:2},1024))])),_:2},1024),0!=e.key?(o(),d(xe,{key:0,label:"规格组合"},{default:s((()=>[h(y(e.key),1)])),_:2},1024)):f("",!0),l(xe,{label:"波动时间"},{default:s((()=>[h(y(e.date),1)])),_:2},1024)])),_:2},1032,["title"])])])])),_:2},1024)])))),128))])),_:1})])):(o(),a("div",S,[l(Ce,{description:"当日的商品价格很平稳"})]))])),_:1},512),[[Ne,le.CommodityVolatilityState]])])),_:1}),l(ce,{span:24,style:{padding:"0","margin-top":"1em","border-radius":"1em",overflow:"hidden"}},{default:s((()=>[le.Class.length>=1?(o(),d(Le,{key:0,style:{border:"1px solid #eee","background-color":"#FFFFFF"}},{default:s((()=>[l(we,{width:"220px"},{default:s((()=>[l(Ge,{"default-active":le.cid,"active-text-color":"#ff5500"},{default:s((()=>[l(Pe,{onClick:u[0]||(u[0]=e=>se.SelectCategories(-1)),index:"-1"},{title:s((()=>[L])),_:1}),le.ActivitiesGoods>=1?(o(),d(Pe,{key:0,onClick:u[1]||(u[1]=e=>se.SelectCategories(-2)),index:"-2"},{title:s((()=>[F])),_:1})):f("",!0),(o(!0),a(n,null,r(le.Class,((e,t)=>(o(),d(Pe,{key:t,index:e.cid,onClick:t=>se.SelectCategories(e.cid)},{title:s((()=>[h(y(e.name),1)])),_:2},1032,["index","onClick"])))),128))])),_:1},8,["default-active"])])),_:1}),p(l(Le,{style:{"padding-bottom":"1em"}},{default:s((()=>[p(l(Se,{"element-loading-svg":le.svg,"element-loading-svg-view-box":"-10, -10, 50, 50"},{default:s((()=>[le.GoodsList.length>=1?(o(),d(ge,{key:0,gutter:12},{default:s((()=>[(o(!0),a(n,null,r(le.GoodsList,((e,t)=>(o(),d(ce,{span:6,key:t,style:{"margin-bottom":"10px"}},{default:s((()=>[l(be,{onClick:t=>se.Open(e.gid),shadow:"hover","body-style":{padding:"10px",cursor:"pointer"}},{default:s((()=>[l(ne,{lazy:"",style:{width:"100%",height:"200px"},title:e.name,src:e.image,fit:"cover"},null,8,["title","src"]),c("div",V,[l(fe,{style:{"max-width":"100%","line-height":"22px","font-size":"16px","font-weight":"500",color:"#333","margin-bottom":"0.5em","text-indent":"0.2em"}},{default:s((()=>[h(y(e.name),1)])),_:2},1024),-2==le.cid&&le.GoodsList.length>=1?(o(),a("div",N,[1===se.Price(e).state?(o(),a("div",A,[c("span",{class:"Price",style:m("color:"+se.Price(e).color)},"￥"+y(se.Money(e)),5),c("span",B,y(se.Price(e).price),1)])):2===se.Price(e).state?(o(),a("div",D,[c("span",{style:m("color:"+se.Price(e).color)},y(se.Price(e).price),5)])):(o(),a("div",$,[c("span",T,y(se.Money(e)),1),c("span",M,y(se.Price(e).price),1),c("span",q,y(e.currency),1)]))])):(o(),a("div",j,[1===se.Price(e).state?(o(),a("div",U,[c("span",{class:"Price",style:m("color:"+se.Price(e).color)},"￥"+y(se.Price(e).price),5)])):2===se.Price(e).state?(o(),a("div",H,[c("span",{style:m("color:"+se.Price(e).color)},y(se.Price(e).price),5)])):(o(),a("div",O,[c("span",E,y(se.Price(e).price),1),c("span",I,y(e.currency),1)]))])),e.sales>=1?(o(),a("div",J,"销量"+y(e.sales>=1e3?"1万+":e.sales),1)):f("",!0),c("div",K,[l(ue,{size:"mini",style:{"margin-right":"0.5em"}},{default:s((()=>[h("每份"+y(e.quantity)+y(e.units),1)])),_:2},1024),e.quota>=100?(o(),d(ue,{key:0,size:"mini",type:"success"},{default:s((()=>[Q])),_:1})):e.quota>0&&e.quota<100?(o(),d(ue,{key:1,size:"mini",type:"danger"},{default:s((()=>[h("库存较少("+y(e.quota)+")",1)])),_:2},1024)):(o(),d(ue,{key:2,size:"mini",type:"info"},{default:s((()=>[R])),_:1})),-2==le.cid&&le.GoodsList.length>=1?(o(),d(ue,{key:3,size:"mini",type:"danger",style:{"margin-left":"0.5em"}},{default:s((()=>[h(" 优惠"+y(e.Seckill.depreciate)+"% ",1)])),_:2},1024)):f("",!0)])])])),_:2},1032,["onClick"])])),_:2},1024)))),128))])),_:1})):(o(),a("div",W,[l(Ce,{description:"此分类一个商品也没有"})])),le.GoodsState&&le.GoodsList.length>=1?(o(),a("div",X,[Y,l(ze,{color:"#999",class:"el-icon-loading"})])):f("",!0)])),_:1},8,["element-loading-svg"]),[[Ne,le.GoodsState&&0===le.GoodsList.length]])])),_:1},512),[[Ae,se.GoodsLoad]])])),_:1})):(o(),a("div",Z,[l(Ce,{description:"当前站点一个商品也没有"})]))])),_:1})])),_:1}),l(Ve,{title:"系统公告","append-to-body":"",center:"",modal:"",modelValue:le.State,"onUpdate:modelValue":u[3]||(u[3]=e=>le.State=e),width:"560px"},{footer:s((()=>[c("span",te,[l(Fe,{class:"ant-btn-primary",type:"danger",size:"medium",onClick:u[2]||(u[2]=e=>le.State=!1)},{default:s((()=>[ie])),_:1})])])),default:s((()=>[c("div",{innerHTML:le.Content},null,8,ee)])),_:1},8,["modelValue"])],64)}}}}))}();