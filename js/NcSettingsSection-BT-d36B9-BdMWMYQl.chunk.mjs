/*! third party licenses: js/vendor.LICENSE.txt */
import{r as n,t as r,d as s,e as a}from"./_plugin-vue2_normalizer-CnwJBUiC.chunk.mjs";n(r);const l={name:"HelpCircleIcon",emits:["click"],props:{title:{type:String},fillColor:{type:String,default:"currentColor"},size:{type:Number,default:24}}};var o=function(){var t=this,e=t._self._c;return e("span",t._b({staticClass:"material-design-icon help-circle-icon",attrs:{"aria-hidden":t.title?null:!0,"aria-label":t.title,role:"img"},on:{click:function(i){return t.$emit("click",i)}}},"span",t.$attrs,!1),[e("svg",{staticClass:"material-design-icon__svg",attrs:{fill:t.fillColor,width:t.size,height:t.size,viewBox:"0 0 24 24"}},[e("path",{attrs:{d:"M15.07,11.25L14.17,12.17C13.45,12.89 13,13.5 13,15H11V14.5C11,13.39 11.45,12.39 12.17,11.67L13.41,10.41C13.78,10.05 14,9.55 14,9C14,7.89 13.1,7 12,7A2,2 0 0,0 10,9H8A4,4 0 0,1 12,5A4,4 0 0,1 16,9C16,9.88 15.64,10.67 15.07,11.25M13,19H11V17H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z"}},[t.title?e("title",[t._v(t._s(t.title))]):t._e()])])])},c=[],d=s(l,o,c,!1,null,null);const p=d.exports,u={name:"NcSettingsSection",components:{HelpCircle:p},props:{name:{type:String,required:!0},description:{type:String,default:""},docUrl:{type:String,default:""},limitWidth:{type:Boolean,default:!0}},data(){return{docNameTranslated:a("External documentation for {name}",{name:this.name})}},computed:{forceLimitWidth(){var t,e;if(this.limitWidth)return!0;const[i]=(e=(t=window._oc_config)==null?void 0:t.version.split(".",2))!=null?e:[];return i&&Number.parseInt(i)>=30},hasDescription(){return this.description.length>0},hasDocUrl(){return this.docUrl.length>0}}};var m=function(){var t=this,e=t._self._c;return e("div",{staticClass:"settings-section",class:{"settings-section--limit-width":t.forceLimitWidth}},[e("h2",{staticClass:"settings-section__name"},[t._v(" "+t._s(t.name)+" "),t.hasDocUrl?e("a",{staticClass:"settings-section__info",attrs:{href:t.docUrl,title:t.docNameTranslated,"aria-label":t.docNameTranslated,target:"_blank",rel:"noreferrer nofollow"}},[e("HelpCircle",{attrs:{size:20}})],1):t._e()]),t.hasDescription?e("p",{staticClass:"settings-section__desc"},[t._v(" "+t._s(t.description)+" ")]):t._e(),t._t("default")],2)},f=[],_=s(u,m,f,!1,null,"0974f50a");const g=_.exports;export{g as N};