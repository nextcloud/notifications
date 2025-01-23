/*! third party licenses: js/vendor.LICENSE.txt */
import{r as Li,a as ne,V as mi}from"./style-5Y66x2kd.chunk.mjs";import{n as se,N as ce,l as de,c as ue,v as le,s as Mi,a as vi}from"./_plugin-vue2_normalizer-A9pcYVBu.chunk.mjs";import{N as we,B as X}from"./BrowserStorage-DNTMrFZz.chunk.mjs";import{N as be}from"./NcSettingsSection-DHX2Y1Ed-DcC4FItc.chunk.mjs";var pe="2.0.0",j="",zi="?",wi="function",L="undefined",V="object",Ni="string",ai="major",r="model",o="name",e="type",c="vendor",a="version",k="architecture",U="console",w="mobile",m="tablet",g="smarttv",ti="wearable",gi="xr",Ei="embedded",$="inapp",Hi="user-agent",Ci=500,Ii="brands",H="formFactors",Oi="fullVersionList",B="platform",qi="platformVersion",pi="bitness",M="sec-ch-ua",fe=M+"-full-version-list",he=M+"-arch",me=M+"-"+pi,ve=M+"-form-factors",ge=M+"-"+w,ke=M+"-"+r,ee=M+"-"+B,ye=ee+"-version",te=[Ii,Oi,w,r,B,qi,k,H,pi],_="browser",E="cpu",S="device",C="engine",x="os",F="result",si="Amazon",W="Apple",Pi="ASUS",Ri="BlackBerry",z="Google",Ui="Huawei",Bi="Lenovo",_e="Honor",ki="LG",li="Microsoft",Fi="Motorola",K="Samsung",ji="Sharp",ci="Sony",yi="Xiaomi",_i="Zebra",R="Mobile ",J=" Browser",Vi="Chrome",q="Chromecast",xe="Edge",Q="Firefox",Z="Opera",Yi="Facebook",Gi="Sogou",Ti="Windows",Se=typeof window!==L,y=Se&&window.navigator?window.navigator:void 0,D=y&&y.userAgentData?y.userAgentData:void 0,Ne=function(i,s){var d={},p=s;if(!bi(s)){p={};for(var u in s)for(var h in s[u])p[h]=s[u][h].concat(p[h]?p[h]:[])}for(var l in i)d[l]=p[l]&&p[l].length%2===0?p[l].concat(i[l]):i[l];return d},di=function(i){for(var s={},d=0;d<i.length;d++)s[i[d].toUpperCase()]=i[d];return s},Ai=function(i,s){if(typeof i===V&&i.length>0){for(var d in i)if(T(i[d])==T(s))return!0;return!1}return Y(i)?T(s).indexOf(T(i))!==-1:!1},bi=function(i,s){for(var d in i)return/^(browser|cpu|device|engine|os)$/.test(d)||(s?bi(i[d]):!1)},Y=function(i){return typeof i===Ni},xi=function(i){if(i){for(var s=[],d=P(/\\?\"/g,i).split(","),p=0;p<d.length;p++)if(d[p].indexOf(";")>-1){var u=ri(d[p]).split(";v=");s[p]={brand:u[0],version:u[1]}}else s[p]=ri(d[p]);return s}},T=function(i){return Y(i)?i.toLowerCase():i},Si=function(i){return Y(i)?P(/[^\d\.]/g,i).split(".")[0]:void 0},A=function(i){for(var s in i){var d=i[s];typeof d==V&&d.length==2?this[d[0]]=d[1]:this[d]=void 0}return this},P=function(i,s){return Y(s)?s.replace(i,j):s},ii=function(i){return P(/\\?\"/g,i)},ri=function(i,s){if(Y(i))return i=P(/^\s\s*/,i),typeof s===L?i:i.substring(0,Ci)},Xi=function(i,s){if(!(!i||!s))for(var d=0,p,u,h,l,f,b;d<s.length&&!f;){var v=s[d],I=s[d+1];for(p=u=0;p<v.length&&!f&&v[p];)if(f=v[p++].exec(i),f)for(h=0;h<I.length;h++)b=f[++u],l=I[h],typeof l===V&&l.length>0?l.length===2?typeof l[1]==wi?this[l[0]]=l[1].call(this,b):this[l[0]]=l[1]:l.length===3?typeof l[1]===wi&&!(l[1].exec&&l[1].test)?this[l[0]]=b?l[1].call(this,b,l[2]):void 0:this[l[0]]=b?b.replace(l[1],l[2]):void 0:l.length===4&&(this[l[0]]=b?l[3].call(this,b.replace(l[1],l[2])):void 0):this[l]=b||void 0;d+=2}},oi=function(i,s){for(var d in s)if(typeof s[d]===V&&s[d].length>0){for(var p=0;p<s[d].length;p++)if(Ai(s[d][p],i))return d===zi?void 0:d}else if(Ai(s[d],i))return d===zi?void 0:d;return s.hasOwnProperty("*")?s["*"]:i},$i={ME:"4.90","NT 3.11":"NT3.51","NT 4.0":"NT4.0",2e3:"NT 5.0",XP:["NT 5.1","NT 5.2"],Vista:"NT 6.0",7:"NT 6.1",8:"NT 6.2","8.1":"NT 6.3",10:["NT 6.4","NT 10.0"],RT:"ARM"},Wi={embedded:"Automotive",mobile:"Mobile",tablet:["Tablet","EInk"],smarttv:"TV",wearable:"Watch",xr:["VR","XR"],"?":["Desktop","Unknown"],"*":void 0},Ki={browser:[[/\b(?:crmo|crios)\/([\w\.]+)/i],[a,[o,R+"Chrome"]],[/edg(?:e|ios|a)?\/([\w\.]+)/i],[a,[o,"Edge"]],[/(opera mini)\/([-\w\.]+)/i,/(opera [mobiletab]{3,6})\b.+version\/([-\w\.]+)/i,/(opera)(?:.+version\/|[\/ ]+)([\w\.]+)/i],[o,a],[/opios[\/ ]+([\w\.]+)/i],[a,[o,Z+" Mini"]],[/\bop(?:rg)?x\/([\w\.]+)/i],[a,[o,Z+" GX"]],[/\bopr\/([\w\.]+)/i],[a,[o,Z]],[/\bb[ai]*d(?:uhd|[ub]*[aekoprswx]{5,6})[\/ ]?([\w\.]+)/i],[a,[o,"Baidu"]],[/\b(?:mxbrowser|mxios|myie2)\/?([-\w\.]*)\b/i],[a,[o,"Maxthon"]],[/(kindle)\/([\w\.]+)/i,/(lunascape|maxthon|netfront|jasmine|blazer|sleipnir)[\/ ]?([\w\.]*)/i,/(avant|iemobile|slim(?:browser|boat|jet))[\/ ]?([\d\.]*)/i,/(?:ms|\()(ie) ([\w\.]+)/i,/(flock|rockmelt|midori|epiphany|silk|skyfire|ovibrowser|bolt|iron|vivaldi|iridium|phantomjs|bowser|qupzilla|falkon|rekonq|puffin|brave|whale(?!.+naver)|qqbrowserlite|duckduckgo|klar|helio|(?=comodo_)?dragon)\/([-\w\.]+)/i,/(heytap|ovi|115)browser\/([\d\.]+)/i,/(weibo)__([\d\.]+)/i],[o,a],[/quark(?:pc)?\/([-\w\.]+)/i],[a,[o,"Quark"]],[/\bddg\/([\w\.]+)/i],[a,[o,"DuckDuckGo"]],[/(?:\buc? ?browser|(?:juc.+)ucweb)[\/ ]?([\w\.]+)/i],[a,[o,"UCBrowser"]],[/microm.+\bqbcore\/([\w\.]+)/i,/\bqbcore\/([\w\.]+).+microm/i,/micromessenger\/([\w\.]+)/i],[a,[o,"WeChat"]],[/konqueror\/([\w\.]+)/i],[a,[o,"Konqueror"]],[/trident.+rv[: ]([\w\.]{1,9})\b.+like gecko/i],[a,[o,"IE"]],[/ya(?:search)?browser\/([\w\.]+)/i],[a,[o,"Yandex"]],[/slbrowser\/([\w\.]+)/i],[a,[o,"Smart "+Bi+J]],[/(avast|avg)\/([\w\.]+)/i],[[o,/(.+)/,"$1 Secure"+J],a],[/\bfocus\/([\w\.]+)/i],[a,[o,Q+" Focus"]],[/\bopt\/([\w\.]+)/i],[a,[o,Z+" Touch"]],[/coc_coc\w+\/([\w\.]+)/i],[a,[o,"Coc Coc"]],[/dolfin\/([\w\.]+)/i],[a,[o,"Dolphin"]],[/coast\/([\w\.]+)/i],[a,[o,Z+" Coast"]],[/miuibrowser\/([\w\.]+)/i],[a,[o,"MIUI"+J]],[/fxios\/([\w\.-]+)/i],[a,[o,R+Q]],[/\bqihoobrowser\/?([\w\.]*)/i],[a,[o,"360"]],[/\b(qq)\/([\w\.]+)/i],[[o,/(.+)/,"$1Browser"],a],[/(oculus|sailfish|huawei|vivo|pico)browser\/([\w\.]+)/i],[[o,/(.+)/,"$1"+J],a],[/samsungbrowser\/([\w\.]+)/i],[a,[o,K+" Internet"]],[/metasr[\/ ]?([\d\.]+)/i],[a,[o,Gi+" Explorer"]],[/(sogou)mo\w+\/([\d\.]+)/i],[[o,Gi+" Mobile"],a],[/(electron)\/([\w\.]+) safari/i,/(tesla)(?: qtcarbrowser|\/(20\d\d\.[-\w\.]+))/i,/m?(qqbrowser|2345(?=browser|chrome|explorer))\w*[\/ ]?v?([\w\.]+)/i],[o,a],[/(lbbrowser|rekonq)/i],[o],[/ome\/([\w\.]+) \w* ?(iron) saf/i,/ome\/([\w\.]+).+qihu (360)[es]e/i],[a,o],[/((?:fban\/fbios|fb_iab\/fb4a)(?!.+fbav)|;fbav\/([\w\.]+);)/i],[[o,Yi],a,[e,$]],[/(Klarna)\/([\w\.]+)/i,/(kakao(?:talk|story))[\/ ]([\w\.]+)/i,/(naver)\(.*?(\d+\.[\w\.]+).*\)/i,/safari (line)\/([\w\.]+)/i,/\b(line)\/([\w\.]+)\/iab/i,/(alipay)client\/([\w\.]+)/i,/(twitter)(?:and| f.+e\/([\w\.]+))/i,/(instagram|snapchat)[\/ ]([-\w\.]+)/i],[o,a,[e,$]],[/\bgsa\/([\w\.]+) .*safari\//i],[a,[o,"GSA"],[e,$]],[/musical_ly(?:.+app_?version\/|_)([\w\.]+)/i],[a,[o,"TikTok"],[e,$]],[/\[(linkedin)app\]/i],[o,[e,$]],[/(chromium)[\/ ]([-\w\.]+)/i],[o,a],[/headlesschrome(?:\/([\w\.]+)| )/i],[a,[o,Vi+" Headless"]],[/ wv\).+(chrome)\/([\w\.]+)/i],[[o,Vi+" WebView"],a],[/droid.+ version\/([\w\.]+)\b.+(?:mobile safari|safari)/i],[a,[o,"Android"+J]],[/chrome\/([\w\.]+) mobile/i],[a,[o,R+"Chrome"]],[/(chrome|omniweb|arora|[tizenoka]{5} ?browser)\/v?([\w\.]+)/i],[o,a],[/version\/([\w\.\,]+) .*mobile(?:\/\w+ | ?)safari/i],[a,[o,R+"Safari"]],[/iphone .*mobile(?:\/\w+ | ?)safari/i],[[o,R+"Safari"]],[/version\/([\w\.\,]+) .*(safari)/i],[a,o],[/webkit.+?(mobile ?safari|safari)(\/[\w\.]+)/i],[o,[a,"1"]],[/(webkit|khtml)\/([\w\.]+)/i],[o,a],[/(?:mobile|tablet);.*(firefox)\/([\w\.-]+)/i],[[o,R+Q],a],[/(navigator|netscape\d?)\/([-\w\.]+)/i],[[o,"Netscape"],a],[/(wolvic|librewolf)\/([\w\.]+)/i],[o,a],[/mobile vr; rv:([\w\.]+)\).+firefox/i],[a,[o,Q+" Reality"]],[/ekiohf.+(flow)\/([\w\.]+)/i,/(swiftfox)/i,/(icedragon|iceweasel|camino|chimera|fennec|maemo browser|minimo|conkeror)[\/ ]?([\w\.\+]+)/i,/(seamonkey|k-meleon|icecat|iceape|firebird|phoenix|palemoon|basilisk|waterfox)\/([-\w\.]+)$/i,/(firefox)\/([\w\.]+)/i,/(mozilla)\/([\w\.]+) .+rv\:.+gecko\/\d+/i,/(polaris|lynx|dillo|icab|doris|amaya|w3m|netsurf|obigo|mosaic|(?:go|ice|up)[\. ]?browser)[-\/ ]?v?([\w\.]+)/i,/\b(links) \(([\w\.]+)/i],[o,[a,/_/g,"."]],[/(cobalt)\/([\w\.]+)/i],[o,[a,/[^\d\.]+./,j]]],cpu:[[/\b(?:(amd|x|x86[-_]?|wow|win)64)\b/i],[[k,"amd64"]],[/(ia32(?=;))/i,/((?:i[346]|x)86)[;\)]/i],[[k,"ia32"]],[/\b(aarch64|arm(v?8e?l?|_?64))\b/i],[[k,"arm64"]],[/\b(arm(?:v[67])?ht?n?[fl]p?)\b/i],[[k,"armhf"]],[/windows (ce|mobile); ppc;/i],[[k,"arm"]],[/((?:ppc|powerpc)(?:64)?)(?: mac|;|\))/i],[[k,/ower/,j,T]],[/(sun4\w)[;\)]/i],[[k,"sparc"]],[/((?:avr32|ia64(?=;))|68k(?=\))|\barm(?=v(?:[1-7]|[5-7]1)l?|;|eabi)|(?=atmel )avr|(?:irix|mips|sparc)(?:64)?\b|pa-risc)/i],[[k,T]]],device:[[/\b(sch-i[89]0\d|shw-m380s|sm-[ptx]\w{2,4}|gt-[pn]\d{2,4}|sgh-t8[56]9|nexus 10)/i],[r,[c,K],[e,m]],[/\b((?:s[cgp]h|gt|sm)-(?![lr])\w+|sc[g-]?[\d]+a?|galaxy nexus)/i,/samsung[- ]((?!sm-[lr])[-\w]+)/i,/sec-(sgh\w+)/i],[r,[c,K],[e,w]],[/(?:\/|\()(ip(?:hone|od)[\w, ]*)(?:\/|;)/i],[r,[c,W],[e,w]],[/\((ipad);[-\w\),; ]+apple/i,/applecoremedia\/[\w\.]+ \((ipad)/i,/\b(ipad)\d\d?,\d\d?[;\]].+ios/i],[r,[c,W],[e,m]],[/(macintosh);/i],[r,[c,W]],[/\b(sh-?[altvz]?\d\d[a-ekm]?)/i],[r,[c,ji],[e,w]],[/(?:honor)([-\w ]+)[;\)]/i],[r,[c,_e],[e,w]],[/\b((?:ag[rs][23]?|bah2?|sht?|btv)-a?[lw]\d{2})\b(?!.+d\/s)/i],[r,[c,Ui],[e,m]],[/(?:huawei)([-\w ]+)[;\)]/i,/\b(nexus 6p|\w{2,4}e?-[atu]?[ln][\dx][012359c][adn]?)\b(?!.+d\/s)/i],[r,[c,Ui],[e,w]],[/\b(poco[\w ]+|m2\d{3}j\d\d[a-z]{2})(?: bui|\))/i,/\b; (\w+) build\/hm\1/i,/\b(hm[-_ ]?note?[_ ]?(?:\d\w)?) bui/i,/\b(redmi[\-_ ]?(?:note|k)?[\w_ ]+)(?: bui|\))/i,/oid[^\)]+; (m?[12][0-389][01]\w{3,6}[c-y])( bui|; wv|\))/i,/\b(mi[-_ ]?(?:a\d|one|one[_ ]plus|note lte|max|cc)?[_ ]?(?:\d?\w?)[_ ]?(?:plus|se|lite|pro)?)(?: bui|\))/i],[[r,/_/g," "],[c,yi],[e,w]],[/oid[^\)]+; (2\d{4}(283|rpbf)[cgl])( bui|\))/i,/\b(mi[-_ ]?(?:pad)(?:[\w_ ]+))(?: bui|\))/i],[[r,/_/g," "],[c,yi],[e,m]],[/; (\w+) bui.+ oppo/i,/\b(cph[12]\d{3}|p(?:af|c[al]|d\w|e[ar])[mt]\d0|x9007|a101op)\b/i],[r,[c,"OPPO"],[e,w]],[/\b(opd2\d{3}a?) bui/i],[r,[c,"OPPO"],[e,m]],[/vivo (\w+)(?: bui|\))/i,/\b(v[12]\d{3}\w?[at])(?: bui|;)/i],[r,[c,"Vivo"],[e,w]],[/\b(rmx[1-3]\d{3})(?: bui|;|\))/i],[r,[c,"Realme"],[e,w]],[/\b(milestone|droid(?:[2-4x]| (?:bionic|x2|pro|razr))?:?( 4g)?)\b[\w ]+build\//i,/\bmot(?:orola)?[- ](\w*)/i,/((?:moto[\w\(\) ]+|xt\d{3,4}|nexus 6)(?= bui|\)))/i],[r,[c,Fi],[e,w]],[/\b(mz60\d|xoom[2 ]{0,2}) build\//i],[r,[c,Fi],[e,m]],[/((?=lg)?[vl]k\-?\d{3}) bui| 3\.[-\w; ]{10}lg?-([06cv9]{3,4})/i],[r,[c,ki],[e,m]],[/(lm(?:-?f100[nv]?|-[\w\.]+)(?= bui|\))|nexus [45])/i,/\blg[-e;\/ ]+((?!browser|netcast|android tv)\w+)/i,/\blg-?([\d\w]+) bui/i],[r,[c,ki],[e,w]],[/(ideatab[-\w ]+)/i,/lenovo ?(s[56]000[-\w]+|tab(?:[\w ]+)|yt[-\d\w]{6}|tb[-\d\w]{6})/i],[r,[c,Bi],[e,m]],[/(?:maemo|nokia).*(n900|lumia \d+)/i,/nokia[-_ ]?([-\w\.]*)/i],[[r,/_/g," "],[c,"Nokia"],[e,w]],[/(pixel c)\b/i],[r,[c,z],[e,m]],[/droid.+; (pixel[\daxl ]{0,6})(?: bui|\))/i],[r,[c,z],[e,w]],[/droid.+; (a?\d[0-2]{2}so|[c-g]\d{4}|so[-gl]\w+|xq-a\w[4-7][12])(?= bui|\).+chrome\/(?![1-6]{0,1}\d\.))/i],[r,[c,ci],[e,w]],[/sony tablet [ps]/i,/\b(?:sony)?sgp\w+(?: bui|\))/i],[[r,"Xperia Tablet"],[c,ci],[e,m]],[/ (kb2005|in20[12]5|be20[12][59])\b/i,/(?:one)?(?:plus)? (a\d0\d\d)(?: b|\))/i],[r,[c,"OnePlus"],[e,w]],[/(alexa)webm/i,/(kf[a-z]{2}wi|aeo(?!bc)\w\w)( bui|\))/i,/(kf[a-z]+)( bui|\)).+silk\//i],[r,[c,si],[e,m]],[/((?:sd|kf)[0349hijorstuw]+)( bui|\)).+silk\//i],[[r,/(.+)/g,"Fire Phone $1"],[c,si],[e,w]],[/(playbook);[-\w\),; ]+(rim)/i],[r,c,[e,m]],[/\b((?:bb[a-f]|st[hv])100-\d)/i,/\(bb10; (\w+)/i],[r,[c,Ri],[e,w]],[/(?:\b|asus_)(transfo[prime ]{4,10} \w+|eeepc|slider \w+|nexus 7|padfone|p00[cj])/i],[r,[c,Pi],[e,m]],[/ (z[bes]6[027][012][km][ls]|zenfone \d\w?)\b/i],[r,[c,Pi],[e,w]],[/(nexus 9)/i],[r,[c,"HTC"],[e,m]],[/(htc)[-;_ ]{1,2}([\w ]+(?=\)| bui)|\w+)/i,/(zte)[- ]([\w ]+?)(?: bui|\/|\))/i,/(alcatel|geeksphone|nexian|panasonic(?!(?:;|\.))|sony(?!-bra))[-_ ]?([-\w]*)/i],[c,[r,/_/g," "],[e,w]],[/tcl (xess p17aa)/i,/droid [\w\.]+; ((?:8[14]9[16]|9(?:0(?:48|60|8[01])|1(?:3[27]|66)|2(?:6[69]|9[56])|466))[gqswx])(_\w(\w|\w\w))?(\)| bui)/i],[r,[c,"TCL"],[e,m]],[/droid [\w\.]+; (418(?:7d|8v)|5087z|5102l|61(?:02[dh]|25[adfh]|27[ai]|56[dh]|59k|65[ah])|a509dl|t(?:43(?:0w|1[adepqu])|50(?:6d|7[adju])|6(?:09dl|10k|12b|71[efho]|76[hjk])|7(?:66[ahju]|67[hw]|7[045][bh]|71[hk]|73o|76[ho]|79w|81[hks]?|82h|90[bhsy]|99b)|810[hs]))(_\w(\w|\w\w))?(\)| bui)/i],[r,[c,"TCL"],[e,w]],[/(itel) ((\w+))/i],[[c,T],r,[e,oi,{tablet:["p10001l","w7001"],"*":"mobile"}]],[/droid.+; ([ab][1-7]-?[0178a]\d\d?)/i],[r,[c,"Acer"],[e,m]],[/droid.+; (m[1-5] note) bui/i,/\bmz-([-\w]{2,})/i],[r,[c,"Meizu"],[e,w]],[/; ((?:power )?armor(?:[\w ]{0,8}))(?: bui|\))/i],[r,[c,"Ulefone"],[e,w]],[/; (energy ?\w+)(?: bui|\))/i,/; energizer ([\w ]+)(?: bui|\))/i],[r,[c,"Energizer"],[e,w]],[/; cat (b35);/i,/; (b15q?|s22 flip|s48c|s62 pro)(?: bui|\))/i],[r,[c,"Cat"],[e,w]],[/((?:new )?andromax[\w- ]+)(?: bui|\))/i],[r,[c,"Smartfren"],[e,w]],[/droid.+; (a(?:015|06[35]|142p?))/i],[r,[c,"Nothing"],[e,w]],[/(blackberry|benq|palm(?=\-)|sonyericsson|acer|asus|dell|meizu|motorola|polytron|infinix|tecno|micromax|advan)[-_ ]?([-\w]*)/i,/; (imo) ((?!tab)[\w ]+?)(?: bui|\))/i,/(hp) ([\w ]+\w)/i,/(asus)-?(\w+)/i,/(microsoft); (lumia[\w ]+)/i,/(lenovo)[-_ ]?([-\w]+)/i,/(jolla)/i,/(oppo) ?([\w ]+) bui/i],[c,r,[e,w]],[/(imo) (tab \w+)/i,/(kobo)\s(ereader|touch)/i,/(archos) (gamepad2?)/i,/(hp).+(touchpad(?!.+tablet)|tablet)/i,/(kindle)\/([\w\.]+)/i],[c,r,[e,m]],[/(surface duo)/i],[r,[c,li],[e,m]],[/droid [\d\.]+; (fp\du?)(?: b|\))/i],[r,[c,"Fairphone"],[e,w]],[/(shield[\w ]+) b/i],[r,[c,"Nvidia"],[e,m]],[/(sprint) (\w+)/i],[c,r,[e,w]],[/(kin\.[onetw]{3})/i],[[r,/\./g," "],[c,li],[e,w]],[/droid.+; ([c6]+|et5[16]|mc[239][23]x?|vc8[03]x?)\)/i],[r,[c,_i],[e,m]],[/droid.+; (ec30|ps20|tc[2-8]\d[kx])\)/i],[r,[c,_i],[e,w]],[/smart-tv.+(samsung)/i],[c,[e,g]],[/hbbtv.+maple;(\d+)/i],[[r,/^/,"SmartTV"],[c,K],[e,g]],[/(nux; netcast.+smarttv|lg (netcast\.tv-201\d|android tv))/i],[[c,ki],[e,g]],[/(apple) ?tv/i],[c,[r,W+" TV"],[e,g]],[/crkey.*devicetype\/chromecast/i],[[r,q+" Third Generation"],[c,z],[e,g]],[/crkey.*devicetype\/([^/]*)/i],[[r,/^/,"Chromecast "],[c,z],[e,g]],[/fuchsia.*crkey/i],[[r,q+" Nest Hub"],[c,z],[e,g]],[/crkey/i],[[r,q],[c,z],[e,g]],[/droid.+aft(\w+)( bui|\))/i],[r,[c,si],[e,g]],[/\(dtv[\);].+(aquos)/i,/(aquos-tv[\w ]+)\)/i],[r,[c,ji],[e,g]],[/(bravia[\w ]+)( bui|\))/i],[r,[c,ci],[e,g]],[/(mitv-\w{5}) bui/i],[r,[c,yi],[e,g]],[/Hbbtv.*(technisat) (.*);/i],[c,r,[e,g]],[/\b(roku)[\dx]*[\)\/]((?:dvp-)?[\d\.]*)/i,/hbbtv\/\d+\.\d+\.\d+ +\([\w\+ ]*; *([\w\d][^;]*);([^;]*)/i],[[c,ri],[r,ri],[e,g]],[/\b(android tv|smart[- ]?tv|opera tv|tv; rv:)\b/i],[[e,g]],[/(ouya)/i,/(nintendo) (\w+)/i],[c,r,[e,U]],[/droid.+; (shield) bui/i],[r,[c,"Nvidia"],[e,U]],[/(playstation \w+)/i],[r,[c,ci],[e,U]],[/\b(xbox(?: one)?(?!; xbox))[\); ]/i],[r,[c,li],[e,U]],[/\b(sm-[lr]\d\d[05][fnuw]?s?)\b/i],[r,[c,K],[e,ti]],[/((pebble))app/i],[c,r,[e,ti]],[/(watch)(?: ?os[,\/]|\d,\d\/)[\d\.]+/i],[r,[c,W],[e,ti]],[/droid.+; (wt63?0{2,3})\)/i],[r,[c,_i],[e,ti]],[/droid.+; (glass) \d/i],[r,[c,z],[e,gi]],[/(pico) (4|neo3(?: link|pro)?)/i],[c,r,[e,gi]],[/; (quest( \d| pro)?)/i],[r,[c,Yi],[e,gi]],[/(tesla)(?: qtcarbrowser|\/[-\w\.]+)/i],[c,[e,Ei]],[/(aeobc)\b/i],[r,[c,si],[e,Ei]],[/droid .+?; ([^;]+?)(?: bui|; wv\)|\) applew).+? mobile safari/i],[r,[e,w]],[/droid .+?; ([^;]+?)(?: bui|\) applew).+?(?! mobile) safari/i],[r,[e,m]],[/\b((tablet|tab)[;\/]|focus\/\d(?!.+mobile))/i],[[e,m]],[/(phone|mobile(?:[;\/]| [ \w\/\.]*safari)|pda(?=.+windows ce))/i],[[e,w]],[/(android[-\w\. ]{0,9});.+buil/i],[r,[c,"Generic"]]],engine:[[/windows.+ edge\/([\w\.]+)/i],[a,[o,xe+"HTML"]],[/(arkweb)\/([\w\.]+)/i],[o,a],[/webkit\/537\.36.+chrome\/(?!27)([\w\.]+)/i],[a,[o,"Blink"]],[/(presto)\/([\w\.]+)/i,/(webkit|trident|netfront|netsurf|amaya|lynx|w3m|goanna|servo)\/([\w\.]+)/i,/ekioh(flow)\/([\w\.]+)/i,/(khtml|tasman|links)[\/ ]\(?([\w\.]+)/i,/(icab)[\/ ]([23]\.[\d\.]+)/i,/\b(libweb)/i],[o,a],[/rv\:([\w\.]{1,9})\b.+(gecko)/i],[a,o]],os:[[/microsoft (windows) (vista|xp)/i],[o,a],[/(windows (?:phone(?: os)?|mobile))[\/ ]?([\d\.\w ]*)/i],[o,[a,oi,$i]],[/windows nt 6\.2; (arm)/i,/windows[\/ ]?([ntce\d\. ]+\w)(?!.+xbox)/i,/(?:win(?=3|9|n)|win 9x )([nt\d\.]+)/i],[[a,oi,$i],[o,Ti]],[/ip[honead]{2,4}\b(?:.*os ([\w]+) like mac|; opera)/i,/(?:ios;fbsv\/|iphone.+ios[\/ ])([\d\.]+)/i,/cfnetwork\/.+darwin/i],[[a,/_/g,"."],[o,"iOS"]],[/(mac os x) ?([\w\. ]*)/i,/(macintosh|mac_powerpc\b)(?!.+haiku)/i],[[o,"macOS"],[a,/_/g,"."]],[/android ([\d\.]+).*crkey/i],[a,[o,q+" Android"]],[/fuchsia.*crkey\/([\d\.]+)/i],[a,[o,q+" Fuchsia"]],[/crkey\/([\d\.]+).*devicetype\/smartspeaker/i],[a,[o,q+" SmartSpeaker"]],[/linux.*crkey\/([\d\.]+)/i],[a,[o,q+" Linux"]],[/crkey\/([\d\.]+)/i],[a,[o,q]],[/droid ([\w\.]+)\b.+(android[- ]x86|harmonyos)/i],[a,o],[/(android|webos|qnx|bada|rim tablet os|maemo|meego|sailfish|openharmony)[-\/ ]?([\w\.]*)/i,/(blackberry)\w*\/([\w\.]*)/i,/(tizen|kaios)[\/ ]([\w\.]+)/i,/\((series40);/i],[o,a],[/\(bb(10);/i],[a,[o,Ri]],[/(?:symbian ?os|symbos|s60(?=;)|series60)[-\/ ]?([\w\.]*)/i],[a,[o,"Symbian"]],[/mozilla\/[\d\.]+ \((?:mobile|tablet|tv|mobile; [\w ]+); rv:.+ gecko\/([\w\.]+)/i],[a,[o,Q+" OS"]],[/web0s;.+rt(tv)/i,/\b(?:hp)?wos(?:browser)?\/([\w\.]+)/i],[a,[o,"webOS"]],[/watch(?: ?os[,\/]|\d,\d\/)([\d\.]+)/i],[a,[o,"watchOS"]],[/(cros) [\w]+(?:\)| ([\w\.]+)\b)/i],[[o,"Chrome OS"],a],[/panasonic;(viera)/i,/(netrange)mmh/i,/(nettv)\/(\d+\.[\w\.]+)/i,/(nintendo|playstation) (\w+)/i,/(xbox); +xbox ([^\);]+)/i,/(pico) .+os([\w\.]+)/i,/\b(joli|palm)\b ?(?:os)?\/?([\w\.]*)/i,/(mint)[\/\(\) ]?(\w*)/i,/(mageia|vectorlinux)[; ]/i,/([kxln]?ubuntu|debian|suse|opensuse|gentoo|arch(?= linux)|slackware|fedora|mandriva|centos|pclinuxos|red ?hat|zenwalk|linpus|raspbian|plan 9|minix|risc os|contiki|deepin|manjaro|elementary os|sabayon|linspire)(?: gnu\/linux)?(?: enterprise)?(?:[- ]linux)?(?:-gnu)?[-\/ ]?(?!chrom|package)([-\w\.]*)/i,/(hurd|linux) ?([\w\.]*)/i,/(gnu) ?([\w\.]*)/i,/\b([-frentopcghs]{0,5}bsd|dragonfly)[\/ ]?(?!amd|[ix346]{1,2}86)([\w\.]*)/i,/(haiku) (\w+)/i],[o,a],[/(sunos) ?([\w\.\d]*)/i],[[o,"Solaris"],a],[/((?:open)?solaris)[-\/ ]?([\w\.]*)/i,/(aix) ((\d)(?=\.|\)| )[\w\.])*/i,/\b(beos|os\/2|amigaos|morphos|openvms|fuchsia|hp-ux|serenityos)/i,/(unix) ?([\w\.]*)/i],[o,a]]},ui=function(){var i={init:{},isIgnore:{},isIgnoreRgx:{},toString:{}};return A.call(i.init,[[_,[o,a,ai,e]],[E,[k]],[S,[e,r,c]],[C,[o,a]],[x,[o,a]]]),A.call(i.isIgnore,[[_,[a,ai]],[C,[a]],[x,[a]]]),A.call(i.isIgnoreRgx,[[_,/ ?browser$/i],[x,/ ?os$/i]]),A.call(i.toString,[[_,[o,a]],[E,[k]],[S,[c,r]],[C,[o,a]],[x,[o,a]]]),i}(),Ee=function(i,s){var d=ui.init[s],p=ui.isIgnore[s]||0,u=ui.isIgnoreRgx[s]||0,h=ui.toString[s]||0;function l(){A.call(this,d)}return l.prototype.getItem=function(){return i},l.prototype.withClientHints=function(){return D?D.getHighEntropyValues(te).then(function(f){return i.setCH(new oe(f,!1)).parseCH().get()}):i.parseCH().get()},l.prototype.withFeatureCheck=function(){return i.detectFeature().get()},s!=F&&(l.prototype.is=function(f){var b=!1;for(var v in this)if(this.hasOwnProperty(v)&&!Ai(p,v)&&T(u?P(u,this[v]):this[v])==T(u?P(u,f):f)){if(b=!0,f!=L)break}else if(f==L&&b){b=!b;break}return b},l.prototype.toString=function(){var f=j;for(var b in h)typeof this[h[b]]!==L&&(f+=(f?" ":j)+this[h[b]]);return f||L}),D||(l.prototype.then=function(f){var b=this,v=function(){for(var O in b)b.hasOwnProperty(O)&&(this[O]=b[O])};v.prototype={is:l.prototype.is,toString:l.prototype.toString};var I=new v;return f(I),I}),new l};function oe(i,s){if(i=i||{},A.call(this,te),s)A.call(this,[[Ii,xi(i[M])],[Oi,xi(i[fe])],[w,/\?1/.test(i[ge])],[r,ii(i[ke])],[B,ii(i[ee])],[qi,ii(i[ye])],[k,ii(i[he])],[H,xi(i[ve])],[pi,ii(i[me])]]);else for(var d in i)this.hasOwnProperty(d)&&typeof i[d]!==L&&(this[d]=i[d])}function Ji(i,s,d,p){return this.get=function(u){return u?this.data.hasOwnProperty(u)?this.data[u]:void 0:this.data},this.set=function(u,h){return this.data[u]=h,this},this.setCH=function(u){return this.uaCH=u,this},this.detectFeature=function(){if(y&&y.userAgent==this.ua)switch(this.itemType){case _:y.brave&&typeof y.brave.isBrave==wi&&this.set(o,"Brave");break;case S:!this.get(e)&&D&&D[w]&&this.set(e,w),this.get(r)=="Macintosh"&&y&&typeof y.standalone!==L&&y.maxTouchPoints&&y.maxTouchPoints>2&&this.set(r,"iPad").set(e,m);break;case x:!this.get(o)&&D&&D[B]&&this.set(o,D[B]);break;case F:var u=this.data,h=function(l){return u[l].getItem().detectFeature().get()};this.set(_,h(_)).set(E,h(E)).set(S,h(S)).set(C,h(C)).set(x,h(x))}return this},this.parseUA=function(){return this.itemType!=F&&Xi.call(this.data,this.ua,this.rgxMap),this.itemType==_&&this.set(ai,Si(this.get(a))),this},this.parseCH=function(){var u=this.uaCH,h=this.rgxMap;switch(this.itemType){case _:var l=u[Oi]||u[Ii],f;if(l)for(var b in l){var v=P(/(Google|Microsoft) /,l[b].brand||l[b]),I=l[b].version;!/not.a.brand/i.test(v)&&(!f||/chrom/i.test(f)&&!/chromi/i.test(v))&&(this.set(o,v).set(a,I).set(ai,Si(I)),f=v)}break;case E:var O=u[k];O&&(O&&u[pi]=="64"&&(O+="64"),Xi.call(this.data,O+";",h));break;case S:if(u[w]&&this.set(e,w),u[r]&&this.set(r,u[r]),u[r]=="Xbox"&&this.set(e,U).set(c,li),u[H]){var ni;if(typeof u[H]!="string")for(var Di=0;!ni&&Di<u[H].length;)ni=oi(u[H][Di++],Wi);else ni=oi(u[H],Wi);this.set(e,ni)}break;case x:var fi=u[B];if(fi){var hi=u[qi];fi==Ti&&(hi=parseInt(Si(hi),10)>=13?"11":"10"),this.set(o,fi).set(a,hi)}this.get(o)==Ti&&u[r]=="Xbox"&&this.set(o,"Xbox").set(a,void 0);break;case F:var ae=this.data,G=function(re){return ae[re].getItem().setCH(u).parseCH().get()};this.set(_,G(_)).set(E,G(E)).set(S,G(S)).set(C,G(C)).set(x,G(x))}return this},A.call(this,[["itemType",i],["ua",s],["uaCH",p],["rgxMap",d],["data",Ee(this,i)]]),this}function N(i,s,d){if(typeof i===V?(bi(i,!0)?(typeof s===V&&(d=s),s=i):(d=i,s=void 0),i=void 0):typeof i===Ni&&!bi(s,!0)&&(d=s,s=void 0),d&&typeof d.append===wi){var p={};d.forEach(function(b,v){p[v]=b}),d=p}if(!(this instanceof N))return new N(i,s,d).getResult();var u=typeof i===Ni?i:d&&d[Hi]?d[Hi]:y&&y.userAgent?y.userAgent:j,h=new oe(d,!0),l=s?Ne(Ki,s):Ki,f=function(b){return b==F?function(){return new Ji(b,u,l,h).set("ua",u).set(_,this.getBrowser()).set(E,this.getCPU()).set(S,this.getDevice()).set(C,this.getEngine()).set(x,this.getOS()).get()}:function(){return new Ji(b,u,l[b],h).parseUA().get()}};return A.call(this,[["getBrowser",f(_)],["getCPU",f(E)],["getDevice",f(S)],["getEngine",f(C)],["getOS",f(x)],["getResult",f(F)],["getUA",function(){return u}],["setUA",function(b){return Y(b)&&(u=b.length>Ci?ri(b,Ci):b),this}]]).setUA(u),this}N.VERSION=pe,N.BROWSER=di([o,a,ai,e]),N.CPU=di([k]),N.DEVICE=di([r,c,e,U,w,g,m,ti,Ei]),N.ENGINE=N.OS=di([o,a]);const ei={EMAIL_SEND_OFF:0,EMAIL_SEND_HOURLY:1,EMAIL_SEND_3HOURLY:2,EMAIL_SEND_DAILY:3,EMAIL_SEND_WEEKLY:4},Ce=[{text:t("notifications","Never"),value:ei.EMAIL_SEND_OFF},{text:t("notifications","1 hour"),value:ei.EMAIL_SEND_HOURLY},{text:t("notifications","3 hours"),value:ei.EMAIL_SEND_3HOURLY},{text:t("notifications","1 day"),value:ei.EMAIL_SEND_DAILY},{text:t("notifications","1 week"),value:ei.EMAIL_SEND_WEEKLY}],Qi={id:null,label:t("notifications","None")},Te=new N,Zi=Te.getBrowser(),ie=Zi.name==="Safari"||Zi.name==="Mobile Safari",Ae={name:"UserSettings",components:{NcCheckboxRadioSwitch:ce,NcSelect:we,NcSettingsSection:be},setup(){const i=Li(de("notifications","config")),s=Li({secondary_speaker:X.getItem("secondary_speaker")==="true",secondary_speaker_device:JSON.parse(X.getItem("secondary_speaker_device"))??Qi}),d=ne([]);return{BATCHTIME_OPTIONS:Ce,isSafari:ie,config:i,storage:s,devices:d}},methods:{async updateSettings(){try{const i=new FormData;i.append("batchSetting",this.config.setting_batchtime),i.append("soundNotification",this.config.sound_notification?"yes":"no"),i.append("soundTalk",this.config.sound_talk?"yes":"no"),await ue.post(le("apps/notifications/api/v2/settings"),i),Mi(t("notifications","Your settings have been updated."))}catch(i){vi(t("notifications","An error occurred while updating your settings.")),console.error(i)}},updateLocalSettings(){try{X.setItem("secondary_speaker",this.storage.secondary_speaker),this.storage.secondary_speaker&&this.storage.secondary_speaker_device.id?X.setItem("secondary_speaker_device",JSON.stringify(this.storage.secondary_speaker_device)):X.removeItem("secondary_speaker_device"),Mi(t("notifications","Your settings have been updated."))}catch(i){vi(t("notifications","An error occurred while updating your settings.")),console.error(i)}},async initializeDevices(){if(!(!ie&&navigator?.mediaDevices?.getUserMedia&&navigator?.mediaDevices?.enumerateDevices)||this.devices.length>0)return;let i=null;try{i=await navigator.mediaDevices.getUserMedia({audio:!0}),this.devices=(await navigator.mediaDevices.enumerateDevices()??[]).filter(s=>s.kind==="audiooutput").map(s=>({id:s.deviceId,label:s.label?s.label:s.fallbackLabel})).concat([Qi])}catch(s){vi(t("notifications","An error occurred while updating your settings.")),console.error("Error while requesting or initializing audio devices: ",s)}finally{i&&i.getTracks().forEach(s=>s.stop())}}}};var Ie=function(){var i=this,s=i._self._c;return s("NcSettingsSection",{attrs:{name:i.t("notifications","Notifications")}},[s("div",{staticClass:"notification-frequency__warning"},[i.config.is_email_set?i._e():s("strong",[i._v(i._s(i.t("notifications","You need to set up your email address before you can receive notification emails.")))])]),s("p",[s("label",{staticClass:"notification-frequency__label",attrs:{for:"notification_reminder_batchtime"}},[i._v(" "+i._s(i.t("notifications","Send email reminders about unhandled notifications after:"))+" ")]),s("select",{directives:[{name:"model",rawName:"v-model",value:i.config.setting_batchtime,expression:"config.setting_batchtime"}],staticClass:"notification-frequency__select",attrs:{id:"notification_reminder_batchtime",name:"notification_reminder_batchtime"},on:{change:[function(d){var p=Array.prototype.filter.call(d.target.options,function(u){return u.selected}).map(function(u){var h="_value"in u?u._value:u.value;return h});i.$set(i.config,"setting_batchtime",d.target.multiple?p:p[0])},function(d){return i.updateSettings()}]}},i._l(i.BATCHTIME_OPTIONS,function(d){return s("option",{key:d.value,domProps:{value:d.value}},[i._v(" "+i._s(d.text)+" ")])}),0)]),s("NcCheckboxRadioSwitch",{attrs:{checked:i.config.sound_notification},on:{"update:checked":[function(d){return i.$set(i.config,"sound_notification",d)},i.updateSettings]}},[i._v(" "+i._s(i.t("notifications","Play sound when a new notification arrives"))+" ")]),s("NcCheckboxRadioSwitch",{attrs:{checked:i.config.sound_talk},on:{"update:checked":[function(d){return i.$set(i.config,"sound_talk",d)},i.updateSettings]}},[i._v(" "+i._s(i.t("notifications","Play sound when a call started (requires Nextcloud Talk)"))+" ")]),i.config.sound_talk?[s("NcCheckboxRadioSwitch",{staticClass:"additional-margin-top",attrs:{checked:i.storage.secondary_speaker,disabled:i.isSafari},on:{"update:checked":[function(d){return i.$set(i.storage,"secondary_speaker",d)},i.updateLocalSettings]}},[i._v(" "+i._s(i.t("notifications","Also repeat sound on a secondary speaker"))+" ")]),i.isSafari?s("div",{staticClass:"notification-frequency__warning"},[s("strong",[i._v(i._s(i.t("notifications","Selection of the speaker device is currently not supported by Safari")))])]):i._e(),!i.isSafari&&i.storage.secondary_speaker?s("NcSelect",{attrs:{"input-id":"device-selector-audio-output",options:i.devices,label:"label","aria-label-combobox":i.t("notifications","Select a device"),clearable:!1,placeholder:i.t("notifications","Select a device")},on:{open:i.initializeDevices,input:i.updateLocalSettings},model:{value:i.storage.secondary_speaker_device,callback:function(d){i.$set(i.storage,"secondary_speaker_device",d)},expression:"storage.secondary_speaker_device"}}):i._e()]:i._e()],2)},Oe=[],qe=se(Ae,Ie,Oe,!1,null,"6f3d8087");const De=qe.exports;mi.prototype.t=t,mi.prototype.n=n,new mi({el:"#notifications-user-settings",render:i=>i(De)});
