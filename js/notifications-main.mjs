const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=[window.OC.filePath('notifications', '', 'js/NotificationsApp-C9WUfcDR.chunk.mjs'),window.OC.filePath('notifications', '', 'js/_plugin-vue2_normalizer-DAw9bTCy.chunk.mjs'),window.OC.filePath('notifications', '', 'js/style-CmLvDuwV.chunk.mjs'),window.OC.filePath('notifications', '', 'css/style-YXBskS14.chunk.css'),window.OC.filePath('notifications', '', 'css/_plugin-vue2_normalizer-hZkPxjsJ.chunk.css'),window.OC.filePath('notifications', '', 'js/BrowserStorage-CiIszpku.chunk.mjs'),window.OC.filePath('notifications', '', 'css/BrowserStorage-CGfDtmoH.chunk.css'),window.OC.filePath('notifications', '', 'css/NotificationsApp-DQbTud9Y.chunk.css')])))=>i.map(i=>d[i]);
/*! third party licenses: js/vendor.LICENSE.txt */
import{V as p}from"./style-CmLvDuwV.chunk.mjs";const v="modulepreload",w=function(i,c){return new URL(i,c).href},y={},A=function(i,c,f){let u=Promise.resolve();if(c&&c.length>0){const s=document.getElementsByTagName("link"),e=document.querySelector("meta[property=csp-nonce]"),h=e?.nonce||e?.getAttribute("nonce");u=Promise.allSettled(c.map(r=>{if(r=w(r,f),r in y)return;y[r]=!0;const l=r.endsWith(".css"),E=l?'[rel="stylesheet"]':"";if(f)for(let a=s.length-1;a>=0;a--){const d=s[a];if(d.href===r&&(!l||d.rel==="stylesheet"))return}else if(document.querySelector(`link[href="${r}"]${E}`))return;const o=document.createElement("link");if(o.rel=l?"stylesheet":v,l||(o.as="script"),o.crossOrigin="",o.href=r,h&&o.setAttribute("nonce",h),document.head.appendChild(o),l)return new Promise((a,d)=>{o.addEventListener("load",a),o.addEventListener("error",()=>d(new Error(`Unable to preload CSS for ${r}`)))})}))}function m(s){const e=new Event("vite:preloadError",{cancelable:!0});if(e.payload=s,window.dispatchEvent(e),!e.defaultPrevented)throw s}return u.then(s=>{for(const e of s||[])e.status==="rejected"&&m(e.reason);return i().catch(m)})};p.prototype.t=t,p.prototype.n=n,p.prototype.OC=OC,p.prototype.OCA=OCA,new p({el:"#notifications",name:"NotificationsApp",components:{NotificationsApp:()=>A(()=>import("./NotificationsApp-C9WUfcDR.chunk.mjs").then(i=>i.N),__vite__mapDeps([0,1,2,3,4,5,6,7]),import.meta.url)},render:i=>i("NotificationsApp")});export{A as _};
//# sourceMappingURL=notifications-main.mjs.map
