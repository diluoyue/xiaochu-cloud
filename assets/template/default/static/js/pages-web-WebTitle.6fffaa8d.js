(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-web-WebTitle"],{"127a":function(t,e,n){"use strict";n("ac1f"),n("1276"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r={data:function(){return{url:""}},onLoad:function(t){if(void 0==t.url||void 0==t.title)uni.navigateBack({delta:1});else{var e=this.str_decrypt(t.url),n=e.split("/");""==n[0]||"."==n[0]||".."==n[0]?this.url=getApp().globalData.domain+e:this.url=e,uni.setNavigationBarTitle({title:t.title})}},methods:{str_decrypt:function(t){t=decodeURIComponent(t);for(var e=String.fromCharCode(t.charCodeAt(0)-t.length),n=1;n<t.length;n++)e+=String.fromCharCode(t.charCodeAt(n)-e.charCodeAt(n-1));return e}}};e.default=r},"6cda":function(t,e,n){"use strict";var r;n.d(e,"b",(function(){return a})),n.d(e,"c",(function(){return i})),n.d(e,"a",(function(){return r}));var a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",[n("v-uni-web-view",{attrs:{src:t.url}})],1)},i=[]},"7c21":function(t,e,n){"use strict";n.r(e);var r=n("6cda"),a=n("cac6");for(var i in a)"default"!==i&&function(t){n.d(e,t,(function(){return a[t]}))}(i);var u,c=n("f0c5"),o=Object(c["a"])(a["default"],r["b"],r["c"],!1,null,"048bf34e",null,!1,r["a"],u);e["default"]=o.exports},cac6:function(t,e,n){"use strict";n.r(e);var r=n("127a"),a=n.n(r);for(var i in r)"default"!==i&&function(t){n.d(e,t,(function(){return r[t]}))}(i);e["default"]=a.a}}]);