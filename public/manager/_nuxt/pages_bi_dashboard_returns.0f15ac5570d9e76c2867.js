webpackJsonp([46],{"1ckk":function(t,e,r){"use strict";e.a={props:["dimension","dimensionsObject","dimensionList","label","multiple"]}},FwsT:function(t,e,r){"use strict";var n={render:function(){var t=this,e=t.$createElement;return(t._self._c||e)("v-select",{attrs:{chips:"",multiple:t.multiple,label:t.label,items:t.dimensionList},model:{value:t.dimensionsObject[t.dimension],callback:function(e){t.$set(t.dimensionsObject,t.dimension,e)},expression:"dimensionsObject[dimension]"}})},staticRenderFns:[]};e.a=n},KHut:function(t,e,r){"use strict";var n=r("fc/v"),s=r("krCb"),i=r("VU/8")(n.a,s.a,!1,null,null,null);e.a=i.exports},Nrks:function(t,e,r){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var n=r("lDby"),s=r("ebPq"),i=r("VU/8")(n.a,s.a,!1,null,null,null);e.default=i.exports},"Wxz/":function(t,e,r){"use strict";var n=r("1ckk"),s=r("FwsT"),i=r("VU/8")(n.a,s.a,!1,null,null,null);e.a=i.exports},dbov:function(t,e){var r=Array.prototype.reverse;t.exports=function(t){return null==t?t:r.call(t)}},ebPq:function(t,e,r){"use strict";var n={render:function(){var t=this.$createElement,e=this._self._c||t;return e("v-container",{attrs:{"grid-list-xs":"",fluid:""}},[e("ReturnsDashBoard")],1)},staticRenderFns:[]};e.a=n},"fc/v":function(t,e,r){"use strict";var n=r("Dd8w"),s=r.n(n),i=r("kvU2"),a=r.n(i),o=r("dbov"),l=r.n(o),u=r("NYxO"),c=r("Wxz/"),d=r("vp33"),b=r("hlHE"),p=a()(b.b);p.legend={enabled:!1},p.xAxis={reversed:!0},e.a={data:function(){return{dimYears:b.h,monthNames:Object(b.k)(),biPax:b.c,filter:{country:"Italia",year:b.d,channel:"C",pax:"Single"},libraryMonth:b.i,libraryNoLegend:p,biChannels:b.a,library:b.b,libraryPie:{chart:{type:"pie",options3d:{enabled:!0,alpha:45,beta:0}},plotOptions:{pie:{allowPointSelect:!0,showInLegend:!1,cursor:"pointer",depth:35,dataLabels:{enabled:!0,format:"<b>{point.name}</b>: {point.percentage:.1f} %"}}}}}},components:{DimensionSelect:c.a,BiChart:d.a},computed:s()({progressivReturnsList:function(){for(var t=a()(this.onlyReturnsList),e=1;e<t.length;e++)t[e].value+=t[e-1].value,t[e].items+=t[e-1].items;return l()(t)},onlyReturnsList:function(){return console.dir(this.returnsList),this.returnsList.filter(function(t){return"RITORNI"===t.serie})}},Object(u.mapState)("bi/dashreturns",["returnsList"]))}},krCb:function(t,e,r){"use strict";var n={render:function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("v-layout",{attrs:{rows:"",wrap:""}},[r("v-toolbar",{staticClass:"mb-3 elevation-1",attrs:{dense:""}},[r("v-toolbar-title",[r("v-icon",[t._v("dashboard")]),t._v("\n            "+t._s(t.$t("Analisi dei ritorni annuali"))+"\n        ")],1)],1),r("v-flex",{attrs:{xs12:""}},[r("bi-chart",{attrs:{type:"column-chart",data:t.returnsList,transform:!0,stacked:!0,"sub-title":t.$t("Revenue"),title:t.$t("Ritorni x anno")}})],1),r("v-flex",{attrs:{xs12:""}},[r("bi-chart",{attrs:{type:"column-chart",data:t.returnsList,transform:!0,stacked:!0,count:!0,"sub-title":t.$t("N° Ritorni"),title:t.$t("Ritorni")}})],1),r("v-flex",{attrs:{xs12:""}},[r("bi-chart",{attrs:{type:"column-chart",data:t.progressivReturnsList,transform:!0,stacked:!0,library:t.libraryNoLegend,"sub-title":t.$t("Revenue"),title:t.$t("Ritorni")+" al "+t.filter.year}})],1),r("v-flex",{attrs:{xs12:""}},[r("bi-chart",{attrs:{type:"column-chart",data:t.progressivReturnsList,transform:!0,stacked:!0,library:t.libraryNoLegend,count:!0,"sub-title":t.$t("N° Ritorni"),title:t.$t("Ritorni")+" al "+t.filter.year}})],1)],1)},staticRenderFns:[]};e.a=n},lDby:function(t,e,r){"use strict";var n=r("Xxa5"),s=r.n(n),i=r("exGp"),a=r.n(i),o=r("KHut");e.a={components:{ReturnsDashBoard:o.a},fetch:function(){var t=a()(s.a.mark(function t(e){var r=e.store;return s.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,r.dispatch("bi/dashreturns/loadData",{},{root:!0});case 2:case"end":return t.stop()}},t,this)}));return function(e){return t.apply(this,arguments)}}()}}});