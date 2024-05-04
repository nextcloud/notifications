"use strict";(self.webpackChunknotifications=self.webpackChunknotifications||[]).push([["src_NotificationsApp_vue"],{2194:(t,e,i)=>{i.d(e,{Z:()=>r});var n=i(7537),s=i.n(n),a=i(3645),o=i.n(a)()(s());o.push([t.id,".notification[data-v-119344b8]{background-color:var(--color-main-background)}.notification[data-v-119344b8] img.notification-icon{display:flex;width:32px;height:32px;filter:var(--background-invert-if-dark)}.notification[data-v-119344b8] .rich-text--wrapper{white-space:pre-wrap;word-break:break-word}.notification .notification-subject[data-v-119344b8]{padding:4px}.notification a.notification-subject[data-v-119344b8]:focus-visible{box-shadow:inset 0 0 0 2px var(--color-main-text) !important}","",{version:3,sources:["webpack://./src/Components/Notification.vue"],names:[],mappings:"AACA,+BACC,6CAAA,CAEA,qDACC,YAAA,CACA,UAAA,CACA,WAAA,CACA,uCAAA,CAED,mDACC,oBAAA,CACA,qBAAA,CAGD,qDACC,WAAA,CAGD,oEACC,4DAAA",sourcesContent:["\n.notification {\n\tbackground-color: var(--color-main-background);\n\n\t:deep(img.notification-icon) {\n\t\tdisplay: flex;\n\t\twidth: 32px;\n\t\theight: 32px;\n\t\tfilter: var(--background-invert-if-dark);\n\t}\n\t:deep(.rich-text--wrapper) {\n\t\twhite-space: pre-wrap;\n\t\tword-break: break-word;\n\t}\n\n\t.notification-subject {\n\t\tpadding: 4px;\n\t}\n\n\ta.notification-subject:focus-visible {\n\t\tbox-shadow: inset 0 0 0 2px var(--color-main-text) !important; // override rule in core/css/headers.scss #header a:focus-visible\n\t}\n}\n\n"],sourceRoot:""}]);const r=o},1343:(t,e,i)=>{i.d(e,{Z:()=>r});var n=i(7537),s=i.n(n),a=i(3645),o=i.n(a)()(s());o.push([t.id,'.external[data-v-042e7c76]:after{content:" ↗"}',"",{version:3,sources:["webpack://./DefaultParameter.vue","webpack://./src/Components/Parameters/DefaultParameter.vue"],names:[],mappings:"AAAA,iCCCA,YACC",sourcesContent:['.external:after{content:" ↗"}',"\n.external:after {\n\tcontent: ' ↗';\n}\n"],sourceRoot:""}]);const r=o},5959:(t,e,i)=>{i.d(e,{Z:()=>r});var n=i(7537),s=i.n(n),a=i(3645),o=i.n(a)()(s());o.push([t.id,".mention[data-v-62148f13]{display:contents;white-space:nowrap}","",{version:3,sources:["webpack://./src/Components/Parameters/User.vue"],names:[],mappings:"AACA,0BACC,gBAAA,CACA,kBAAA",sourcesContent:["\n.mention {\n\tdisplay: contents;\n\twhite-space: nowrap;\n}\n"],sourceRoot:""}]);const r=o},9012:(t,e,i)=>{i.d(e,{Z:()=>r});var n=i(7537),s=i.n(n),a=i(3645),o=i.n(a)()(s());o.push([t.id,".notification-container[data-v-945f8ede]{overflow:hidden}.notification-wrapper[data-v-945f8ede]{max-height:calc(100vh - 200px);overflow:auto}[data-v-945f8ede] .empty-content{margin:12vh 10px}[data-v-945f8ede] .empty-content p{color:var(--color-text-maxcontrast)}.icon-alert-outline[data-v-945f8ede]{background-size:64px;width:64px;height:64px}.fade-enter-active[data-v-945f8ede],.fade-leave-active[data-v-945f8ede]{transition:opacity var(--animation-quick) ease}.fade-enter-from[data-v-945f8ede],.fade-leave-to[data-v-945f8ede]{opacity:0}.list-move[data-v-945f8ede],.list-enter-active[data-v-945f8ede],.list-leave-active[data-v-945f8ede]{transition:all var(--animation-quick) ease}.list-enter-from[data-v-945f8ede],.list-leave-to[data-v-945f8ede]{opacity:0;transform:translateX(30px)}.list-leave-active[data-v-945f8ede]{width:100%}","",{version:3,sources:["webpack://./src/NotificationsApp.vue"],names:[],mappings:"AACA,yCAEC,eAAA,CAGD,uCACC,8BAAA,CACA,aAAA,CAGD,iCACC,gBAAA,CAEA,mCACC,mCAAA,CAIF,qCACC,oBAAA,CACA,UAAA,CACA,WAAA,CAGD,wEAEC,8CAAA,CAGD,kEAEC,SAAA,CAGD,oGAGC,0CAAA,CAGD,kEAEC,SAAA,CACA,0BAAA,CAGD,oCACC,UAAA",sourcesContent:["\n.notification-container {\n\t/* Prevent slide animation to go out of the div */\n\toverflow: hidden;\n}\n\n.notification-wrapper {\n\tmax-height: calc(100vh - 50px * 4);\n\toverflow: auto;\n}\n\n::v-deep .empty-content {\n\tmargin: 12vh 10px;\n\n\tp {\n\t\tcolor: var(--color-text-maxcontrast);\n\t}\n}\n\n.icon-alert-outline {\n\tbackground-size: 64px;\n\twidth: 64px;\n\theight: 64px;\n}\n\n.fade-enter-active,\n.fade-leave-active {\n\ttransition: opacity var(--animation-quick) ease;\n}\n\n.fade-enter-from,\n.fade-leave-to {\n\topacity: 0;\n}\n\n.list-move,\n.list-enter-active,\n.list-leave-active {\n\ttransition: all var(--animation-quick) ease;\n}\n\n.list-enter-from,\n.list-leave-to {\n\topacity: 0;\n\ttransform: translateX(30px);\n}\n\n.list-leave-active {\n\twidth: 100%;\n}\n"],sourceRoot:""}]);const r=o},987:(e,i,n)=>{n.r(i),n.d(i,{default:()=>nt});var s=n(7040),a=n(2652),o=n(8652),r=n(6034),c=n(2228),l=n(4024),u=n(7963),d=n(1766),h=n(9183);const p={name:"Action",components:{NcButton:a.Z},props:{label:{type:String,default:"",required:!0},link:{type:String,default:"",required:!0},type:{type:String,default:"",required:!0},primary:{type:Boolean,default:!1,required:!0},notificationIndex:{type:Number,required:!0}},data:()=>({tabbed:!1}),computed:{isWebLink(){return"WEB"===this.typeWithDefault},typeWithDefault(){return this.type||"GET"},buttonType(){return this.primary?"primary":"secondary"}},methods:{async onClickActionButtonWeb(e){try{const t={cancelAction:!1,notification:this.$parent.$props,action:{url:this.link,type:this.typeWithDefault}};await(0,h.j8)("notifications:action:execute",t),t.cancelAction&&e.preventDefault()}catch(e){console.error("Failed to perform action",e),(0,l.x2)(t("notifications","Failed to perform action"))}},async onClickActionButton(){try{const t={cancelAction:!1,notification:this.$parent.$props,action:{url:this.link,type:this.typeWithDefault}};if(await(0,h.j8)("notifications:action:execute",t),t.cancelAction)return;await(0,s.ZP)({method:this.typeWithDefault,url:this.link}),this.$parent._$el.fadeOut(OC.menuSpeed),this.$parent.$emit("remove",this.notificationIndex),(0,h.j8)("notifications:action:executed",t);try{$("body").trigger(new $.Event("OCA.Notification.Action",{notification:this.$parent,action:{url:this.link,type:this.typeWithDefault}}))}catch(t){console.error(t)}}catch(e){console.error("Failed to perform action",e),(0,l.x2)(t("notifications","Failed to perform action"))}}}};var f=n(1900);const m=(0,f.Z)(p,(function(){var t=this,e=t._self._c;return t.isWebLink?e("NcButton",{staticClass:"action-button pull-right",attrs:{type:"primary",href:t.link},on:{click:t.onClickActionButtonWeb}},[t._v("\n\t"+t._s(t.label)+"\n")]):t.isWebLink?t._e():e("NcButton",{staticClass:"action-button pull-right",attrs:{type:t.buttonType},on:{click:t.onClickActionButton}},[t._v("\n\t"+t._s(t.label)+"\n")])}),[],!1,null,null,null).exports;var g=n(1480),v=n(7883);const b={name:"DefaultParameter",props:{type:{type:String,required:!0},id:{type:[Number,String],required:!0},name:{type:String,required:!0},link:{type:String,default:""}},computed:{hasInternalLink(){return this.link&&("deck-board"===this.type||"deck-card"===this.type)}}};var y=n(3379),A=n.n(y),C=n(7795),_=n.n(C),w=n(569),k=n.n(w),N=n(3565),x=n.n(N),I=n(9216),S=n.n(I),P=n(4589),j=n.n(P),B=n(1343),T={};T.styleTagTransform=j(),T.setAttributes=x(),T.insert=k().bind(null,"head"),T.domAPI=_(),T.insertStyleElement=S();A()(B.Z,T);B.Z&&B.Z.locals&&B.Z.locals;const Z=(0,f.Z)(b,(function(){var t=this,e=t._self._c;return t.hasInternalLink?e("a",{attrs:{href:t.link}},[e("strong",[t._v(t._s(t.name))])]):t.link?e("a",{staticClass:"external",attrs:{href:t.link,target:"_blank",rel:"noopener noreferrer"}},[e("strong",[t._v(t._s(t.name))])]):e("strong",[t._v(t._s(t.name))])}),[],!1,null,"042e7c76",null).exports;const D={name:"File",props:{type:{type:String,required:!0},id:{type:[Number,String],required:!0},name:{type:String,required:!0},path:{type:String,default:""},link:{type:String,default:""}},computed:{title(){const e=this.path.lastIndexOf("/"),i=this.path.indexOf("/"),n=this.path.substring(0===i?1:0,e);return 0===n.length?"":t("notifications","in {path}",{path:n})}}};const O=(0,f.Z)(D,(function(){var t=this;return(0,t._self._c)("a",{staticClass:"filename",attrs:{title:t.title,href:t.link}},[t._v(t._s(t.name))])}),[],!1,null,null,null).exports;const R={name:"User",components:{NcUserBubble:n(4624).Z},props:{type:{type:String,required:!0},id:{type:String,required:!0},name:{type:String,required:!0},server:{type:String,default:""}},computed:{cloudId(){return this.server?this.id+"@"+this.server:""}}};var q=n(5959),F={};F.styleTagTransform=j(),F.setAttributes=x(),F.insert=k().bind(null,"head"),F.domAPI=_(),F.insertStyleElement=S();A()(q.Z,F);q.Z&&q.Z.locals&&q.Z.locals;const M=(0,f.Z)(R,(function(){var t=this,e=t._self._c;return e("div",{staticClass:"mention"},[t.cloudId?e("strong",{attrs:{title:t.cloudId}},[t._v("\n\t\t"+t._s(t.name)+"\n\t")]):e("NcUserBubble",{attrs:{"display-name":t.name,user:t.id}})],1)}),[],!1,null,"62148f13",null).exports,E={name:"Notification",components:{Action:m,NcButton:a.Z,Close:r.Z,Message:c.Z,NcRichText:o.ZP},props:{notificationId:{type:Number,default:-1},datetime:{type:String,default:""},app:{type:String,default:""},icon:{type:String,default:""},link:{type:String,default:""},externalLink:{type:String,default:""},user:{type:String,default:""},message:{type:String,default:""},messageRich:{type:String,default:""},messageRichParameters:{type:[Object,Array],default:()=>({})},subject:{type:String,default:""},subjectRich:{type:String,default:""},subjectRichParameters:{type:[Object,Array],default:()=>({})},objectType:{type:String,default:""},objectId:{type:String,default:""},shouldNotify:{type:Boolean,default:!0},actions:{type:Array,default:()=>[]},index:{type:Number,default:-1}},data:()=>({showFullMessage:!1}),_$el:null,computed:{timestamp(){return"warning"===this.datetime?0:new Date(this.datetime).valueOf()},absoluteDate(){return"warning"===this.datetime?"":(0,v.Z)(this.timestamp).format("LLL")},relativeDate(){if("warning"===this.datetime)return"";const e=(0,v.Z)().diff((0,v.Z)(this.timestamp));return e>=0&&e<45e3?t("core","seconds ago"):(0,v.Z)(this.timestamp).fromNow()},useLink(){if(!this.link)return!1;let t=!1;return Object.keys(this.subjectRichParameters).forEach((e=>{this.subjectRichParameters[e].link&&(t=!0)})),!t},preparedSubjectParameters(){return this.prepareParameters(this.subjectRichParameters)},preparedMessageParameters(){return this.prepareParameters(this.messageRichParameters)},isCollapsedMessage(){return this.message.length>200&&!this.showFullMessage}},mounted(){if(this._$el=$(this.$el),void 0===this.$parent.$parent.$parent.showBrowserNotifications&&console.error("Failed to read showBrowserNotifications property from App component"),this.$parent.$parent.$parent.backgroundFetching){const t={notification:this.$props};(0,h.j8)("notifications:notification:received",t)}if(this.shouldNotify&&this.$parent.$parent.$parent.showBrowserNotifications)if(this._createWebNotification(),"spreed"===this.app&&"call"===this.objectType){if((0,u.j)("notifications","sound_talk")){new d.Howl({src:[(0,g.FW)("notifications","img","talk.ogg")],volume:.5}).play()}}else if((0,u.j)("notifications","sound_notification")){new d.Howl({src:[(0,g.FW)("notifications","img","notification.ogg")],volume:.5}).play()}},methods:{prepareParameters(t){const e={};return Object.keys(t).forEach((i=>{const n=t[i].type;e[i]="user"===n?{component:M,props:t[i]}:"file"===n?{component:O,props:t[i]}:{component:Z,props:t[i]}})),e},onClickMessage(t){(t.target.closest(".rich-text--wrapper")||!this.messageRich&&this.message)&&(this.showFullMessage=!this.showFullMessage)},onDismissNotification(){s.ZP.delete((0,g.Ii)("apps/notifications/api/v2/notifications/{id}",{id:this.notificationId})).then((()=>{this.$emit("remove",this.index)})).catch((()=>{(0,l.x2)(t("notifications","Failed to dismiss notification"))}))},_createWebNotification(){const t=new Notification(this.subject,{title:this.subject,lang:OC.getLocale(),body:this.message,icon:this.icon,tag:this.notificationId});this.link&&(t.onclick=async function(t){const e={cancelAction:!1,notification:this.$props,action:{url:this.link,type:"WEB"}};await(0,h.j8)("notifications:action:execute",e),e.cancelAction||(console.debug("Redirecting because of a click onto a notification",this.link),window.location.href=this.link),window.focus()}.bind(this))}}};var G=n(2194),W={};W.styleTagTransform=j(),W.setAttributes=x(),W.insert=k().bind(null,"head"),W.domAPI=_(),W.insertStyleElement=S();A()(G.Z,W);G.Z&&G.Z.locals&&G.Z.locals;const L=(0,f.Z)(E,(function(){var t=this,e=t._self._c;return e("li",{staticClass:"notification",attrs:{"data-id":t.notificationId,"data-timestamp":t.timestamp,"data-object-type":t.objectType,"data-app":t.app}},[e("div",{staticClass:"notification-heading"},[e("span",{staticClass:"hidden-visually"},[t._v(t._s(t.absoluteDate))]),t._v(" "),t.timestamp?e("span",{staticClass:"notification-time live-relative-timestamp",attrs:{title:t.absoluteDate,"data-timestamp":t.timestamp}},[t._v(t._s(t.relativeDate))]):t._e(),t._v(" "),t.timestamp?e("NcButton",{staticClass:"notification-dismiss-button",attrs:{type:"tertiary","aria-label":t.t("notifications","Dismiss")},on:{click:t.onDismissNotification},scopedSlots:t._u([{key:"icon",fn:function(){return[e("Close",{attrs:{size:20}})]},proxy:!0}],null,!1,2121748766)}):t._e()],1),t._v(" "),t.externalLink?e("a",{staticClass:"notification-subject full-subject-link external",attrs:{href:t.externalLink,target:"_blank",rel:"noreferrer noopener"}},[e("span",{staticClass:"image"},[e("img",{staticClass:"notification-icon",attrs:{src:t.icon,alt:""}})]),t._v(" "),e("span",{staticClass:"subject"},[t._v(t._s(t.subject)+" ↗")])]):t.useLink?e("a",{staticClass:"notification-subject full-subject-link",attrs:{href:t.link}},[t.icon?e("span",{staticClass:"image"},[e("img",{staticClass:"notification-icon",attrs:{src:t.icon,alt:""}})]):t._e(),t._v(" "),t.subjectRich?e("NcRichText",{attrs:{text:t.subjectRich,arguments:t.preparedSubjectParameters}}):e("span",{staticClass:"subject"},[t._v(t._s(t.subject))])],1):e("div",{staticClass:"notification-subject"},[t.icon?e("span",{staticClass:"image"},[e("img",{staticClass:"notification-icon",attrs:{src:t.icon,alt:""}})]):t._e(),t._v(" "),t.subjectRich?e("NcRichText",{attrs:{text:t.subjectRich,arguments:t.preparedSubjectParameters}}):e("span",{staticClass:"subject"},[t._v(t._s(t.subject))])],1),t._v(" "),t.message?e("div",{staticClass:"notification-message",on:{click:t.onClickMessage}},[e("div",{staticClass:"message-container",class:{collapsed:t.isCollapsedMessage}},[t.messageRich?e("NcRichText",{attrs:{text:t.messageRich,arguments:t.preparedMessageParameters,autolink:!0}}):e("span",[t._v(t._s(t.message))])],1),t._v(" "),t.isCollapsedMessage?e("div",{staticClass:"notification-overflow"}):t._e()]):t._e(),t._v(" "),t.actions.length?e("div",{staticClass:"notification-actions"},t._l(t.actions,(function(i,n){return e("Action",t._b({key:n,attrs:{"notification-index":t.index}},"Action",i,!1))})),1):t.externalLink?e("div",{staticClass:"notification-actions"},[e("NcButton",{staticClass:"action-button pull-right",attrs:{type:"primary",href:"https://nextcloud.com/fairusepolicy",target:"_blank",rel:"noreferrer noopener"},scopedSlots:t._u([{key:"icon",fn:function(){return[e("Message",{attrs:{size:20}})]},proxy:!0}])},[t._v("\n\t\t\t"+t._s(t.t("notifications","Contact Nextcloud GmbH"))+" ↗\n\t\t")])],1):t._e()])}),[],!1,null,"119344b8",null).exports;var H=n(3907);const U=(0,n(2556).Kc)("notifications").clearOnLogout().persist().build(),z=t=>(t.notificationId=t.notification_id,t.objectId=t.object_id,t.objectType=t.object_type,delete t.notification_id,delete t.object_id,delete t.object_type,t),V=async t=>{let e={};t&&(e={headers:{"If-None-Match":t}});try{const t=await s.ZP.get((0,g.Ii)("apps/notifications/api/v2/notifications"),e);U.setItem("status",""+t.status),204!==t.status&&(U.setItem("headers",JSON.stringify(t.headers)),U.setItem("data",JSON.stringify(t.data.ocs.data.map(z))))}catch(t){var i;null!=t&&null!==(i=t.response)&&void 0!==i&&i.status?U.setItem("status",""+t.response.status):U.setItem("status","500")}};var J=n(2534),X=n(7421),Y=n(346),K=n(5878),Q=n(7584);const tt={name:"NotificationsApp",components:{NcButton:a.Z,Close:r.Z,Bell:X.Z,Message:c.Z,NcEmptyContent:Y.Z,NcHeaderMenu:Q.Z,Notification:L},data(){var t;return{webNotificationsGranted:!1,backgroundFetching:!1,hasNotifyPush:!1,shutdown:!1,theming:(null===(t=(0,K.F)())||void 0===t?void 0:t.theming)||{},hasThrottledPushNotifications:(0,u.j)("notifications","throttled_push_notifications"),notifications:[],lastETag:null,lastTabId:null,userStatus:null,tabId:null,pollIntervalBase:3e4,pollIntervalCurrent:3e4,interval:null,pushEndpoints:null,open:!1}},_$icon:null,computed:{isRedThemed(){var t;if(null!==(t=this.theming)&&void 0!==t&&t.color){const t=this.rgbToHsl(this.theming.color.substring(1,3),this.theming.color.substring(3,5),this.theming.color.substring(5,7)),e=360*t[0];return(e>=330||e<=15)&&t[1]>.4&&(t[2]>.1||t[2]<.6)}return!1},isOrangeThemed(){var t;if(null!==(t=this.theming)&&void 0!==t&&t.color){const t=this.rgbToHsl(this.theming.color.substring(1,3),this.theming.color.substring(3,5),this.theming.color.substring(5,7)),e=360*t[0];return(e>=305||e<=64)&&t[1]>.7&&(t[2]>.1||t[2]<.6)}return!1},showBrowserNotifications(){return this.backgroundFetching&&this.webNotificationsGranted&&"dnd"!==this.userStatus&&this.tabId===this.lastTabId},emptyContentMessage(){return null===this.webNotificationsGranted?t("notifications","Requesting browser permissions to show notifications"):this.hasThrottledPushNotifications?t("notifications","Push notifications might be unreliable"):t("notifications","No notifications")},emptyContentDescription(){return this.hasThrottledPushNotifications?t("notifications","Nextcloud GmbH sponsors a free push notification gateway for private users. To ensure good service, the gateway limits the number of push notifications per server. For enterprise users, a more scalable gateway is available. Contact Nextcloud GmbH for more information."):""},warningIcon:()=>(0,g.hp)("core","actions/alert-outline.svg")},mounted(){this.tabId=OC.requestToken||""+Math.random(),this._$icon=$(this.$refs.icon),this._oldcount=0,console.debug("Registering notifications container as a menu"),OC.registerMenu($(this.$refs.button),$(this.$refs.container),void 0,!0),this.checkWebNotificationPermissions(),this._fetch();(0,J.oL)("notify_notification",(()=>{this._fetchAfterNotifyPush()}))&&(console.debug("Has notify_push enabled, slowing polling to 15 minutes"),this.pollIntervalBase=9e5,this.hasNotifyPush=!0),this._setPollingInterval(this.pollIntervalBase),this._watchTabVisibility(),(0,h.Ld)("networkOffline",this.handleNetworkOffline),(0,h.Ld)("networkOnline",this.handleNetworkOnline),(0,h.Ld)("user_status:status.updated",this.userStatusUpdated)},beforeDestroy(){(0,h.r1)("user_status:status.updated",this.userStatusUpdated),(0,h.r1)("networkOffline",this.handleNetworkOffline),(0,h.r1)("networkOnline",this.handleNetworkOnline)},methods:{userStatusUpdated(t){(0,H.ts)().uid===t.userId&&(this.userStatus=t.status)},onOpen(){this.requestWebNotificationPermissions()},handleNetworkOffline(){console.debug("Network is offline, slowing down pollingInterval to "+10*this.pollIntervalBase),this._setPollingInterval(10*this.pollIntervalBase)},handleNetworkOnline(){this._fetch(),console.debug("Network is online, reseting pollingInterval to "+this.pollIntervalBase),this._setPollingInterval(this.pollIntervalBase)},setupBackgroundFetcher(){OC.config.session_keepalive?(console.debug("Started background fetcher as session_keepalive is enabled"),this.interval=window.setInterval(this._backgroundFetch.bind(this),this.pollIntervalCurrent)):console.debug("Did not start background fetcher as session_keepalive is off")},onDismissAll(){s.ZP.delete((0,g.Ii)("apps/notifications/api/v2/notifications")).then((()=>{this.notifications=[]})).catch((()=>{(0,l.x2)(t("notifications","Failed to dismiss all notifications"))}))},onRemove(t){this.notifications.splice(t,1)},rgbToHsl(t,e,i){t=parseInt(t,16)/255,e=parseInt(e,16)/255,i=parseInt(i,16)/255;const n=Math.max(t,e,i),s=Math.min(t,e,i);let a,o;const r=(n+s)/2;if(n===s)a=o=0;else{const c=n-s;switch(o=r>.5?c/(2-n-s):c/(n+s),n){case t:a=(e-i)/c+(e<i?6:0);break;case e:a=(i-t)/c+2;break;case i:a=(t-e)/c+4}a/=6}return[a,o,r]},_updateDocTitleOnNewNotifications(t){t.length>this._oldcount&&(this._oldcount=t.length,this.backgroundFetching&&document.hidden&&(document.title.startsWith("* ")||(document.title="* "+document.title)))},_restoreTitle(){document.title.startsWith("* ")&&(document.title=document.title.substring(2))},_fetchAfterNotifyPush(){this.backgroundFetching=!0,this.hasNotifyPush&&this.tabId!==this.lastTabId?(console.debug("Deferring notification refresh from browser storage are notify_push event to give the last tab the chance to do it"),setTimeout((()=>{this._fetch()}),5e3)):(console.debug("Refreshing notifications are notify_push event"),this._fetch())},async _fetch(){const t=await(async(t,e,i,n)=>{const s=parseInt(U.getItem("lastUpdated"),10),a=U.getItem("tabId"),o=(0,v.Z)().format("X");return(i||a===t&&s+25<o||a===t&&n||s+35<o)&&(U.setItem("tabId",t),U.setItem("lastUpdated",o),await V(e)),{status:parseInt(U.getItem("status"),10),headers:JSON.parse(U.getItem("headers")||"[]"),data:JSON.parse(U.getItem("data")||"[]"),tabId:U.getItem("tabId"),lastUpdated:parseInt(U.getItem("lastUpdated"),10)}})(this.tabId,this.lastETag,!this.backgroundFetching,this.hasNotifyPush);204===t.status?(console.debug("Fetching notifications but no content, slowing down polling to "+10*this.pollIntervalBase),this._setPollingInterval(10*this.pollIntervalBase)):200===t.status?(this.userStatus=t.headers["x-nextcloud-user-status"],this.lastETag=t.headers.etag,this.lastTabId=t.tabId,this.notifications=t.data,console.debug("Got notification data, restoring default polling interval."),this._setPollingInterval(this.pollIntervalBase),this._updateDocTitleOnNewNotifications(this.notifications)):304===t.status?this._setPollingInterval(this.pollIntervalBase):503===t.status?(console.info("Slowing down notifications: instance is in maintenance mode."),this._setPollingInterval(10*this.pollIntervalBase)):404===t.status?(console.info("Slowing down notifications: app is disabled."),this._setPollingInterval(10*this.pollIntervalBase)):(console.info("Slowing down notifications: Status "+t.status),this._setPollingInterval(10*this.pollIntervalBase))},_backgroundFetch(){this.backgroundFetching=!0,this._fetch()},_watchTabVisibility(){document.addEventListener("visibilitychange",this._visibilityChange,!1)},_visibilityChange(){document.hidden||this._restoreTitle()},_setPollingInterval(t){this.interval&&t===this.pollIntervalCurrent||(console.debug("Polling interval updated to "+t),this.interval&&(window.clearInterval(this.interval),this.interval=null),this.pollIntervalCurrent=t,this.setupBackgroundFetcher())},_shutDownNotifications(t){console.debug("Shutting down notifications "+(t?"temporary":"bye")),this.interval&&(window.clearInterval(this.interval),this.interval=null),this.shutdown=!t},checkWebNotificationPermissions(){return"Notification"in window?"granted"===window.Notification.permission?(console.debug("Notifications permissions granted"),void(this.webNotificationsGranted=!0)):"denied"===window.Notification.permission?(console.debug("Notifications permissions denied"),void(this.webNotificationsGranted=!1)):"http:"===window.location.protocol?(console.debug("Notifications require HTTPS"),void(this.webNotificationsGranted=!1)):(console.info("Notifications permissions not yet requested"),void(this.webNotificationsGranted=null)):(console.info("Browser does not support notifications"),void(this.webNotificationsGranted=!1))},async requestWebNotificationPermissions(){null===this.webNotificationsGranted&&(console.info("Requesting notifications permissions"),window.Notification.requestPermission().then((t=>{this.webNotificationsGranted="granted"===t})))}}};var et=n(9012),it={};it.styleTagTransform=j(),it.setAttributes=x(),it.insert=k().bind(null,"head"),it.domAPI=_(),it.insertStyleElement=S();A()(et.Z,it);et.Z&&et.Z.locals&&et.Z.locals;const nt=(0,f.Z)(tt,(function(){var t=this,e=t._self._c;return t.shutdown?t._e():e("NcHeaderMenu",{staticClass:"notifications-button",attrs:{id:"notifications","exclude-click-outside-selectors":[".popover"],open:t.open,"aria-label":t.t("notifications","Notifications")},on:{"update:open":function(e){t.open=e},open:t.onOpen},scopedSlots:t._u([{key:"trigger",fn:function(){return[0!==t.notifications.length||null===t.webNotificationsGranted||t.hasThrottledPushNotifications?e("svg",{staticClass:"notifications-button__icon",attrs:{xmlns:"http://www.w3.org/2000/svg","xmlns:xlink":"http://www.w3.org/1999/xlink",version:"1.1",width:"20",height:"20",viewBox:"0 0 24 24",fill:"currentColor"}},[e("path",{attrs:{d:"M 19,11.79 C 18.5,11.92 18,12 17.5,12 14.47,12 12,9.53 12,6.5 12,5.03 12.58,3.7 13.5,2.71 13.15,2.28 12.61,2 12,2 10.9,2 10,2.9 10,4 V 4.29 C 7.03,5.17 5,7.9 5,11 v 6 l -2,2 v 1 H 21 V 19 L 19,17 V 11.79 M 12,23 c 1.11,0 2,-0.89 2,-2 h -4 c 0,1.11 0.9,2 2,2 z"}}),t._v(" "),e("path",{staticClass:"notification__dot",class:t.isRedThemed?"notification__dot--white":"",attrs:{d:"M 21,6.5 C 21,8.43 19.43,10 17.5,10 15.57,10 14,8.43 14,6.5 14,4.57 15.57,3 17.5,3 19.43,3 21,4.57 21,6.5"}}),t._v(" "),t.hasThrottledPushNotifications?e("path",{staticClass:"notification__dot notification__dot--warning",class:t.isOrangeThemed?"notification__dot--white":"",attrs:{d:"M 21,6.5 C 21,8.43 19.43,10 17.5,10 15.57,10 14,8.43 14,6.5 14,4.57 15.57,3 17.5,3 19.43,3 21,4.57 21,6.5"}}):t._e()]):e("Bell",{staticClass:"notifications-button__icon",attrs:{size:20,title:t.t("notifications","Notifications")}})]},proxy:!0}],null,!1,760905135)},[t._v(" "),e("div",{ref:"container",staticClass:"notification-container"},[e("transition",{attrs:{name:"fade",mode:"out-in"}},[t.notifications.length>0?e("div",[e("transition-group",{staticClass:"notification-wrapper",attrs:{name:"list",tag:"ul"}},[t.hasThrottledPushNotifications?e("Notification",{key:-2016,attrs:{datetime:"warning",app:"core",icon:t.warningIcon,"external-link":"https://nextcloud.com/fairusepolicy",message:t.emptyContentDescription,subject:t.emptyContentMessage,index:2016}}):t._e(),t._v(" "),t._l(t.notifications,(function(i,n){return e("Notification",t._b({key:i.notificationId,attrs:{index:n},on:{remove:t.onRemove}},"Notification",i,!1))}))],2),t._v(" "),t.notifications.length>0?e("span",{staticClass:"dismiss-all",on:{click:t.onDismissAll}},[e("NcButton",{attrs:{type:"tertiary"},on:{click:t.onDismissAll},scopedSlots:t._u([{key:"icon",fn:function(){return[e("Close",{attrs:{size:20}})]},proxy:!0}],null,!1,2121748766)},[t._v("\n\t\t\t\t\t\t"+t._s(t.t("notifications","Dismiss all notifications"))+"\n\t\t\t\t\t")])],1):t._e()],1):e("NcEmptyContent",{attrs:{name:t.emptyContentMessage,description:t.emptyContentDescription},scopedSlots:t._u([{key:"icon",fn:function(){return[t.hasThrottledPushNotifications?e("span",{staticClass:"icon icon-alert-outline"}):e("Bell")]},proxy:!0},t.hasThrottledPushNotifications?{key:"action",fn:function(){return[e("NcButton",{attrs:{type:"primary",href:"https://nextcloud.com/fairusepolicy",target:"_blank",rel:"noreferrer noopener"},scopedSlots:t._u([{key:"icon",fn:function(){return[e("Message",{attrs:{size:20}})]},proxy:!0}],null,!1,1386745923)},[t._v("\n\t\t\t\t\t\t"+t._s(t.t("notifications","Contact Nextcloud GmbH"))+" ↗\n\t\t\t\t\t")])]},proxy:!0}:null],null,!0)})],1)],1)])}),[],!1,null,"945f8ede",null).exports}}]);
//# sourceMappingURL=notifications-src_NotificationsApp_vue.js.map?v=eaf960ae98d1378c486c