/*! third party licenses: js/vendor.LICENSE.txt */
import{r as Ui,a as le,V as gi}from"./style-N1aAjoYj.chunk.mjs";import{n as we,N as pe,l as he,c as me,v as fe,s as Pi,a as ki}from"./_plugin-vue2_normalizer-CfUu9DuP.chunk.mjs";import{N as ve,B as J}from"./BrowserStorage-DrvUR9Yt.chunk.mjs";import{N as ge}from"./NcSettingsSection-DHX2Y1Ed-DJtDp3gD.chunk.mjs";var ke="2.0.2",qi=500,Fi="user-agent",j="",Bi="?",pi="function",H="undefined",$="object",Oi="string",k="browser",I="cpu",C="device",S="engine",x="os",B="result",o="name",e="type",r="vendor",s="version",y="architecture",si="major",a="model",ri="console",l="mobile",f="tablet",g="smarttv",N="wearable",yi="xr",ni="embedded",X="inapp",zi="brands",U="formFactors",Hi="fullVersionList",V="platform",Mi="platformVersion",mi="bitness",M="sec-ch-ua",ye=M+"-full-version-list",_e=M+"-arch",xe=M+"-"+mi,Se=M+"-form-factors",Ne=M+"-"+l,Ce=M+"-"+a,se=M+"-"+V,Ee=se+"-version",ce=[zi,Hi,l,a,V,Mi,y,U,mi],ui="Amazon",P="Apple",Vi="ASUS",ji="BlackBerry",R="Google",$i="Huawei",Yi="Lenovo",Wi="Honor",bi="LG",_i="Microsoft",xi="Motorola",Si="Nvidia",Gi="OnePlus",Ni="OPPO",Q="Samsung",Ki="Sharp",Z="Sony",Ci="Xiaomi",Ei="Zebra",Ji="Chrome",Xi="Chromium",L="Chromecast",Te="Edge",ii="Firefox",ei="Opera",Qi="Facebook",Zi="Sogou",F="Mobile ",ti=" Browser",Li="Windows",Ie=typeof window!==H,_=Ie&&window.navigator?window.navigator:void 0,D=_&&_.userAgentData?_.userAgentData:void 0,Ae=function(i,c){var d={},p=c;if(!hi(c)){p={};for(var u in c)for(var h in c[u])p[h]=c[u][h].concat(p[h]?p[h]:[])}for(var b in i)d[b]=p[b]&&p[b].length%2===0?p[b].concat(i[b]):i[b];return d},li=function(i){for(var c={},d=0;d<i.length;d++)c[i[d].toUpperCase()]=i[d];return c},Di=function(i,c){if(typeof i===$&&i.length>0){for(var d in i)if(A(i[d])==A(c))return!0;return!1}return W(i)?A(c).indexOf(A(i))!==-1:!1},hi=function(i,c){for(var d in i)return/^(browser|cpu|device|engine|os)$/.test(d)||(c?hi(i[d]):!1)},W=function(i){return typeof i===Oi},Ti=function(i){if(i){for(var c=[],d=Y(/\\?\"/g,i).split(","),p=0;p<d.length;p++)if(d[p].indexOf(";")>-1){var u=ci(d[p]).split(";v=");c[p]={brand:u[0],version:u[1]}}else c[p]=ci(d[p]);return c}},A=function(i){return W(i)?i.toLowerCase():i},Ii=function(i){return W(i)?Y(/[^\d\.]/g,i).split(".")[0]:void 0},q=function(i){for(var c in i){var d=i[c];typeof d==$&&d.length==2?this[d[0]]=d[1]:this[d]=void 0}return this},Y=function(i,c){return W(c)?c.replace(i,j):c},ai=function(i){return Y(/\\?\"/g,i)},ci=function(i,c){if(W(i))return i=Y(/^\s\s*/,i),typeof c===H?i:i.substring(0,qi)},Ai=function(i,c){if(!(!i||!c))for(var d=0,p,u,h,b,m,w;d<c.length&&!m;){var v=c[d],E=c[d+1];for(p=u=0;p<v.length&&!m&&v[p];)if(m=v[p++].exec(i),m)for(h=0;h<E.length;h++)w=m[++u],b=E[h],typeof b===$&&b.length>0?b.length===2?typeof b[1]==pi?this[b[0]]=b[1].call(this,w):this[b[0]]=b[1]:b.length===3?typeof b[1]===pi&&!(b[1].exec&&b[1].test)?this[b[0]]=w?b[1].call(this,w,b[2]):void 0:this[b[0]]=w?w.replace(b[1],b[2]):void 0:b.length===4&&(this[b[0]]=w?b[3].call(this,w.replace(b[1],b[2])):void 0):this[b]=w||void 0;d+=2}},z=function(i,c){for(var d in c)if(typeof c[d]===$&&c[d].length>0){for(var p=0;p<c[d].length;p++)if(Di(c[d][p],i))return d===Bi?void 0:d}else if(Di(c[d],i))return d===Bi?void 0:d;return c.hasOwnProperty("*")?c["*"]:i},ie={ME:"4.90","NT 3.11":"NT3.51","NT 4.0":"NT4.0",2e3:"NT 5.0",XP:["NT 5.1","NT 5.2"],Vista:"NT 6.0",7:"NT 6.1",8:"NT 6.2","8.1":"NT 6.3",10:["NT 6.4","NT 10.0"],RT:"ARM"},ee={embedded:"Automotive",mobile:"Mobile",tablet:["Tablet","EInk"],smarttv:"TV",wearable:"Watch",xr:["VR","XR"],"?":["Desktop","Unknown"],"*":void 0},te={browser:[[/\b(?:crmo|crios)\/([\w\.]+)/i],[s,[o,F+"Chrome"]],[/edg(?:e|ios|a)?\/([\w\.]+)/i],[s,[o,"Edge"]],[/(opera mini)\/([-\w\.]+)/i,/(opera [mobiletab]{3,6})\b.+version\/([-\w\.]+)/i,/(opera)(?:.+version\/|[\/ ]+)([\w\.]+)/i],[o,s],[/opios[\/ ]+([\w\.]+)/i],[s,[o,ei+" Mini"]],[/\bop(?:rg)?x\/([\w\.]+)/i],[s,[o,ei+" GX"]],[/\bopr\/([\w\.]+)/i],[s,[o,ei]],[/\bb[ai]*d(?:uhd|[ub]*[aekoprswx]{5,6})[\/ ]?([\w\.]+)/i],[s,[o,"Baidu"]],[/\b(?:mxbrowser|mxios|myie2)\/?([-\w\.]*)\b/i],[s,[o,"Maxthon"]],[/(kindle)\/([\w\.]+)/i,/(lunascape|maxthon|netfront|jasmine|blazer|sleipnir)[\/ ]?([\w\.]*)/i,/(avant|iemobile|slim(?:browser|boat|jet))[\/ ]?([\d\.]*)/i,/(?:ms|\()(ie) ([\w\.]+)/i,/(flock|rockmelt|midori|epiphany|silk|skyfire|ovibrowser|bolt|iron|vivaldi|iridium|phantomjs|bowser|qupzilla|falkon|rekonq|puffin|brave|whale(?!.+naver)|qqbrowserlite|duckduckgo|klar|helio|(?=comodo_)?dragon)\/([-\w\.]+)/i,/(heytap|ovi|115)browser\/([\d\.]+)/i,/(weibo)__([\d\.]+)/i],[o,s],[/quark(?:pc)?\/([-\w\.]+)/i],[s,[o,"Quark"]],[/\bddg\/([\w\.]+)/i],[s,[o,"DuckDuckGo"]],[/(?:\buc? ?browser|(?:juc.+)ucweb)[\/ ]?([\w\.]+)/i],[s,[o,"UCBrowser"]],[/microm.+\bqbcore\/([\w\.]+)/i,/\bqbcore\/([\w\.]+).+microm/i,/micromessenger\/([\w\.]+)/i],[s,[o,"WeChat"]],[/konqueror\/([\w\.]+)/i],[s,[o,"Konqueror"]],[/trident.+rv[: ]([\w\.]{1,9})\b.+like gecko/i],[s,[o,"IE"]],[/ya(?:search)?browser\/([\w\.]+)/i],[s,[o,"Yandex"]],[/slbrowser\/([\w\.]+)/i],[s,[o,"Smart "+Yi+ti]],[/(avast|avg)\/([\w\.]+)/i],[[o,/(.+)/,"$1 Secure"+ti],s],[/\bfocus\/([\w\.]+)/i],[s,[o,ii+" Focus"]],[/\bopt\/([\w\.]+)/i],[s,[o,ei+" Touch"]],[/coc_coc\w+\/([\w\.]+)/i],[s,[o,"Coc Coc"]],[/dolfin\/([\w\.]+)/i],[s,[o,"Dolphin"]],[/coast\/([\w\.]+)/i],[s,[o,ei+" Coast"]],[/miuibrowser\/([\w\.]+)/i],[s,[o,"MIUI"+ti]],[/fxios\/([\w\.-]+)/i],[s,[o,F+ii]],[/\bqihoobrowser\/?([\w\.]*)/i],[s,[o,"360"]],[/\b(qq)\/([\w\.]+)/i],[[o,/(.+)/,"$1Browser"],s],[/(oculus|sailfish|huawei|vivo|pico)browser\/([\w\.]+)/i],[[o,/(.+)/,"$1"+ti],s],[/samsungbrowser\/([\w\.]+)/i],[s,[o,Q+" Internet"]],[/metasr[\/ ]?([\d\.]+)/i],[s,[o,Zi+" Explorer"]],[/(sogou)mo\w+\/([\d\.]+)/i],[[o,Zi+" Mobile"],s],[/(electron)\/([\w\.]+) safari/i,/(tesla)(?: qtcarbrowser|\/(20\d\d\.[-\w\.]+))/i,/m?(qqbrowser|2345(?=browser|chrome|explorer))\w*[\/ ]?v?([\w\.]+)/i],[o,s],[/(lbbrowser|rekonq)/i],[o],[/ome\/([\w\.]+) \w* ?(iron) saf/i,/ome\/([\w\.]+).+qihu (360)[es]e/i],[s,o],[/((?:fban\/fbios|fb_iab\/fb4a)(?!.+fbav)|;fbav\/([\w\.]+);)/i],[[o,Qi],s,[e,X]],[/(Klarna)\/([\w\.]+)/i,/(kakao(?:talk|story))[\/ ]([\w\.]+)/i,/(naver)\(.*?(\d+\.[\w\.]+).*\)/i,/(daum)apps[\/ ]([\w\.]+)/i,/safari (line)\/([\w\.]+)/i,/\b(line)\/([\w\.]+)\/iab/i,/(alipay)client\/([\w\.]+)/i,/(twitter)(?:and| f.+e\/([\w\.]+))/i,/(instagram|snapchat)[\/ ]([-\w\.]+)/i],[o,s,[e,X]],[/\bgsa\/([\w\.]+) .*safari\//i],[s,[o,"GSA"],[e,X]],[/musical_ly(?:.+app_?version\/|_)([\w\.]+)/i],[s,[o,"TikTok"],[e,X]],[/\[(linkedin)app\]/i],[o,[e,X]],[/(chromium)[\/ ]([-\w\.]+)/i],[o,s],[/headlesschrome(?:\/([\w\.]+)| )/i],[s,[o,Ji+" Headless"]],[/ wv\).+(chrome)\/([\w\.]+)/i],[[o,Ji+" WebView"],s],[/droid.+ version\/([\w\.]+)\b.+(?:mobile safari|safari)/i],[s,[o,"Android"+ti]],[/chrome\/([\w\.]+) mobile/i],[s,[o,F+"Chrome"]],[/(chrome|omniweb|arora|[tizenoka]{5} ?browser)\/v?([\w\.]+)/i],[o,s],[/version\/([\w\.\,]+) .*mobile(?:\/\w+ | ?)safari/i],[s,[o,F+"Safari"]],[/iphone .*mobile(?:\/\w+ | ?)safari/i],[[o,F+"Safari"]],[/version\/([\w\.\,]+) .*(safari)/i],[s,o],[/webkit.+?(mobile ?safari|safari)(\/[\w\.]+)/i],[o,[s,"1"]],[/(webkit|khtml)\/([\w\.]+)/i],[o,s],[/(?:mobile|tablet);.*(firefox)\/([\w\.-]+)/i],[[o,F+ii],s],[/(navigator|netscape\d?)\/([-\w\.]+)/i],[[o,"Netscape"],s],[/(wolvic|librewolf)\/([\w\.]+)/i],[o,s],[/mobile vr; rv:([\w\.]+)\).+firefox/i],[s,[o,ii+" Reality"]],[/ekiohf.+(flow)\/([\w\.]+)/i,/(swiftfox)/i,/(icedragon|iceweasel|camino|chimera|fennec|maemo browser|minimo|conkeror)[\/ ]?([\w\.\+]+)/i,/(seamonkey|k-meleon|icecat|iceape|firebird|phoenix|palemoon|basilisk|waterfox)\/([-\w\.]+)$/i,/(firefox)\/([\w\.]+)/i,/(mozilla)\/([\w\.]+) .+rv\:.+gecko\/\d+/i,/(amaya|dillo|doris|icab|ladybird|lynx|mosaic|netsurf|obigo|polaris|w3m|(?:go|ice|up)[\. ]?browser)[-\/ ]?v?([\w\.]+)/i,/\b(links) \(([\w\.]+)/i],[o,[s,/_/g,"."]],[/(cobalt)\/([\w\.]+)/i],[o,[s,/[^\d\.]+./,j]]],cpu:[[/\b((amd|x|x86[-_]?|wow|win)64)\b/i],[[y,"amd64"]],[/(ia32(?=;))/i,/\b((i[346]|x)86)(pc)?\b/i],[[y,"ia32"]],[/\b(aarch64|arm(v?[89]e?l?|_?64))\b/i],[[y,"arm64"]],[/\b(arm(v[67])?ht?n?[fl]p?)\b/i],[[y,"armhf"]],[/( (ce|mobile); ppc;|\/[\w\.]+arm\b)/i],[[y,"arm"]],[/((ppc|powerpc)(64)?)( mac|;|\))/i],[[y,/ower/,j,A]],[/ sun4\w[;\)]/i],[[y,"sparc"]],[/\b(avr32|ia64(?=;)|68k(?=\))|\barm(?=v([1-7]|[5-7]1)l?|;|eabi)|(irix|mips|sparc)(64)?\b|pa-risc)/i],[[y,A]]],device:[[/\b(sch-i[89]0\d|shw-m380s|sm-[ptx]\w{2,4}|gt-[pn]\d{2,4}|sgh-t8[56]9|nexus 10)/i],[a,[r,Q],[e,f]],[/\b((?:s[cgp]h|gt|sm)-(?![lr])\w+|sc[g-]?[\d]+a?|galaxy nexus)/i,/samsung[- ]((?!sm-[lr])[-\w]+)/i,/sec-(sgh\w+)/i],[a,[r,Q],[e,l]],[/(?:\/|\()(ip(?:hone|od)[\w, ]*)(?:\/|;)/i],[a,[r,P],[e,l]],[/\((ipad);[-\w\),; ]+apple/i,/applecoremedia\/[\w\.]+ \((ipad)/i,/\b(ipad)\d\d?,\d\d?[;\]].+ios/i],[a,[r,P],[e,f]],[/(macintosh);/i],[a,[r,P]],[/\b(sh-?[altvz]?\d\d[a-ekm]?)/i],[a,[r,Ki],[e,l]],[/\b((?:brt|eln|hey2?|gdi|jdn)-a?[lnw]09|(?:ag[rm]3?|jdn2|kob2)-a?[lw]0[09]hn)(?: bui|\)|;)/i],[a,[r,Wi],[e,f]],[/honor([-\w ]+)[;\)]/i],[a,[r,Wi],[e,l]],[/\b((?:ag[rs][2356]?k?|bah[234]?|bg[2o]|bt[kv]|cmr|cpn|db[ry]2?|jdn2|got|kob2?k?|mon|pce|scm|sht?|[tw]gr|vrd)-[ad]?[lw][0125][09]b?|605hw|bg2-u03|(?:gem|fdr|m2|ple|t1)-[7a]0[1-4][lu]|t1-a2[13][lw]|mediapad[\w\. ]*(?= bui|\)))\b(?!.+d\/s)/i],[a,[r,$i],[e,f]],[/(?:huawei)([-\w ]+)[;\)]/i,/\b(nexus 6p|\w{2,4}e?-[atu]?[ln][\dx][012359c][adn]?)\b(?!.+d\/s)/i],[a,[r,$i],[e,l]],[/oid[^\)]+; (2[\dbc]{4}(182|283|rp\w{2})[cgl]|m2105k81a?c)(?: bui|\))/i,/\b((?:red)?mi[-_ ]?pad[\w- ]*)(?: bui|\))/i],[[a,/_/g," "],[r,Ci],[e,f]],[/\b(poco[\w ]+|m2\d{3}j\d\d[a-z]{2})(?: bui|\))/i,/\b; (\w+) build\/hm\1/i,/\b(hm[-_ ]?note?[_ ]?(?:\d\w)?) bui/i,/\b(redmi[\-_ ]?(?:note|k)?[\w_ ]+)(?: bui|\))/i,/oid[^\)]+; (m?[12][0-389][01]\w{3,6}[c-y])( bui|; wv|\))/i,/\b(mi[-_ ]?(?:a\d|one|one[_ ]plus|note lte|max|cc)?[_ ]?(?:\d?\w?)[_ ]?(?:plus|se|lite|pro)?)(?: bui|\))/i,/ ([\w ]+) miui\/v?\d/i],[[a,/_/g," "],[r,Ci],[e,l]],[/; (\w+) bui.+ oppo/i,/\b(cph[12]\d{3}|p(?:af|c[al]|d\w|e[ar])[mt]\d0|x9007|a101op)\b/i],[a,[r,Ni],[e,l]],[/\b(opd2(\d{3}a?))(?: bui|\))/i],[a,[r,z,{OnePlus:["304","403","203"],"*":Ni}],[e,f]],[/vivo (\w+)(?: bui|\))/i,/\b(v[12]\d{3}\w?[at])(?: bui|;)/i],[a,[r,"Vivo"],[e,l]],[/\b(rmx[1-3]\d{3})(?: bui|;|\))/i],[a,[r,"Realme"],[e,l]],[/\b(milestone|droid(?:[2-4x]| (?:bionic|x2|pro|razr))?:?( 4g)?)\b[\w ]+build\//i,/\bmot(?:orola)?[- ](\w*)/i,/((?:moto(?! 360)[\w\(\) ]+|xt\d{3,4}|nexus 6)(?= bui|\)))/i],[a,[r,xi],[e,l]],[/\b(mz60\d|xoom[2 ]{0,2}) build\//i],[a,[r,xi],[e,f]],[/((?=lg)?[vl]k\-?\d{3}) bui| 3\.[-\w; ]{10}lg?-([06cv9]{3,4})/i],[a,[r,bi],[e,f]],[/(lm(?:-?f100[nv]?|-[\w\.]+)(?= bui|\))|nexus [45])/i,/\blg[-e;\/ ]+((?!browser|netcast|android tv|watch)\w+)/i,/\blg-?([\d\w]+) bui/i],[a,[r,bi],[e,l]],[/(ideatab[-\w ]+|602lv|d-42a|a101lv|a2109a|a3500-hv|s[56]000|pb-6505[my]|tb-?x?\d{3,4}(?:f[cu]|xu|[av])|yt\d?-[jx]?\d+[lfmx])( bui|;|\)|\/)/i,/lenovo ?(b[68]0[08]0-?[hf]?|tab(?:[\w- ]+?)|tb[\w-]{6,7})( bui|;|\)|\/)/i],[a,[r,Yi],[e,f]],[/(nokia) (t[12][01])/i],[r,a,[e,f]],[/(?:maemo|nokia).*(n900|lumia \d+|rm-\d+)/i,/nokia[-_ ]?(([-\w\. ]*))/i],[[a,/_/g," "],[e,l],[r,"Nokia"]],[/(pixel (c|tablet))\b/i],[a,[r,R],[e,f]],[/droid.+; (pixel[\daxl ]{0,6})(?: bui|\))/i],[a,[r,R],[e,l]],[/droid.+; (a?\d[0-2]{2}so|[c-g]\d{4}|so[-gl]\w+|xq-a\w[4-7][12])(?= bui|\).+chrome\/(?![1-6]{0,1}\d\.))/i],[a,[r,Z],[e,l]],[/sony tablet [ps]/i,/\b(?:sony)?sgp\w+(?: bui|\))/i],[[a,"Xperia Tablet"],[r,Z],[e,f]],[/ (kb2005|in20[12]5|be20[12][59])\b/i,/(?:one)?(?:plus)? (a\d0\d\d)(?: b|\))/i],[a,[r,Gi],[e,l]],[/(alexa)webm/i,/(kf[a-z]{2}wi|aeo(?!bc)\w\w)( bui|\))/i,/(kf[a-z]+)( bui|\)).+silk\//i],[a,[r,ui],[e,f]],[/((?:sd|kf)[0349hijorstuw]+)( bui|\)).+silk\//i],[[a,/(.+)/g,"Fire Phone $1"],[r,ui],[e,l]],[/(playbook);[-\w\),; ]+(rim)/i],[a,r,[e,f]],[/\b((?:bb[a-f]|st[hv])100-\d)/i,/\(bb10; (\w+)/i],[a,[r,ji],[e,l]],[/(?:\b|asus_)(transfo[prime ]{4,10} \w+|eeepc|slider \w+|nexus 7|padfone|p00[cj])/i],[a,[r,Vi],[e,f]],[/ (z[bes]6[027][012][km][ls]|zenfone \d\w?)\b/i],[a,[r,Vi],[e,l]],[/(nexus 9)/i],[a,[r,"HTC"],[e,f]],[/(htc)[-;_ ]{1,2}([\w ]+(?=\)| bui)|\w+)/i,/(zte)[- ]([\w ]+?)(?: bui|\/|\))/i,/(alcatel|geeksphone|nexian|panasonic(?!(?:;|\.))|sony(?!-bra))[-_ ]?([-\w]*)/i],[r,[a,/_/g," "],[e,l]],[/tcl (xess p17aa)/i,/droid [\w\.]+; ((?:8[14]9[16]|9(?:0(?:48|60|8[01])|1(?:3[27]|66)|2(?:6[69]|9[56])|466))[gqswx])(_\w(\w|\w\w))?(\)| bui)/i],[a,[r,"TCL"],[e,f]],[/droid [\w\.]+; (418(?:7d|8v)|5087z|5102l|61(?:02[dh]|25[adfh]|27[ai]|56[dh]|59k|65[ah])|a509dl|t(?:43(?:0w|1[adepqu])|50(?:6d|7[adju])|6(?:09dl|10k|12b|71[efho]|76[hjk])|7(?:66[ahju]|67[hw]|7[045][bh]|71[hk]|73o|76[ho]|79w|81[hks]?|82h|90[bhsy]|99b)|810[hs]))(_\w(\w|\w\w))?(\)| bui)/i],[a,[r,"TCL"],[e,l]],[/(itel) ((\w+))/i],[[r,A],a,[e,z,{tablet:["p10001l","w7001"],"*":"mobile"}]],[/droid.+; ([ab][1-7]-?[0178a]\d\d?)/i],[a,[r,"Acer"],[e,f]],[/droid.+; (m[1-5] note) bui/i,/\bmz-([-\w]{2,})/i],[a,[r,"Meizu"],[e,l]],[/; ((?:power )?armor(?:[\w ]{0,8}))(?: bui|\))/i],[a,[r,"Ulefone"],[e,l]],[/; (energy ?\w+)(?: bui|\))/i,/; energizer ([\w ]+)(?: bui|\))/i],[a,[r,"Energizer"],[e,l]],[/; cat (b35);/i,/; (b15q?|s22 flip|s48c|s62 pro)(?: bui|\))/i],[a,[r,"Cat"],[e,l]],[/((?:new )?andromax[\w- ]+)(?: bui|\))/i],[a,[r,"Smartfren"],[e,l]],[/droid.+; (a(?:015|06[35]|142p?))/i],[a,[r,"Nothing"],[e,l]],[/(imo) (tab \w+)/i,/(infinix) (x1101b?)/i],[r,a,[e,f]],[/(blackberry|benq|palm(?=\-)|sonyericsson|acer|asus(?! zenw)|dell|jolla|meizu|motorola|polytron|infinix|tecno|micromax|advan)[-_ ]?([-\w]*)/i,/; (hmd|imo) ([\w ]+?)(?: bui|\))/i,/(hp) ([\w ]+\w)/i,/(microsoft); (lumia[\w ]+)/i,/(lenovo)[-_ ]?([-\w ]+?)(?: bui|\)|\/)/i,/(oppo) ?([\w ]+) bui/i],[r,a,[e,l]],[/(kobo)\s(ereader|touch)/i,/(archos) (gamepad2?)/i,/(hp).+(touchpad(?!.+tablet)|tablet)/i,/(kindle)\/([\w\.]+)/i],[r,a,[e,f]],[/(surface duo)/i],[a,[r,_i],[e,f]],[/droid [\d\.]+; (fp\du?)(?: b|\))/i],[a,[r,"Fairphone"],[e,l]],[/((?:tegranote|shield t(?!.+d tv))[\w- ]*?)(?: b|\))/i],[a,[r,Si],[e,f]],[/(sprint) (\w+)/i],[r,a,[e,l]],[/(kin\.[onetw]{3})/i],[[a,/\./g," "],[r,_i],[e,l]],[/droid.+; ([c6]+|et5[16]|mc[239][23]x?|vc8[03]x?)\)/i],[a,[r,Ei],[e,f]],[/droid.+; (ec30|ps20|tc[2-8]\d[kx])\)/i],[a,[r,Ei],[e,l]],[/smart-tv.+(samsung)/i],[r,[e,g]],[/hbbtv.+maple;(\d+)/i],[[a,/^/,"SmartTV"],[r,Q],[e,g]],[/(nux; netcast.+smarttv|lg (netcast\.tv-201\d|android tv))/i],[[r,bi],[e,g]],[/(apple) ?tv/i],[r,[a,P+" TV"],[e,g]],[/crkey.*devicetype\/chromecast/i],[[a,L+" Third Generation"],[r,R],[e,g]],[/crkey.*devicetype\/([^/]*)/i],[[a,/^/,"Chromecast "],[r,R],[e,g]],[/fuchsia.*crkey/i],[[a,L+" Nest Hub"],[r,R],[e,g]],[/crkey/i],[[a,L],[r,R],[e,g]],[/droid.+aft(\w+)( bui|\))/i],[a,[r,ui],[e,g]],[/(shield \w+ tv)/i],[a,[r,Si],[e,g]],[/\(dtv[\);].+(aquos)/i,/(aquos-tv[\w ]+)\)/i],[a,[r,Ki],[e,g]],[/(bravia[\w ]+)( bui|\))/i],[a,[r,Z],[e,g]],[/(mi(tv|box)-?\w+) bui/i],[a,[r,Ci],[e,g]],[/Hbbtv.*(technisat) (.*);/i],[r,a,[e,g]],[/\b(roku)[\dx]*[\)\/]((?:dvp-)?[\d\.]*)/i,/hbbtv\/\d+\.\d+\.\d+ +\([\w\+ ]*; *([\w\d][^;]*);([^;]*)/i],[[r,ci],[a,ci],[e,g]],[/droid.+; ([\w- ]+) (?:android tv|smart[- ]?tv)/i],[a,[e,g]],[/\b(android tv|smart[- ]?tv|opera tv|tv; rv:)\b/i],[[e,g]],[/(ouya)/i,/(nintendo) (\w+)/i],[r,a,[e,ri]],[/droid.+; (shield)( bui|\))/i],[a,[r,Si],[e,ri]],[/(playstation \w+)/i],[a,[r,Z],[e,ri]],[/\b(xbox(?: one)?(?!; xbox))[\); ]/i],[a,[r,_i],[e,ri]],[/\b(sm-[lr]\d\d[0156][fnuw]?s?|gear live)\b/i],[a,[r,Q],[e,N]],[/((pebble))app/i,/(asus|google|lg|oppo) ((pixel |zen)?watch[\w ]*)( bui|\))/i],[r,a,[e,N]],[/(ow(?:19|20)?we?[1-3]{1,3})/i],[a,[r,Ni],[e,N]],[/(watch)(?: ?os[,\/]|\d,\d\/)[\d\.]+/i],[a,[r,P],[e,N]],[/(opwwe\d{3})/i],[a,[r,Gi],[e,N]],[/(moto 360)/i],[a,[r,xi],[e,N]],[/(smartwatch 3)/i],[a,[r,Z],[e,N]],[/(g watch r)/i],[a,[r,bi],[e,N]],[/droid.+; (wt63?0{2,3})\)/i],[a,[r,Ei],[e,N]],[/droid.+; (glass) \d/i],[a,[r,R],[e,yi]],[/(pico) (4|neo3(?: link|pro)?)/i],[r,a,[e,yi]],[/; (quest( \d| pro)?)/i],[a,[r,Qi],[e,yi]],[/(tesla)(?: qtcarbrowser|\/[-\w\.]+)/i],[r,[e,ni]],[/(aeobc)\b/i],[a,[r,ui],[e,ni]],[/(homepod).+mac os/i],[a,[r,P],[e,ni]],[/windows iot/i],[[e,ni]],[/droid .+?; ([^;]+?)(?: bui|; wv\)|\) applew).+?(mobile|vr|\d) safari/i],[a,[e,z,{mobile:"Mobile",xr:"VR","*":f}]],[/\b((tablet|tab)[;\/]|focus\/\d(?!.+mobile))/i],[[e,f]],[/(phone|mobile(?:[;\/]| [ \w\/\.]*safari)|pda(?=.+windows ce))/i],[[e,l]],[/droid .+?; ([\w\. -]+)( bui|\))/i],[a,[r,"Generic"]]],engine:[[/windows.+ edge\/([\w\.]+)/i],[s,[o,Te+"HTML"]],[/(arkweb)\/([\w\.]+)/i],[o,s],[/webkit\/537\.36.+chrome\/(?!27)([\w\.]+)/i],[s,[o,"Blink"]],[/(presto)\/([\w\.]+)/i,/(webkit|trident|netfront|netsurf|amaya|lynx|w3m|goanna|servo)\/([\w\.]+)/i,/ekioh(flow)\/([\w\.]+)/i,/(khtml|tasman|links)[\/ ]\(?([\w\.]+)/i,/(icab)[\/ ]([23]\.[\d\.]+)/i,/\b(libweb)/i],[o,s],[/ladybird\//i],[[o,"LibWeb"]],[/rv\:([\w\.]{1,9})\b.+(gecko)/i],[s,o]],os:[[/microsoft (windows) (vista|xp)/i],[o,s],[/(windows (?:phone(?: os)?|mobile|iot))[\/ ]?([\d\.\w ]*)/i],[o,[s,z,ie]],[/windows nt 6\.2; (arm)/i,/windows[\/ ]([ntce\d\. ]+\w)(?!.+xbox)/i,/(?:win(?=3|9|n)|win 9x )([nt\d\.]+)/i],[[s,z,ie],[o,Li]],[/[adehimnop]{4,7}\b(?:.*os ([\w]+) like mac|; opera)/i,/(?:ios;fbsv\/|iphone.+ios[\/ ])([\d\.]+)/i,/cfnetwork\/.+darwin/i],[[s,/_/g,"."],[o,"iOS"]],[/(mac os x) ?([\w\. ]*)/i,/(macintosh|mac_powerpc\b)(?!.+haiku)/i],[[o,"macOS"],[s,/_/g,"."]],[/android ([\d\.]+).*crkey/i],[s,[o,L+" Android"]],[/fuchsia.*crkey\/([\d\.]+)/i],[s,[o,L+" Fuchsia"]],[/crkey\/([\d\.]+).*devicetype\/smartspeaker/i],[s,[o,L+" SmartSpeaker"]],[/linux.*crkey\/([\d\.]+)/i],[s,[o,L+" Linux"]],[/crkey\/([\d\.]+)/i],[s,[o,L]],[/droid ([\w\.]+)\b.+(android[- ]x86|harmonyos)/i],[s,o],[/(ubuntu) ([\w\.]+) like android/i],[[o,/(.+)/,"$1 Touch"],s],[/(android|bada|blackberry|kaios|maemo|meego|openharmony|qnx|rim tablet os|sailfish|series40|symbian|tizen|webos)\w*[-\/; ]?([\d\.]*)/i],[o,s],[/\(bb(10);/i],[s,[o,ji]],[/(?:symbian ?os|symbos|s60(?=;)|series ?60)[-\/ ]?([\w\.]*)/i],[s,[o,"Symbian"]],[/mozilla\/[\d\.]+ \((?:mobile|tablet|tv|mobile; [\w ]+); rv:.+ gecko\/([\w\.]+)/i],[s,[o,ii+" OS"]],[/web0s;.+rt(tv)/i,/\b(?:hp)?wos(?:browser)?\/([\w\.]+)/i],[s,[o,"webOS"]],[/watch(?: ?os[,\/]|\d,\d\/)([\d\.]+)/i],[s,[o,"watchOS"]],[/(cros) [\w]+(?:\)| ([\w\.]+)\b)/i],[[o,"Chrome OS"],s],[/panasonic;(viera)/i,/(netrange)mmh/i,/(nettv)\/(\d+\.[\w\.]+)/i,/(nintendo|playstation) (\w+)/i,/(xbox); +xbox ([^\);]+)/i,/(pico) .+os([\w\.]+)/i,/\b(joli|palm)\b ?(?:os)?\/?([\w\.]*)/i,/(mint)[\/\(\) ]?(\w*)/i,/(mageia|vectorlinux)[; ]/i,/([kxln]?ubuntu|debian|suse|opensuse|gentoo|arch(?= linux)|slackware|fedora|mandriva|centos|pclinuxos|red ?hat|zenwalk|linpus|raspbian|plan 9|minix|risc os|contiki|deepin|manjaro|elementary os|sabayon|linspire)(?: gnu\/linux)?(?: enterprise)?(?:[- ]linux)?(?:-gnu)?[-\/ ]?(?!chrom|package)([-\w\.]*)/i,/(hurd|linux)(?: arm\w*| x86\w*| ?)([\w\.]*)/i,/(gnu) ?([\w\.]*)/i,/\b([-frentopcghs]{0,5}bsd|dragonfly)[\/ ]?(?!amd|[ix346]{1,2}86)([\w\.]*)/i,/(haiku) (\w+)/i],[o,s],[/(sunos) ?([\w\.\d]*)/i],[[o,"Solaris"],s],[/((?:open)?solaris)[-\/ ]?([\w\.]*)/i,/(aix) ((\d)(?=\.|\)| )[\w\.])*/i,/\b(beos|os\/2|amigaos|morphos|openvms|fuchsia|hp-ux|serenityos)/i,/(unix) ?([\w\.]*)/i],[o,s]]},wi=function(){var i={init:{},isIgnore:{},isIgnoreRgx:{},toString:{}};return q.call(i.init,[[k,[o,s,si,e]],[I,[y]],[C,[e,a,r]],[S,[o,s]],[x,[o,s]]]),q.call(i.isIgnore,[[k,[s,si]],[S,[s]],[x,[s]]]),q.call(i.isIgnoreRgx,[[k,/ ?browser$/i],[x,/ ?os$/i]]),q.call(i.toString,[[k,[o,s]],[I,[y]],[C,[r,a]],[S,[o,s]],[x,[o,s]]]),i}(),qe=function(i,c){var d=wi.init[c],p=wi.isIgnore[c]||0,u=wi.isIgnoreRgx[c]||0,h=wi.toString[c]||0;function b(){q.call(this,d)}return b.prototype.getItem=function(){return i},b.prototype.withClientHints=function(){return D?D.getHighEntropyValues(ce).then(function(m){return i.setCH(new de(m,!1)).parseCH().get()}):i.parseCH().get()},b.prototype.withFeatureCheck=function(){return i.detectFeature().get()},c!=B&&(b.prototype.is=function(m){var w=!1;for(var v in this)if(this.hasOwnProperty(v)&&!Di(p,v)&&A(u?Y(u,this[v]):this[v])==A(u?Y(u,m):m)){if(w=!0,m!=H)break}else if(m==H&&w){w=!w;break}return w},b.prototype.toString=function(){var m=j;for(var w in h)typeof this[h[w]]!==H&&(m+=(m?" ":j)+this[h[w]]);return m||H}),D||(b.prototype.then=function(m){var w=this,v=function(){for(var O in w)w.hasOwnProperty(O)&&(this[O]=w[O])};v.prototype={is:b.prototype.is,toString:b.prototype.toString};var E=new v;return m(E),E}),new b};function de(i,c){if(i=i||{},q.call(this,ce),c)q.call(this,[[zi,Ti(i[M])],[Hi,Ti(i[ye])],[l,/\?1/.test(i[Ne])],[a,ai(i[Ce])],[V,ai(i[se])],[Mi,ai(i[Ee])],[y,ai(i[_e])],[U,Ti(i[Se])],[mi,ai(i[xe])]]);else for(var d in i)this.hasOwnProperty(d)&&typeof i[d]!==H&&(this[d]=i[d])}function ae(i,c,d,p){return this.get=function(u){return u?this.data.hasOwnProperty(u)?this.data[u]:void 0:this.data},this.set=function(u,h){return this.data[u]=h,this},this.setCH=function(u){return this.uaCH=u,this},this.detectFeature=function(){if(_&&_.userAgent==this.ua)switch(this.itemType){case k:_.brave&&typeof _.brave.isBrave==pi&&this.set(o,"Brave");break;case C:!this.get(e)&&D&&D[l]&&this.set(e,l),this.get(a)=="Macintosh"&&_&&typeof _.standalone!==H&&_.maxTouchPoints&&_.maxTouchPoints>2&&this.set(a,"iPad").set(e,f);break;case x:!this.get(o)&&D&&D[V]&&this.set(o,D[V]);break;case B:var u=this.data,h=function(b){return u[b].getItem().detectFeature().get()};this.set(k,h(k)).set(I,h(I)).set(C,h(C)).set(S,h(S)).set(x,h(x))}return this},this.parseUA=function(){return this.itemType!=B&&Ai.call(this.data,this.ua,this.rgxMap),this.itemType==k&&this.set(si,Ii(this.get(s))),this},this.parseCH=function(){var u=this.uaCH,h=this.rgxMap;switch(this.itemType){case k:case S:var b=u[Hi]||u[zi],m;if(b)for(var w in b){var v=b[w].brand||b[w],E=b[w].version;this.itemType==k&&!/not.a.brand/i.test(v)&&(!m||/chrom/i.test(m)&&v!=Xi)&&(v=z(v,{Chrome:"Google Chrome",Edge:"Microsoft Edge","Chrome WebView":"Android WebView","Chrome Headless":"HeadlessChrome"}),this.set(o,v).set(s,E).set(si,Ii(E)),m=v),this.itemType==S&&v==Xi&&this.set(s,E)}break;case I:var O=u[y];O&&(O&&u[mi]=="64"&&(O+="64"),Ai.call(this.data,O+";",h));break;case C:if(u[l]&&this.set(e,l),u[a]&&(this.set(a,u[a]),!this.get(e)||!this.get(r))){var G={};Ai.call(G,"droid 9; "+u[a]+")",h),!this.get(e)&&G.type&&this.set(e,G.type),!this.get(r)&&G.vendor&&this.set(r,G.vendor)}if(u[U]){var di;if(typeof u[U]!="string")for(var Ri=0;!di&&Ri<u[U].length;)di=z(u[U][Ri++],ee);else di=z(u[U],ee);this.set(e,di)}break;case x:var fi=u[V];if(fi){var vi=u[Mi];fi==Li&&(vi=parseInt(Ii(vi),10)>=13?"11":"10"),this.set(o,fi).set(s,vi)}this.get(o)==Li&&u[a]=="Xbox"&&this.set(o,"Xbox").set(s,void 0);break;case B:var ue=this.data,K=function(be){return ue[be].getItem().setCH(u).parseCH().get()};this.set(k,K(k)).set(I,K(I)).set(C,K(C)).set(S,K(S)).set(x,K(x))}return this},q.call(this,[["itemType",i],["ua",c],["uaCH",p],["rgxMap",d],["data",qe(this,i)]]),this}function T(i,c,d){if(typeof i===$?(hi(i,!0)?(typeof c===$&&(d=c),c=i):(d=i,c=void 0),i=void 0):typeof i===Oi&&!hi(c,!0)&&(d=c,c=void 0),d&&typeof d.append===pi){var p={};d.forEach(function(w,v){p[v]=w}),d=p}if(!(this instanceof T))return new T(i,c,d).getResult();var u=typeof i===Oi?i:d&&d[Fi]?d[Fi]:_&&_.userAgent?_.userAgent:j,h=new de(d,!0),b=c?Ae(te,c):te,m=function(w){return w==B?function(){return new ae(w,u,b,h).set("ua",u).set(k,this.getBrowser()).set(I,this.getCPU()).set(C,this.getDevice()).set(S,this.getEngine()).set(x,this.getOS()).get()}:function(){return new ae(w,u,b[w],h).parseUA().get()}};return q.call(this,[["getBrowser",m(k)],["getCPU",m(I)],["getDevice",m(C)],["getEngine",m(S)],["getOS",m(x)],["getResult",m(B)],["getUA",function(){return u}],["setUA",function(w){return W(w)&&(u=w.length>qi?ci(w,qi):w),this}]]).setUA(u),this}T.VERSION=ke,T.BROWSER=li([o,s,si,e]),T.CPU=li([y]),T.DEVICE=li([a,r,e,ri,l,g,f,N,ni]),T.ENGINE=T.OS=li([o,s]);const oi={EMAIL_SEND_OFF:0,EMAIL_SEND_HOURLY:1,EMAIL_SEND_3HOURLY:2,EMAIL_SEND_DAILY:3,EMAIL_SEND_WEEKLY:4},Oe=[{text:t("notifications","Never"),value:oi.EMAIL_SEND_OFF},{text:t("notifications","1 hour"),value:oi.EMAIL_SEND_HOURLY},{text:t("notifications","3 hours"),value:oi.EMAIL_SEND_3HOURLY},{text:t("notifications","1 day"),value:oi.EMAIL_SEND_DAILY},{text:t("notifications","1 week"),value:oi.EMAIL_SEND_WEEKLY}],oe={id:null,label:t("notifications","None")},Le=new T,re=Le.getBrowser(),ne=re.name==="Safari"||re.name==="Mobile Safari",De={name:"UserSettings",components:{NcCheckboxRadioSwitch:pe,NcSelect:ve,NcSettingsSection:ge},setup(){const i=Ui(he("notifications","config")),c=Ui({secondary_speaker:J.getItem("secondary_speaker")==="true",secondary_speaker_device:JSON.parse(J.getItem("secondary_speaker_device"))??oe}),d=le([]);return{BATCHTIME_OPTIONS:Oe,isSafari:ne,config:i,storage:c,devices:d}},methods:{async updateSettings(){try{const i=new FormData;i.append("batchSetting",this.config.setting_batchtime),i.append("soundNotification",this.config.sound_notification?"yes":"no"),i.append("soundTalk",this.config.sound_talk?"yes":"no"),await me.post(fe("apps/notifications/api/v2/settings"),i),Pi(t("notifications","Your settings have been updated."))}catch(i){ki(t("notifications","An error occurred while updating your settings.")),console.error(i)}},updateLocalSettings(){try{J.setItem("secondary_speaker",this.storage.secondary_speaker),this.storage.secondary_speaker&&this.storage.secondary_speaker_device.id?J.setItem("secondary_speaker_device",JSON.stringify(this.storage.secondary_speaker_device)):J.removeItem("secondary_speaker_device"),Pi(t("notifications","Your settings have been updated."))}catch(i){ki(t("notifications","An error occurred while updating your settings.")),console.error(i)}},async initializeDevices(){if(!(!ne&&navigator?.mediaDevices?.getUserMedia&&navigator?.mediaDevices?.enumerateDevices)||this.devices.length>0)return;let i=null;try{i=await navigator.mediaDevices.getUserMedia({audio:!0}),this.devices=(await navigator.mediaDevices.enumerateDevices()??[]).filter(c=>c.kind==="audiooutput").map(c=>({id:c.deviceId,label:c.label?c.label:c.fallbackLabel})).concat([oe])}catch(c){ki(t("notifications","An error occurred while updating your settings.")),console.error("Error while requesting or initializing audio devices: ",c)}finally{i&&i.getTracks().forEach(c=>c.stop())}}}};var ze=function(){var i=this,c=i._self._c;return c("NcSettingsSection",{attrs:{name:i.t("notifications","Notifications")}},[c("div",{staticClass:"notification-frequency__warning"},[i.config.is_email_set?i._e():c("strong",[i._v(i._s(i.t("notifications","You need to set up your email address before you can receive notification emails.")))])]),c("p",[c("label",{staticClass:"notification-frequency__label",attrs:{for:"notification_reminder_batchtime"}},[i._v(" "+i._s(i.t("notifications","Send email reminders about unhandled notifications after:"))+" ")]),c("select",{directives:[{name:"model",rawName:"v-model",value:i.config.setting_batchtime,expression:"config.setting_batchtime"}],staticClass:"notification-frequency__select",attrs:{id:"notification_reminder_batchtime",name:"notification_reminder_batchtime"},on:{change:[function(d){var p=Array.prototype.filter.call(d.target.options,function(u){return u.selected}).map(function(u){var h="_value"in u?u._value:u.value;return h});i.$set(i.config,"setting_batchtime",d.target.multiple?p:p[0])},function(d){return i.updateSettings()}]}},i._l(i.BATCHTIME_OPTIONS,function(d){return c("option",{key:d.value,domProps:{value:d.value}},[i._v(" "+i._s(d.text)+" ")])}),0)]),c("NcCheckboxRadioSwitch",{attrs:{checked:i.config.sound_notification},on:{"update:checked":[function(d){return i.$set(i.config,"sound_notification",d)},i.updateSettings]}},[i._v(" "+i._s(i.t("notifications","Play sound when a new notification arrives"))+" ")]),c("NcCheckboxRadioSwitch",{attrs:{checked:i.config.sound_talk},on:{"update:checked":[function(d){return i.$set(i.config,"sound_talk",d)},i.updateSettings]}},[i._v(" "+i._s(i.t("notifications","Play sound when a call started (requires Nextcloud Talk)"))+" ")]),i.config.sound_talk?[c("NcCheckboxRadioSwitch",{staticClass:"additional-margin-top",attrs:{checked:i.storage.secondary_speaker,disabled:i.isSafari},on:{"update:checked":[function(d){return i.$set(i.storage,"secondary_speaker",d)},i.updateLocalSettings]}},[i._v(" "+i._s(i.t("notifications","Also repeat sound on a secondary speaker"))+" ")]),i.isSafari?c("div",{staticClass:"notification-frequency__warning"},[c("strong",[i._v(i._s(i.t("notifications","Selection of the speaker device is currently not supported by Safari")))])]):i._e(),!i.isSafari&&i.storage.secondary_speaker?c("NcSelect",{attrs:{"input-id":"device-selector-audio-output",options:i.devices,label:"label","aria-label-combobox":i.t("notifications","Select a device"),clearable:!1,placeholder:i.t("notifications","Select a device")},on:{open:i.initializeDevices,input:i.updateLocalSettings},model:{value:i.storage.secondary_speaker_device,callback:function(d){i.$set(i.storage,"secondary_speaker_device",d)},expression:"storage.secondary_speaker_device"}}):i._e()]:i._e()],2)},He=[],Me=we(De,ze,He,!1,null,"6f3d8087");const Re=Me.exports;gi.prototype.t=t,gi.prototype.n=n,new gi({el:"#notifications-user-settings",render:i=>i(Re)});
