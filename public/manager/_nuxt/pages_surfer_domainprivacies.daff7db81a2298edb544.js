webpackJsonp([13],{"0QMV":function(t,e,s){var a=s("2s93");"string"==typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);s("rjj0")("24829394",a,!0,{sourceMap:!1})},"0z4b":function(t,e,s){"use strict";var a=s("981S"),r=s("KceB"),n=s("VU/8")(a.a,r.a,!1,null,null,null);e.a=n.exports},"21M4":function(t,e,s){"use strict";var a=s("UbEG"),r=s("k7t3");var n=function(t){s("BCRM")},i=s("VU/8")(a.a,r.a,!1,n,null,null);e.a=i.exports},"2BmN":function(t,e,s){"use strict";e.a={name:"SubscriptionFixedForm"}},"2s93":function(t,e,s){(t.exports=s("FZ+f")(!1)).push([t.i,".bigger-check{position:relative;top:3px;width:16px;height:16px}.owner-user-form-terms-data-table{padding:0;background:transparent;width:100%;border:1px solid silver;border-spacing:0;border-collapse:collapse}.owner-user-form-terms-data-table th{text-transform:uppercase}.owner-user-form-terms-data-table td,.owner-user-form-terms-data-table th{background:#fff;padding:3px 3px 3px 5px;border-spacing:0;border:1px solid silver;text-align:left;vertical-align:baseline}",""])},"4dq0":function(t,e,s){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var a=s("uraA"),r=s("pdP+"),n=s("VU/8")(a.a,r.a,!1,null,null,null);e.default=n.exports},"6LEm":function(t,e,s){"use strict";var a=s("XLdm"),r=s("f1oe"),n=s("VU/8")(a.a,r.a,!1,null,null,null);e.a=n.exports},"7pg/":function(t,e,s){"use strict";var a=s("2BmN"),r=s("RZ8+");var n=function(t){s("ZAly")},i=s("VU/8")(a.a,r.a,!1,n,"data-v-e33b2ffc",null);e.a=i.exports},"981S":function(t,e,s){"use strict";e.a={components:{},props:[]}},AwqC:function(t,e,s){(t.exports=s("FZ+f")(!1)).push([t.i,".subscription-title{border-bottom:4px solid #e1e1e1}.subscription-title-revoke-all{background:#2e879d!important;font-size:11px;position:relative;top:-6px}",""])},BCRM:function(t,e,s){var a=s("o9cn");"string"==typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);s("rjj0")("5298a995",a,!0,{sourceMap:!1})},F8Oc:function(t,e,s){"use strict";var a=s("b+Qv"),r=s("yR21");var n=function(t){s("XERY")},i=s("VU/8")(a.a,r.a,!1,n,"data-v-33334a88",null);e.a=i.exports},KceB:function(t,e,s){"use strict";var a={render:function(){var t=this.$createElement;return(this._self._c||t)("div",[this._v("\neditor\n    ")])},staticRenderFns:[]};e.a=a},M9La:function(t,e,s){"use strict";var a=s("o8fq"),r=s("eyq5"),n=s("VU/8")(a.a,r.a,!1,null,null,null);e.a=n.exports},"RZ8+":function(t,e,s){"use strict";var a={render:function(){var t=this.$createElement;return(this._self._c||t)("div",[this._v("\n    fform\n")])},staticRenderFns:[]};e.a=a},TcwD:function(t,e,s){var a=s("AwqC");"string"==typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);s("rjj0")("79c9336a",a,!0,{sourceMap:!1})},UbEG:function(t,e,s){"use strict";e.a={components:{},props:{hideCancel:{default:!1},hideDelete:{default:!1},editMode:Boolean,color:{default:"grey darken-1"}},methods:{onEditClick:function(){this.$emit("edit")},onDeleteClick:function(){this.$emit("delete")},onSaveClick:function(){this.$emit("save")},onCancelClick:function(){this.$emit("cancel")}}}},XERY:function(t,e,s){var a=s("ft4A");"string"==typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);s("rjj0")("7b233354",a,!0,{sourceMap:!1})},XLdm:function(t,e,s){"use strict";e.a={components:{},props:[]}},ZAly:function(t,e,s){var a=s("ofvO");"string"==typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);s("rjj0")("0076580b",a,!0,{sourceMap:!1})},"b+Qv":function(t,e,s){"use strict";var a=s("Dd8w"),r=s.n(a),n=s("y9sX"),i=s("uVDJ"),o=s("6LEm"),c=s("7pg/"),l=s("M9La"),u=s("0z4b"),d=s("lHK6"),p=s.n(d),v=s("NYxO");e.a={name:"Subscription",methods:r()({},Object(v.mapActions)("owners/users",["unsubscribeAll","unsubscribeNewsletters"]),{onUnsubNewsletters:function(){var t=this;confirm(this.$t("Do you confirm?"))&&this.unsubscribeNewsletters().then(function(){t.$router.replace("/surfer/unnewslettersdone")})},onUnsubscribeAll:function(){var t=this;confirm(this.$t("Do you confirm?"))&&this.unsubscribeAll().then(function(){t.$router.replace("/surfer/unallrequestsent")})}}),computed:r()({},Object(v.mapState)("api",["hasError"]),Object(v.mapState)("owners/users",["recordList"]),Object(v.mapGetters)("owners/users",{$record:"getLastSubscription",hasNewsLetters:"hasNewsLettersTermsDomainObject"}),{hasPrivacies:function(){return!p()(this.recordList)}}),components:{SubscriptionTitle:i.a,SubscriptionDynaForm:o.a,SubscriptionFixedForm:c.a,SubscriptionRecap:l.a,SubscriptionTermEditor:u.a,UserFormTerms:n.a}}},eyq5:function(t,e,s){"use strict";var a={render:function(){var t=this.$createElement,e=this._self._c||t;return e("v-layout",[e("v-flex",[this._v("\n        "+this._s(this.rec)+"\n    ")])],1)},staticRenderFns:[]};e.a=a},f1oe:function(t,e,s){"use strict";var a={render:function(){var t=this.$createElement;return(this._self._c||t)("div",[this._v("\ndform\n    ")])},staticRenderFns:[]};e.a=a},ft4A:function(t,e,s){(t.exports=s("FZ+f")(!1)).push([t.i,"",""])},h984:function(t,e,s){"use strict";var a={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("v-container",{staticClass:"pa-0 subscription-title",attrs:{dark:"",fluid:"","grid-list-sm":""}},[s("v-layout",{attrs:{row:"",wrap:""}},[s("v-flex",{staticClass:"headline pt-0 mb-2"},[s("b",[t._v(t._s(t.name)+" "+t._s(t.surname)+": "+t._s(t.$t("Here are your subscriptions to the regulations of the site"))+" "+t._s(t.domain))])])],1),s("v-layout",{attrs:{"mt-0":"",row:"",wrap:""}},[s("v-flex",{staticClass:" pt-2",attrs:{sm10:""}},[t._v("\n            "+t._s(t.$t("If you wish you can modify them"))+"\n        ")]),s("v-flex",{staticClass:"text-xs-right",attrs:{sm2:""}},[s("v-btn",{staticClass:"subscription-title-revoke-all elevation-0 text-upper",attrs:{dark:"",small:""},on:{click:function(e){t.$emit("unsubscribe-all")}}},[t._v(t._s(t.$t("revoke all"))+"\n            ")])],1)],1)],1)},staticRenderFns:[]};e.a=a},ipnL:function(t,e,s){"use strict";var a=s("Dd8w"),r=s.n(a),n=s("NYxO"),i=s("21M4");e.a={name:"UserFormTerms",methods:r()({onSave:function(){var t=this;this.$nuxt.$loading.start(),this.saveAllTerms(this.modified).then(function(){t.loadRecordList({id:t.params.id}),t.$nuxt.$loading.finish(),t.dataEdit=!1}).catch(function(){return t.$nuxt.$loading.finish()})},flagDisabled:function(t){return!this.dataEdit||!!t.mandatory&&!this.allowAll},allFlags:function(t){if(!t||!t.paragraphs)return[];for(var e=[],s=0;s<t.paragraphs.length;s++)for(var a=t.paragraphs[s],r=0;r<a.treatments.length;r++){var n=a.treatments[r],i=n.code,o=n.selected,c=n.mandatory;e.push({code:i,selected:o,mandatory:c})}return e},addChangedTerm:function(t){var e=this,s=this.modified.findIndex(function(e){return t.id===e.id});this.$nextTick(function(){var a={id:t.id,privacy:t.privacy,privacyFlags:e.allFlags(t.privacy)};s<0?e.modified.push(a):e.modified[s]=a})}},Object(n.mapActions)("owners/users",["saveAllTerms","loadRecordList"]),{termName:function(t){var e=0;return e=t.privacy&&t.privacy.termId?t.privacy.termId:t.termId,this.termsMap[e]?this.termsMap[e].name:"Informativa generica"}}),props:{allowAll:{default:!1},showSmartBar:{default:!0},multiPrivacy:{default:!0},readOnly:{default:!1}},data:function(){return{dataEdit:!1,modified:[]}},components:{SmallEditSaveButtonBar:i.a},created:function(){this.readOnly?this.dataEdit=!1:this.showSmartBar||(this.dataEdit=!0)},computed:r()({},Object(n.mapGetters)("terms",["termsMap"]),Object(n.mapState)("owners/users",["recordList"]),Object(n.mapState)("route",["params"]))}},k7t3:function(t,e,s){"use strict";var a={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("v-flex",{staticClass:"small-edit-save-button-bar pa-0 ma-0"},[t.editMode?t._e():[t.hideDelete?t._e():s("v-tooltip",{attrs:{top:"","close-delay":"0","open-delay":"0"}},[s("v-btn",{attrs:{slot:"activator",icon:"",flat:"",color:t.color},on:{click:t.onDeleteClick},slot:"activator"},[s("v-icon",[t._v("delete")])],1),s("span",[t._v(t._s(t.$t("Delete")))])],1),s("v-tooltip",{attrs:{top:"","close-delay":"0","open-delay":"0"}},[s("v-btn",{attrs:{slot:"activator",icon:"",flat:"",color:t.color},on:{click:t.onEditClick},slot:"activator"},[s("v-icon",[t._v("edit")])],1),s("span",[t._v(t._s(t.$t("Edit")))])],1)],t.editMode?[t.hideCancel?t._e():s("v-tooltip",{attrs:{top:"","close-delay":"0","open-delay":"0"}},[s("v-btn",{attrs:{slot:"activator",icon:"",flat:"",color:t.color},on:{click:t.onCancelClick},slot:"activator"},[s("v-icon",[t._v("exit_to_app")])],1),s("span",[t._v(t._s(t.$t("Cancel")))])],1),s("v-tooltip",{attrs:{top:"","close-delay":"0","open-delay":"0"}},[s("v-btn",{attrs:{slot:"activator",icon:"",flat:"",color:t.color},on:{click:t.onSaveClick},slot:"activator"},[s("v-icon",[t._v("save")])],1),s("span",[t._v(t._s(t.$t("Save")))])],1)]:t._e()],2)},staticRenderFns:[]};e.a=a},o8fq:function(t,e,s){"use strict";e.a={components:{},props:["rec"]}},o9cn:function(t,e,s){(t.exports=s("FZ+f")(!1)).push([t.i,".small-edit-save-button-bar .btn{margin:0}",""])},ofvO:function(t,e,s){(t.exports=s("FZ+f")(!1)).push([t.i,"",""])},"pdP+":function(t,e,s){"use strict";var a={render:function(){var t=this.$createElement;return(this._self._c||t)("subscription",{attrs:{"read-only":!0}})},staticRenderFns:[]};e.a=a},tsoV:function(t,e,s){"use strict";var a={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("v-layout",{staticClass:"owner-user-form-terms",attrs:{column:""}},[s("v-flex",{staticClass:" lighten-5 pa-3",attrs:{xs12:""}},[s("v-layout",{attrs:{row:"",wrap:""}},[t.multiPrivacy?s("v-flex",{staticClass:"title pt-3",attrs:{xs12:"",sm6:""}},[t._v("\n                    "+t._s(t.$t("Terms"))+"\n                ")]):t._e(),t.showSmartBar?s("v-flex",{staticClass:"text-xs-right",attrs:{xs12:"",sm6:""}},[s("SmallEditSaveButtonBar",{attrs:{"hide-delete":!0,"edit-mode":t.dataEdit},on:{edit:function(e){t.dataEdit=!0},save:t.onSave,cancel:function(e){t.dataEdit=!1}}})],1):t._e()],1),t._l(t.recordList,function(e,a,r){return[s("v-layout",{attrs:{row:"",wrap:""}},[s("v-flex",{attrs:{xs12:""}},[s("table",{staticClass:"owner-user-form-terms-data-table mb-3"},[t._l(e,function(e,a,r){return[0==r?[s("tr",{staticClass:"caption owner-user-form-terms-data-table-privacy-name"},[s("td",{staticClass:"pl-3 pt-2",attrs:{colspan:"3"}},[s("b",[t._v("\n                                            "+t._s(t.termName(e))+"\n                                        ")])])]),s("tr",{staticClass:"caption"},[s("th",{attrs:{width:"1%"}},[t._v(t._s(t.$t("Date")))]),s("th",{attrs:{width:"1%"}},[t._v(t._s(t.$t("Time")))]),s("th",{attrs:{width:"50%"}},[t._v(t._s(t.$t("Treatments")))]),s("th",{attrs:{width:"30%"}},[t._v(t._s(t.$t("Privacy url"))+"/IP")])])]:t._e(),e?[s("tr",{staticClass:"caption"},[e.created?s("td",[t._v(t._s(t._f("dmy")(e.created)))]):t._e(),e.created?s("td",[t._v(" "+t._s(t._f("time")(e.created))+" ")]):t._e(),s("td",[e.privacy?s("span",[t._l(e.privacy.paragraphs,function(a){return[s("div",[s("div",[s("i",[t._v(t._s(a.title))])]),t._l(a.treatments,function(a,r){return[s("div",{staticClass:"ml-4"},[s("input",{directives:[{name:"model",rawName:"v-model",value:a.selected,expression:"t.selected"}],staticClass:"bigger-check",attrs:{type:"checkbox",disabled:t.flagDisabled(a)},domProps:{checked:Array.isArray(a.selected)?t._i(a.selected,null)>-1:a.selected},on:{click:function(s){t.addChangedTerm(e)},change:function(e){var s=a.selected,r=e.target,n=!!r.checked;if(Array.isArray(s)){var i=t._i(s,null);r.checked?i<0&&t.$set(a,"selected",s.concat([null])):i>-1&&t.$set(a,"selected",s.slice(0,i).concat(s.slice(i+1)))}else t.$set(a,"selected",n)}}}),t._v(" "+t._s(a.code)+"\n                                                            "),a.mandatory||a.restrictive?s("span",{staticClass:"ml-2"},[t._v("\n                                                                ("),a.mandatory?s("span",[t._v(t._s(t.$t("Mandatory"))+" ")]):t._e(),a.restrictive?s("span",[t._v(t._s(t.$t("Restrictive"))+" ")]):t._e(),t._v(")\n                                                            ")]):t._e()])]})],2)]})],2):t._e()]),s("td",[t._v("\n                                        "+t._s(e.page)),s("br"),t._v(t._s(e.ip)+"\n                                    ")])]),s("tr",[s("td",{staticClass:"pa-0",staticStyle:{"border-bottom":"3px solid grey"},attrs:{colspan:"4"}},[s("v-expansion-panel",{staticClass:"elevation-0"},[s("v-expansion-panel-content",[s("div",{attrs:{slot:"header"},slot:"header"},[t._v(t._s(t.$t("Other data")))]),s("v-card",[s("v-card-text",{staticClass:"grey lighten-3"},[t._l(e.form,function(e,a){return[s("div",{key:a},[s("b",[t._v(t._s(a))]),t._v(": "+t._s(e)+"\n                                                            ")])]})],2)],1)],1)],1)],1)]),e.properties&&e.properties.history?s("tr",[s("td",{staticClass:"pa-0",staticStyle:{"border-bottom":"3px solid grey"},attrs:{colspan:"4"}},[s("v-expansion-panel",{staticClass:"elevation-0"},[s("v-expansion-panel-content",[s("div",{attrs:{slot:"header"},slot:"header"},[t._v(t._s(t.$t("History")))]),s("v-card",[s("v-card-text",{staticClass:"grey lighten-3",staticStyle:{"font-size":"12px"}},[t._l(e.properties.history,function(e){return[s("div",[t._v("\n                                                                "+t._s(t._f("dmy")(e.update))+" "+t._s(t._f("time")(e.update))+"  "),s("b",[t._v(t._s(e.user))]),t._l(e.variations,function(e){return[t._v("\n                                                                    "+t._s(t.$t(e.action))+" "+t._s(e.flag)+"\n                                                                ")]})],2)]})],2)],1)],1)],1)],1)]):t._e()]:t._e()]})],2)])],1)]})],2)],1)},staticRenderFns:[]};e.a=a},uV2p:function(t,e,s){"use strict";var a=s("Dd8w"),r=s.n(a),n=s("NYxO");e.a={computed:r()({},Object(n.mapState)("privacy",["$record"])),props:{name:{default:""},surname:{default:""},domain:{default:""}}}},uVDJ:function(t,e,s){"use strict";var a=s("uV2p"),r=s("h984");var n=function(t){s("TcwD")},i=s("VU/8")(a.a,r.a,!1,n,null,null);e.a=i.exports},uraA:function(t,e,s){"use strict";var a=s("F8Oc");e.a={components:{Subscription:a.a},layout:"whitepage",auth:!1,fetch:function(t){var e=t.store,s=t.query;if(s._k)return e.dispatch("privacy/loadByEmailOwnerDomain",s._k,{root:!0}).then(function(){}).catch(function(){})}}},y9sX:function(t,e,s){"use strict";var a=s("ipnL"),r=s("tsoV");var n=function(t){s("0QMV")},i=s("VU/8")(a.a,r.a,!1,n,null,null);e.a=i.exports},yR21:function(t,e,s){"use strict";var a={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("v-container",{attrs:{color:"white"}},[t.hasError?s("v-card",{staticClass:"pa-3",attrs:{flat:"",color:"white"}},[s("v-layout",[s("v-flex",{staticClass:"title"},[t._v(t._s(t.$t("Invalid request"))+"!!!")])],1)],1):t._e(),t.hasError?t._e():s("v-card",{staticClass:"pa-3",attrs:{flat:"",color:"white"}},[s("subscription-title",{attrs:{name:t.$record.name,surname:t.$record.surname,domain:t.$record.domain},on:{"unsubscribe-all":t.onUnsubscribeAll}}),s("UserFormTerms",t._b({attrs:{"allow-all":!1,"show-smart-bar":!1}},"UserFormTerms",t.$attrs,!1)),s("v-card-actions",{staticClass:"text-xs-center"},[t.hasNewsLetters?s("v-btn",{staticClass:"text-upper elevation-0",attrs:{color:"info",small:""},on:{click:t.onUnsubNewsletters}},[t._v("\n                "+t._s(t.$t("unsubscribe newsletters"))+"\n            ")]):t._e()],1)],1)],1)},staticRenderFns:[]};e.a=a}});