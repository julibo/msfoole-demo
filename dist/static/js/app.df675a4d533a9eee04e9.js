webpackJsonp([1],{"6Wol":function(t,e){},"7Otq":function(t,e,s){t.exports=s.p+"static/img/logo.cdae353.png"},F8en:function(t,e){},LtRD:function(t,e){},Mkyo:function(t,e){},NHnr:function(t,e,s){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var a=s("7+uW"),i="成都北新医院网上预约挂号系统",l="bx-token",o=s("lbHh"),r=s.n(o),n={name:"Header",data:()=>({}),created:function(){r.a.get(l)?(this.$store.state.auth=!0,this.$router.push({path:"/"})):this.$router.push({path:"/login"})},mounted:function(){},watch:{},computed:{},methods:{shouye:function(){this.$router.push({path:"/"})},login:function(){this.$router.push({path:"/login"})},logout:function(){r.a.remove(l),this.$store.state.auth=!1,this.$store.state.token="",this.$router.push({path:"/login"})}}},c={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"header"},[a("el-row",[a("el-col",{attrs:{span:20}},[a("div",{staticClass:"logoer"},[a("img",{attrs:{src:s("7Otq")}})])]),t._v(" "),a("el-col",{attrs:{span:4}},[a("div",{staticClass:"handler"},[a("el-button",{attrs:{round:""},on:{click:t.shouye}},[a("span",{staticStyle:{"font-size":"1em"}},[t._v("首 页")])]),t._v(" "),t.$store.state.auth?a("el-button",{attrs:{round:""},on:{click:t.logout}},[a("span",{staticStyle:{"font-size":"1em"}},[t._v("退 出")])]):a("el-button",{attrs:{round:""},on:{click:t.login}},[a("span",{staticStyle:{"font-size":"1em"}},[t._v("登 录")])])],1)])],1)],1)},staticRenderFns:[]};var d=s("VU/8")(n,c,!1,function(t){s("Mkyo")},"data-v-774fe29b",null).exports,u={name:"Footer",data:()=>({name:i})},h={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"footer"},[e("h2",[this._v(this._s(this.name))])])},staticRenderFns:[]};var v=s("VU/8")(u,h,!1,function(t){s("kns/")},"data-v-49908c42",null).exports,p={name:"App",components:{"el-footer":v,"el-header":d},provide(){return{reload:this.reload}},data:()=>({isRouterAlive:!0}),created:function(){},mounted:function(){},destroyed:function(){},watch:{},computed:{},methods:{reload(){this.isRouterAlive=!1,this.$nextTick(function(){this.isRouterAlive=!0})}}},_={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{attrs:{id:"app"}},[e("el-container",[e("el-header"),this._v(" "),e("el-main",[this.isRouterAlive?e("router-view"):this._e()],1),this._v(" "),e("el-footer")],1)],1)},staticRenderFns:[]};var f=s("VU/8")(p,_,!1,function(t){s("q4HZ")},null,null).exports,m=s("/ocq"),g=s("mtWM"),b=s.n(g),y=s("Av7u"),C=s.n(y),k="",x="";k="http://wap.xdbxyy.com";const w={key:"A89226D089EE57FA",iv:x="57EF7B0E6E582178"};const $=t=>(t=t,D=w.key,j=w.iv,D=C.a.enc.Utf8.parse(D),j=C.a.enc.Utf8.parse(j),C.a.AES.encrypt(t,D,{iv:j,mode:C.a.mode.CBC,padding:C.a.pad.Pkcs7}).toString());var S,D,j;const z=(t,e,s)=>{let a=[];for(let t in s){let e=s[t];"object"!=typeof e||e instanceof Array&&!(e.length>0&&"object"==typeof e[0])||(e=JSON.stringify(e)),a.push(t+e)}return a.sort(),(t=>C.a.MD5(t).toString())(e+t+a.join(""))};var R=s("NYxO");a.default.use(R.a);var I=new R.a.Store({state:{auth:!1,token:"",order:{}}}),E=s("zL8q"),N=s.n(E);b.a.defaults.withCredentials=!0;const U=b.a.create({baseURL:k,timeout:3e4,withCredentials:!0}),T=(t,e,s)=>U(O(t,"post",!1,e,s)).then(t=>q(t)).catch(t=>V(t)),V=t=>{if(t){var e="";return e="string"==typeof t.message?t.message:t.msg,E.Message.error(e),Promise.reject(null)}},q=t=>{if(200===t.status&&0===t.data.code)return t.data;if(200===t.status&&0!==t.data.code){let e={22:"非法数据请求",23:"签名验证失败",24:"用户认证失败"};return e[t.data.code]?(r.a.remove(l),E.Message.error(e[t.data.code]),ot.push({path:"/login"}),Promise.resolve(null)):Promise.reject(t.data)}return Promise.reject(t.data)},O=(t,e,s,a,i=0)=>{let o={url:t,method:e,headers:{level:i}};if(1===i)a={encrypt:$(JSON.stringify(a))};else if(2===i){let e=(new Date).getTime(),s=I.state.token;if(!s)if("/Index/Index/getCode"==t||"/Index/Index/login"==t)s=x;else{if(!r.a.get(l))return void E.Message.error("系统异常");s=r.a.get(l)}let n=z(s,e,a);o.headers={level:i,timestamp:e,signstr:n,token:s}}return s||(o.headers["Content-Type"]="application/x-www-form-urlencoded",o.responseType="text",o.transformRequest=[function(t){return(t=>{if("string"==typeof t)return t;let e="";for(let s in t){let a=t[s];"object"!=typeof a||a instanceof Array&&!(a.length>0&&"object"==typeof a[0])||(a=JSON.stringify(a)),e+=s+"="+encodeURIComponent(a)+"&"}return e.length>0&&(e=e.substring(0,e.length-1)),e})(t)}]),e in{get:!0,delete:!0}?o.params=a:e in{post:!0,put:!0}&&(o.data=a),o};var A={name:"Login",data:()=>({disabled:!1,loging:!1,form:{number:"",code:""}}),created:function(){r.a.get(l)&&this.$router.push({path:"/"})},mounted:function(){},destroyed:function(){},watch:{},computed:{},methods:{onSubmit:function(){if(""==this.form.number||""==this.form.code)this.$message.warning("请输入手机号码和验证码");else{this.loging=!0;const t={number:this.form.number,code:this.form.code};T("/Index/Index/login",t,2).then(t=>{null!=t&&(t.data?(this.$store.state.auth=!0,this.$router.push({path:"/"})):(this.loging=!1,this.$message.error("账户或验证码错误")))},t=>{this.loging=!1})}},onSend:function(){if(""==this.form.number)this.$message.warning("请输入就诊卡或手机号码");else{this.disabled=!0;const t={number:this.form.number};T("/Index/Index/getCode",t,2).then(t=>{this.disabled=!1,null!=t&&(1==t.data?this.$message.success("验证码发送成功"):this.$message.warning("验证码发送失败"))},t=>{this.disabled=!1})}}}},F={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"login"},[s("el-card",{attrs:{shadow:"always"}},[s("div",{staticClass:"login-form"},[s("h3",[t._v("短信快捷登录")]),t._v(" "),s("el-form",{ref:"form",attrs:{model:t.form}},[s("el-form-item",[s("el-input",{attrs:{placeholder:"请输入就诊卡号或手机号码"},model:{value:t.form.number,callback:function(e){t.$set(t.form,"number",e)},expression:"form.number"}})],1),t._v(" "),s("el-form-item",[s("div",{staticClass:"login-code"},[s("el-input",{attrs:{placeholder:"验证码"},model:{value:t.form.code,callback:function(e){t.$set(t.form,"code",e)},expression:"form.code"}})],1),t._v(" "),s("div",{staticClass:"login-send"},[s("el-button",{attrs:{disabled:t.disabled},on:{click:t.onSend}},[t._v("发送验证码")])],1)]),t._v(" "),s("el-form-item",[s("el-button",{staticStyle:{width:"100%"},attrs:{type:"primary",disabled:t.loging},on:{click:t.onSubmit}},[t._v("登录")])],1)],1)],1)])],1)},staticRenderFns:[]};var M=s("VU/8")(A,F,!1,function(t){s("wlpt")},"data-v-595d20f7",null).exports,P={inject:["reload"],name:"Bodyer",data:()=>({loading:!0,disabled:!1,tableData:[]}),created:function(){r.a.get(l)?T("/Index/Sale/index",{},2).then(t=>{this.loading=!1,null!=t&&(this.tableData=t.data)},t=>{this.loading=!1}):this.$router.push({path:"/login"})},mounted:function(){},watch:{},computed:{},methods:{addReg:function(){this.$router.push({path:"/officer"})},showReport:function(){this.$router.push({path:"/report"})},cancelReg:function(t,e){if(e)this.$message({message:"已支付预约挂号请到柜台取消",type:"warning"});else{this.disabled=!0,T("/Index/Sale/cancel",{hybh:t,sjh:e},2).then(t=>{this.disabled=!1,null!=t&&(this.reload(),this.$message.success("预约挂号取消成功"))},t=>{this.disabled=!1})}}}},L={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"bodyer"},[s("div",{staticClass:"body-main"},[[s("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:t.tableData,"header-cell-class-name":"reg-header-cell","cell-class-name":"reg-cell",stripe:""}},[s("el-table-column",{attrs:{label:"预约时间"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v("\n            "+t._s(e.row.ghrq)+"\n            "),1==e.row.ysh_lx?[t._v(" 上午")]:[t._v(" 下午")]]}}])}),t._v(" "),s("el-table-column",{attrs:{prop:"mzh",label:"门诊号"}}),t._v(" "),s("el-table-column",{attrs:{prop:"ysh",label:"顺序号"}}),t._v(" "),s("el-table-column",{attrs:{prop:"ysxm",label:"看诊医生"}}),t._v(" "),s("el-table-column",{attrs:{prop:"xm",label:"就诊人"}}),t._v(" "),s("el-table-column",{attrs:{label:"挂号费"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v("\n            ￥"+t._s(e.row.ghf)+"\n          ")]}}])}),t._v(" "),s("el-table-column",{attrs:{label:"支付状态"},scopedSlots:t._u([{key:"default",fn:function(e){return[""!=e.row.sjh?[t._v("已在线支付")]:[t._v("支付异常")]]}}])})],1)]],2),t._v(" "),s("div",{staticClass:"body-foot"},[s("el-button",{attrs:{type:"primary"},on:{click:t.addReg}},[t._v("预约挂号")]),t._v(" "),s("el-button",{attrs:{type:"success"},on:{click:t.showReport}},[t._v("检查报告")])],1)])},staticRenderFns:[]};var B=s("VU/8")(P,L,!1,function(t){s("6Wol"),s("LtRD")},"data-v-4414782a",null).exports,H={name:"Officer",data:()=>({tableData:[]}),created:function(){r.a.get(l)||this.$router.push({path:"/login"})},mounted:function(){T("/Index/Sale/getOffices",{},2).then(t=>{null!=t&&(this.tableData=t.data)})},watch:{},computed:{},methods:{xzks:function(t,e){this.$router.push({path:"/doctor",query:{ksbm:t,ksmc:e}})}}},J={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"officer"},[s("div",{staticClass:"liucheng"},[s("el-row",{attrs:{gutter:24}},[s("el-steps",{attrs:{active:0,"finish-status":"success","align-center":""}},[s("el-step",{attrs:{title:"选择科室"}}),t._v(" "),s("el-step",{attrs:{title:"选择医生"}}),t._v(" "),s("el-step",{attrs:{title:"确认挂号"}}),t._v(" "),s("el-step",{attrs:{title:"预约完成"}})],1)],1)],1),t._v(" "),s("div",{staticClass:"officer-main"},[s("el-row",{attrs:{gutter:24}},[s("ul",t._l(t.tableData,function(e){return s("li",{staticClass:"keshi-li"},[s("div",{staticClass:"grid-content bg-purple",on:{click:function(s){t.xzks(e.ksbm,e.ksmc)}}},[s("div",{staticClass:"keshi-name"},[t._v(t._s(e.ksmc))]),t._v(" "),s("div",{staticClass:"keshi-dizhi"},[t._v(t._s(e.kswz||"不详"))])])])}),0)])],1)])},staticRenderFns:[]};var Z=s("VU/8")(H,J,!1,function(t){s("QNMy")},"data-v-444607bc",null).exports,W={name:"Doctor",data:()=>({ksmc:"",ksbm:"",tableData:[],showDate:[],doctorPhoto:"..//..//..//static//img//doctor_male.png"}),created:function(){r.a.get(l)?(this.ksmc=this.$route.query.ksmc,this.ksbm=this.$route.query.ksbm,T("/Index/Sale/getSourceDate",{},2).then(t=>{null!=t&&(this.showDate=t.data)})):this.$router.push({path:"/login"})},mounted:function(){T("/Index/Sale/getSource",{ksbm:this.ksbm},2).then(t=>{null!=t&&(this.tableData=t.data)})},watch:{},computed:{},methods:{selectSource:function(t){T("/Index/Sale/getSource",{ksbm:this.ksbm,appoint:t},2).then(t=>{null!=t&&(this.tableData=t.data)})},order:function(t,e,s,a,i,l,o,r){this.$store.state.order={ysbh:t,zzks:e,ghrq:s,ghlb:a,ysh_lx:i,ysxm:l,ghf:o,zzksmc:r},this.$router.push({path:"/order"})}}},Q={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"doctor"},[s("div",{staticClass:"liucheng"},[s("el-row",{attrs:{gutter:24}},[s("el-steps",{attrs:{active:1,"finish-status":"success","align-center":""}},[s("el-step",{attrs:{title:"选择科室"}}),t._v(" "),s("el-step",{attrs:{title:"选择医生"}}),t._v(" "),s("el-step",{attrs:{title:"确认挂号"}}),t._v(" "),s("el-step",{attrs:{title:"预约完成"}})],1)],1)],1),t._v(" "),s("div",{staticClass:"doctor-main"},[s("div",{staticClass:"doctor-left"},[s("div",{staticClass:"doctor-list"},t._l(t.tableData,function(e){return s("div",{staticClass:"doctor-li"},[s("div",{staticClass:"doctor-man"},[s("div",{staticClass:"doctor-photo"},[s("img",{attrs:{src:e.photo||t.doctorPhoto}})])]),t._v(" "),s("div",{staticClass:"doctor-info"},[s("div",{staticClass:"doctor-info-top"},[s("div",{staticClass:"doctor-info-name"},[t._v(t._s(e.ysxm))]),t._v(" "),s("div",{staticClass:"doctor-info-title"},[t._v(t._s(e.ghlbmc))])]),t._v(" "),s("div",{staticClass:"doctor-info-intro"},[t._v(t._s(e.intro||"简介待完善..."))]),t._v(" "),s("div",{staticClass:"doctor-info-date"},t._l(e.plan,function(a){return s("div",{staticClass:"doctor-date-li"},[s("i",{staticClass:"el-icon-date"}),t._v(" "),s("el-button",{attrs:{type:"text"},on:{click:function(s){t.order(e.ysbh,e.zzks,a.date,e.ghlb,a.ysh_lx,e.ysxm,e.ghf,e.zzksmc)}}},[t._v(t._s(a.showDate)+" "+t._s(a.week)+" "+t._s(a.showTime)+" 余号"+t._s(a.surplus))])],1)}),0)])])}),0)]),t._v(" "),s("div",{staticClass:"doctor-right"},[t._l(t.showDate,function(e,a){return[s("div",{staticClass:"doctor-rlist"},[s("el-button",{attrs:{type:"primary",plain:""},on:{click:function(e){t.selectSource(a)}}},[t._v(t._s(e))])],1)]})],2)])])},staticRenderFns:[]};var Y=s("VU/8")(W,Q,!1,function(t){s("hxZ6")},"data-v-2fdab11c",null).exports,G={name:"Order",data:()=>({active:2,userinfo:{},details:{},payDialog:!1,payType:1,payCode:"",tradeNo:"",timer:null}),created:function(){r.a.get(l)?T("/Index/Sale/getUser",{},2).then(t=>{null!=t&&(this.userinfo=t.data)}):this.$router.push({path:"/login"})},mounted:function(){this.details=this.$store.state.order,"{}"==JSON.stringify(this.details)&&this.$router.push({path:"/officer"})},watch:{},computed:{},methods:{reset(){this.$router.go(-1)},handleClose(t){this.$confirm("如果已经支付请等待系统通知...").then(e=>{clearInterval(this.timer),t()}).catch(t=>{})},payOrder(t){this.active=3;const e={ysbh:this.details.ysbh,zzks:this.details.zzks,ghrq:this.details.ghrq,ghlb:this.details.ghlb,ysh_lx:this.details.ysh_lx,ghf:this.details.ghf,zfzl:t};0==t?T("/Index/Sale/checkIn",e,2).then(t=>{if(null!=t){this.active=4;let e="预约挂号登记成功，号源编号为"+t.data+"，以此为凭到柜台支付挂号费后即可就诊。";this.$alert(e,"预约登记",{confirmButtonText:"确定",callback:t=>{this.$router.push({path:"/"})}})}}):(this.payType=t,this.payDialog=!0,this.payCode="",T("/Index/Sale/createOrder",e,2).then(t=>{null!=t&&(this.payCode=t.data.codeUrl,this.tradeNo=t.data.tradeNo,this.timer=setInterval(()=>{this.refresh(this.tradeNo)},2e3))}))},refresh:function(t){T("/Index/Sale/refresh",{tradeNo:t},2).then(e=>{null!=e&&e.data.status>1&&(clearInterval(this.timer),this.$router.push({path:"/Payer",query:{tradeNo:t}}))})}}},K={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"order"},[s("div",{staticClass:"liucheng"},[s("el-row",{attrs:{gutter:24}},[s("el-steps",{attrs:{active:t.active,"finish-status":"success","align-center":""}},[s("el-step",{attrs:{title:"选择科室"}}),t._v(" "),s("el-step",{attrs:{title:"选择医生"}}),t._v(" "),s("el-step",{attrs:{title:"确认挂号"}}),t._v(" "),s("el-step",{attrs:{title:"预约完成"}})],1)],1)],1),t._v(" "),s("div",{staticClass:"order-main"},[s("div",{staticClass:"order-body"},[s("el-card",{attrs:{shadow:"hover"}},[s("div",{staticClass:"order-list"},[s("div",{staticClass:"order-li"},[s("div",{staticClass:"bg-purple"},[t._v("就诊科室：")]),t._v(" "),s("div",{staticClass:"bg-purple-light"},[t._v(t._s(t.details.zzksmc))])]),t._v(" "),s("div",{staticClass:"order-li"},[s("div",{staticClass:"bg-purple"},[t._v("看诊医生：")]),t._v(" "),s("div",{staticClass:"bg-purple-light"},[t._v(t._s(t.details.ysxm))])]),t._v(" "),s("div",{staticClass:"order-li"},[s("div",{staticClass:"bg-purple"},[t._v("看诊时间：")]),t._v(" "),1==t.details.ysh_lx?s("div",{staticClass:"bg-purple-light"},[t._v(t._s(t.details.ghrq)+" 上午")]):s("div",{staticClass:"bg-purple-light"},[t._v(t._s(t.details.ghrq)+" 下午")])]),t._v(" "),s("div",{staticClass:"order-li"},[s("div",{staticClass:"bg-purple"},[t._v("就诊病人：")]),t._v(" "),s("div",{staticClass:"bg-purple-light"},[t._v(t._s(t.userinfo.xm))])]),t._v(" "),s("div",{staticClass:"order-li"},[s("div",{staticClass:"bg-purple"},[t._v("挂号费用：")]),t._v(" "),s("div",{staticClass:"bg-purple-light"},[t._v("￥"+t._s(t.details.ghf))])])])])],1),t._v(" "),s("div",{staticClass:"order-foot"},[s("el-button",{attrs:{type:"success"},on:{click:function(e){t.payOrder(2)}}},[t._v("微信付款")]),t._v(" "),s("el-button",{attrs:{type:"primary"},on:{click:function(e){t.payOrder(1)}}},[t._v("支付宝付款")]),t._v(" "),s("el-button",{on:{click:function(e){t.reset()}}},[t._v("重选医生")])],1)]),t._v(" "),s("el-dialog",{staticStyle:{padding:"0"},attrs:{width:"480px",top:"0",visible:t.payDialog,"before-close":t.handleClose},on:{"update:visible":function(e){t.payDialog=e}}},[s("div",{class:["zhifu",1==t.payType?"zhifubao":"weixin"]},[s("div",{staticClass:"zhifubao-info"},[t._v("扫码后请等待系统通知")]),t._v(" "),s("div",{staticClass:"zhifubao-jine"},[t._v("挂号费：￥"+t._s(t.details.ghf))]),t._v(" "),s("div",{staticClass:"zhifubao-code"},[s("img",{attrs:{src:t.payCode,width:"180",height:"180"}})])])])],1)},staticRenderFns:[]};var X=s("VU/8")(G,K,!1,function(t){s("rJ5U"),s("tEtM")},"data-v-1ba0cbd4",null).exports,tt={name:"Payer",data:()=>({result:{}}),created:function(){if(r.a.get(l)){var t=this.$route.query.tradeNo;T("/Index/Sale/refresh",{tradeNo:t},2).then(t=>{null!=t&&(this.result=t.data,2==this.result.status&&setTimeout(()=>{this.$router.push({path:"/"})},3e3))})}else this.$router.push({path:"/login"})},mounted:function(){},watch:{},computed:{},methods:{}},et={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"payer"},[s("div",{staticClass:"liucheng"},[s("el-row",{attrs:{gutter:24}},[s("el-steps",{attrs:{active:4,"finish-status":"success","align-center":""}},[s("el-step",{attrs:{title:"选择科室"}}),t._v(" "),s("el-step",{attrs:{title:"选择医生"}}),t._v(" "),s("el-step",{attrs:{title:"确认挂号"}}),t._v(" "),s("el-step",{attrs:{title:"预约完成"}})],1)],1)],1),t._v(" "),s("div",{staticClass:"payer-main"},[2==t.result.status?s("h1",[t._v("预约挂号成功，请按时就诊")]):3==t.result.status?s("h1",[t._v("预约挂号失败，挂号费将按原路返回")]):4==t.result.status?s("h1",[t._v("挂号费返回失败，将转由人工处理")]):s("h1",[t._v("预约挂号失败，挂号费已按原路返回")])])])},staticRenderFns:[]};var st=s("VU/8")(tt,et,!1,function(t){s("F8en")},"data-v-fe992f32",null).exports,at={inject:["reload"],name:"Bodyer",data:()=>({loading:!0,disabled:!1,tableData:[],gridData:{jcjg:{}},dialogCheckVisible:!1,dialogTestVisible:!1,testData:{jyjg:[],shjg:[]}}),created:function(){r.a.get(l)?T("/Index/Sale/report",{},2).then(t=>{this.loading=!1,null!=t&&(this.tableData=t.data)},t=>{this.loading=!1}):this.$router.push({path:"/login"})},mounted:function(){},watch:{},computed:{},methods:{showCheck:function(t){this.dialogCheckVisible=!0,this.gridData=t},showTest:function(t){console.log(t),this.dialogTestVisible=!0,this.testData=t}}},it={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"bodyer"},[s("div",{staticClass:"body-main"},[[s("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:t.tableData,"header-cell-class-name":"reg-header-cell","cell-class-name":"reg-cell",stripe:""}},[s("el-table-column",{attrs:{prop:"ghrq",label:"检查日期"}}),t._v(" "),s("el-table-column",{attrs:{prop:"mzh",label:"门诊号"}}),t._v(" "),s("el-table-column",{attrs:{prop:"ysxm",label:"看诊医生"}}),t._v(" "),s("el-table-column",{attrs:{prop:"byxm",label:"就诊人"}}),t._v(" "),s("el-table-column",{attrs:{label:"报告类型"},scopedSlots:t._u([{key:"default",fn:function(e){return["1"==e.row.type?[t._v("检查报告")]:[t._v("检验报告")]]}}])}),t._v(" "),s("el-table-column",{attrs:{prop:"jcxmmc",label:"检查项目"}}),t._v(" "),s("el-table-column",{attrs:{label:"报告详情"},scopedSlots:t._u([{key:"default",fn:function(e){return["1"==e.row.type?[s("el-button",{attrs:{type:"success",round:""},on:{click:function(s){t.showCheck(e.row)}}},[t._v("查看")])]:[s("el-button",{attrs:{type:"success",round:""},on:{click:function(s){t.showTest(e.row)}}},[t._v("查看")])]]}}])})],1)]],2),t._v(" "),s("div",{staticClass:"body-foot"},[s("el-dialog",{attrs:{title:"检查结果",visible:t.dialogCheckVisible},on:{"update:visible":function(e){t.dialogCheckVisible=e}}},[s("div",{staticClass:"dialog_check"},[s("div",{staticClass:"dialog_check_list"},[s("div",{staticClass:"dialog_check_title"},[t._v("检查编号:")]),t._v(" "),s("div",{staticClass:"dialog_check_content"},[t._v(t._s(t.gridData.kdxh||"--"))])]),t._v(" "),s("div",{staticClass:"dialog_check_list"},[s("div",{staticClass:"dialog_check_title"},[t._v("检查名称:")]),t._v(" "),s("div",{staticClass:"dialog_check_content"},[t._v(t._s(t.gridData.jcxmmc||"--"))])]),t._v(" "),s("div",{staticClass:"dialog_check_list"},[s("div",{staticClass:"dialog_check_title"},[t._v("检查部位:")]),t._v(" "),s("div",{staticClass:"dialog_check_content"},[t._v(t._s(t.gridData.jcjg.jcbw||"--"))])]),t._v(" "),s("div",{staticClass:"dialog_check_list"},[s("div",{staticClass:"dialog_check_title"},[t._v("检查方式:")]),t._v(" "),s("div",{staticClass:"dialog_check_content"},[t._v(t._s(t.gridData.jcjg.jcfs||"--"))])]),t._v(" "),s("div",{staticClass:"dialog_check_list"},[s("div",{staticClass:"dialog_check_title"},[t._v("检查结果:")]),t._v(" "),s("div",{staticClass:"dialog_check_content"},[t._v(t._s(t.gridData.jcjg.jg||"--"))])]),t._v(" "),s("div",{staticClass:"dialog_check_list"},[s("div",{staticClass:"dialog_check_title"},[t._v("诊断意见:")]),t._v(" "),s("div",{staticClass:"dialog_check_content"},[t._v(t._s(t.gridData.jcjg.zd||"--"))])]),t._v(" "),s("div",{staticClass:"dialog_check_list"},[s("div",{staticClass:"dialog_check_title"},[t._v("影像所见:")]),t._v(" "),s("div",{staticClass:"dialog_check_content"},[t._v(t._s(t.gridData.jcjg.zdnr||"--"))])])])]),t._v(" "),s("el-dialog",{attrs:{title:"检验报告",visible:t.dialogTestVisible},on:{"update:visible":function(e){t.dialogTestVisible=e}}},[t.testData.jyjg.length>0?s("div",[s("el-table",{staticStyle:{width:"100%"},attrs:{data:t.testData.jyjg}},[s("el-table-column",{attrs:{prop:"SAMPLE_GROUP_NAME",label:"检验项目"}}),t._v(" "),s("el-table-column",{attrs:{prop:"ITEMNAME",label:"指标名称"}}),t._v(" "),s("el-table-column",{attrs:{prop:"RESULT",label:"检验结果"}}),t._v(" "),s("el-table-column",{attrs:{prop:"UNIT",label:"单位"}}),t._v(" "),s("el-table-column",{attrs:{prop:"REFRANGE",label:"参考范围"}})],1)],1):t._e(),t._v(" "),t.testData.shjg.length>0?s("div",[s("el-table",{staticStyle:{width:"100%"},attrs:{data:t.testData.shjg}},[s("el-table-column",{attrs:{prop:"jcxm",label:"检验名称"}}),t._v(" "),s("el-table-column",{attrs:{prop:"xjmc",label:"细菌名称"}}),t._v(" "),s("el-table-column",{attrs:{prop:"antiname",label:"抗生素名称"}}),t._v(" "),s("el-table-column",{attrs:{prop:"suscept",label:"药敏结果"}}),t._v(" "),s("el-table-column",{attrs:{prop:"susQuan",label:"药敏数量"}}),t._v(" "),s("el-table-column",{attrs:{prop:"refRange",label:"查考范围"}})],1)],1):t._e()])],1)])},staticRenderFns:[]};var lt=s("VU/8")(at,it,!1,function(t){s("VPdY"),s("c2bZ")},"data-v-5cfda1d5",null).exports;a.default.use(m.a);var ot=new m.a({routes:[{path:"*",redirect:"/"},{path:"/header",name:"Header",component:d},{path:"/footer",name:"Footer",component:v},{path:"/login",name:"Login",component:M},{path:"/",name:"Bodyer",component:B},{path:"/officer",name:"Officer",component:Z},{path:"/doctor",name:"Doctor",component:Y},{path:"/order",name:"Order",component:X},{path:"/payer",name:"Payer",component:st},{path:"/report",name:"Report",component:lt}],mode:"history",scrollBehavior:(t,e,s)=>({x:0,y:0})});s("tvR6");a.default.use(N.a),a.default.config.productionTip=!1,ot.beforeEach((t,e,s)=>{t.meta.title&&(document.title=t.meta.title),s()}),new a.default({el:"#app",router:ot,store:I,components:{App:f},template:"<App/>"})},QNMy:function(t,e){},VPdY:function(t,e){},c2bZ:function(t,e){},hxZ6:function(t,e){},"kns/":function(t,e){},q4HZ:function(t,e){},rJ5U:function(t,e){},tEtM:function(t,e){},tvR6:function(t,e){},wlpt:function(t,e){}},["NHnr"]);
//# sourceMappingURL=app.df675a4d533a9eee04e9.js.map