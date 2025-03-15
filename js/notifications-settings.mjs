/*! third party licenses: js/vendor.LICENSE.txt */
import{r as Pi,a as le,V as gi}from"./style-N1aAjoYj.chunk.mjs";import{n as ue,N as pe,l as he,c as me,v as fe,s as Bi,a as ki}from"./_plugin-vue2_normalizer-CfUu9DuP.chunk.mjs";import{N as ve,B as J}from"./BrowserStorage-DrvUR9Yt.chunk.mjs";import{N as ge}from"./NcSettingsSection-DHX2Y1Ed-DJtDp3gD.chunk.mjs";var ke="2.0.3",Oi=500,Fi="user-agent",j="",Vi="?",pi="function",H="undefined",Y="object",qi="string",k="browser",A="cpu",C="device",S="engine",x="os",F="result",a="name",e="type",r="vendor",s="version",y="architecture",ni="major",o="model",ai="console",l="mobile",f="tablet",g="smarttv",N="wearable",yi="xr",si="embedded",X="inapp",Hi="brands",U="formFactors",zi="fullVersionList",V="platform",Ri="platformVersion",mi="bitness",z="sec-ch-ua",ye=z+"-full-version-list",_e=z+"-arch",xe=z+"-"+mi,Se=z+"-form-factors",Ne=z+"-"+l,Ce=z+"-"+o,ne=z+"-"+V,Ee=ne+"-version",ce=[Hi,zi,l,o,V,Ri,y,U,mi],wi="Amazon",P="Apple",ji="ASUS",Yi="BlackBerry",R="Google",$i="Huawei",Wi="Lenovo",Gi="Honor",bi="LG",_i="Microsoft",xi="Motorola",Si="Nvidia",Ki="OnePlus",Ni="OPPO",Q="Samsung",Ji="Sharp",Z="Sony",Ci="Xiaomi",Ei="Zebra",Xi="Chrome",Qi="Chromium",q="Chromecast",Ie="Edge",ii="Firefox",ei="Opera",Ii="Facebook",Zi="Sogou",B="Mobile ",ti=" Browser",Li="Windows",Ae=typeof window!==H,_=Ae&&window.navigator?window.navigator:void 0,L=_&&_.userAgentData?_.userAgentData:void 0,Te=function(i,c){var d={},p=c;if(!hi(c)){p={};for(var w in c)for(var h in c[w])p[h]=c[w][h].concat(p[h]?p[h]:[])}for(var b in i)d[b]=p[b]&&p[b].length%2===0?p[b].concat(i[b]):i[b];return d},li=function(i){for(var c={},d=0;d<i.length;d++)c[i[d].toUpperCase()]=i[d];return c},Di=function(i,c){if(typeof i===Y&&i.length>0){for(var d in i)if(T(i[d])==T(c))return!0;return!1}return W(i)?T(c).indexOf(T(i))!==-1:!1},hi=function(i,c){for(var d in i)return/^(browser|cpu|device|engine|os)$/.test(d)||(c?hi(i[d]):!1)},W=function(i){return typeof i===qi},Ai=function(i){if(i){for(var c=[],d=$(/\\?\"/g,i).split(","),p=0;p<d.length;p++)if(d[p].indexOf(";")>-1){var w=ci(d[p]).split(";v=");c[p]={brand:w[0],version:w[1]}}else c[p]=ci(d[p]);return c}},T=function(i){return W(i)?i.toLowerCase():i},Ti=function(i){return W(i)?$(/[^\d\.]/g,i).split(".")[0]:void 0},M=function(i){for(var c in i){var d=i[c];typeof d==Y&&d.length==2?this[d[0]]=d[1]:this[d]=void 0}return this},$=function(i,c){return W(c)?c.replace(i,j):c},oi=function(i){return $(/\\?\"/g,i)},ci=function(i,c){if(W(i))return i=$(/^\s\s*/,i),typeof c===H?i:i.substring(0,Oi)},Mi=function(i,c){if(!(!i||!c))for(var d=0,p,w,h,b,m,u;d<c.length&&!m;){var v=c[d],E=c[d+1];for(p=w=0;p<v.length&&!m&&v[p];)if(m=v[p++].exec(i),m)for(h=0;h<E.length;h++)u=m[++w],b=E[h],typeof b===Y&&b.length>0?b.length===2?typeof b[1]==pi?this[b[0]]=b[1].call(this,u):this[b[0]]=b[1]:b.length===3?typeof b[1]===pi&&!(b[1].exec&&b[1].test)?this[b[0]]=u?b[1].call(this,u,b[2]):void 0:this[b[0]]=u?u.replace(b[1],b[2]):void 0:b.length===4&&(this[b[0]]=u?b[3].call(this,u.replace(b[1],b[2])):void 0):this[b]=u||void 0;d+=2}},D=function(i,c){for(var d in c)if(typeof c[d]===Y&&c[d].length>0){for(var p=0;p<c[d].length;p++)if(Di(c[d][p],i))return d===Vi?void 0:d}else if(Di(c[d],i))return d===Vi?void 0:d;return c.hasOwnProperty("*")?c["*"]:i},ie={ME:"4.90","NT 3.11":"NT3.51","NT 4.0":"NT4.0",2e3:"NT 5.0",XP:["NT 5.1","NT 5.2"],Vista:"NT 6.0",7:"NT 6.1",8:"NT 6.2","8.1":"NT 6.3",10:["NT 6.4","NT 10.0"],RT:"ARM"},ee={embedded:"Automotive",mobile:"Mobile",tablet:["Tablet","EInk"],smarttv:"TV",wearable:"Watch",xr:["VR","XR"],"?":["Desktop","Unknown"],"*":void 0},te={browser:[[/\b(?:crmo|crios)\/([\w\.]+)/i],[s,[a,B+"Chrome"]],[/edg(?:e|ios|a)?\/([\w\.]+)/i],[s,[a,"Edge"]],[/(opera mini)\/([-\w\.]+)/i,/(opera [mobiletab]{3,6})\b.+version\/([-\w\.]+)/i,/(opera)(?:.+version\/|[\/ ]+)([\w\.]+)/i],[a,s],[/opios[\/ ]+([\w\.]+)/i],[s,[a,ei+" Mini"]],[/\bop(?:rg)?x\/([\w\.]+)/i],[s,[a,ei+" GX"]],[/\bopr\/([\w\.]+)/i],[s,[a,ei]],[/\bb[ai]*d(?:uhd|[ub]*[aekoprswx]{5,6})[\/ ]?([\w\.]+)/i],[s,[a,"Baidu"]],[/\b(?:mxbrowser|mxios|myie2)\/?([-\w\.]*)\b/i],[s,[a,"Maxthon"]],[/(kindle)\/([\w\.]+)/i,/(lunascape|maxthon|netfront|jasmine|blazer|sleipnir)[\/ ]?([\w\.]*)/i,/(avant|iemobile|slim(?:browser|boat|jet))[\/ ]?([\d\.]*)/i,/(?:ms|\()(ie) ([\w\.]+)/i,/(flock|rockmelt|midori|epiphany|silk|skyfire|ovibrowser|bolt|iron|vivaldi|iridium|phantomjs|bowser|qupzilla|falkon|rekonq|puffin|brave|whale(?!.+naver)|qqbrowserlite|duckduckgo|klar|helio|(?=comodo_)?dragon|otter|dooble|(?:lg |qute)browser)\/([-\w\.]+)/i,/(heytap|ovi|115|surf)browser\/([\d\.]+)/i,/(ecosia|weibo)(?:__| \w+@)([\d\.]+)/i],[a,s],[/quark(?:pc)?\/([-\w\.]+)/i],[s,[a,"Quark"]],[/\bddg\/([\w\.]+)/i],[s,[a,"DuckDuckGo"]],[/(?:\buc? ?browser|(?:juc.+)ucweb)[\/ ]?([\w\.]+)/i],[s,[a,"UCBrowser"]],[/microm.+\bqbcore\/([\w\.]+)/i,/\bqbcore\/([\w\.]+).+microm/i,/micromessenger\/([\w\.]+)/i],[s,[a,"WeChat"]],[/konqueror\/([\w\.]+)/i],[s,[a,"Konqueror"]],[/trident.+rv[: ]([\w\.]{1,9})\b.+like gecko/i],[s,[a,"IE"]],[/ya(?:search)?browser\/([\w\.]+)/i],[s,[a,"Yandex"]],[/slbrowser\/([\w\.]+)/i],[s,[a,"Smart "+Wi+ti]],[/(avast|avg)\/([\w\.]+)/i],[[a,/(.+)/,"$1 Secure"+ti],s],[/\bfocus\/([\w\.]+)/i],[s,[a,ii+" Focus"]],[/\bopt\/([\w\.]+)/i],[s,[a,ei+" Touch"]],[/coc_coc\w+\/([\w\.]+)/i],[s,[a,"Coc Coc"]],[/dolfin\/([\w\.]+)/i],[s,[a,"Dolphin"]],[/coast\/([\w\.]+)/i],[s,[a,ei+" Coast"]],[/miuibrowser\/([\w\.]+)/i],[s,[a,"MIUI"+ti]],[/fxios\/([\w\.-]+)/i],[s,[a,B+ii]],[/\bqihoobrowser\/?([\w\.]*)/i],[s,[a,"360"]],[/\b(qq)\/([\w\.]+)/i],[[a,/(.+)/,"$1Browser"],s],[/(oculus|sailfish|huawei|vivo|pico)browser\/([\w\.]+)/i],[[a,/(.+)/,"$1"+ti],s],[/samsungbrowser\/([\w\.]+)/i],[s,[a,Q+" Internet"]],[/metasr[\/ ]?([\d\.]+)/i],[s,[a,Zi+" Explorer"]],[/(sogou)mo\w+\/([\d\.]+)/i],[[a,Zi+" Mobile"],s],[/(electron)\/([\w\.]+) safari/i,/(tesla)(?: qtcarbrowser|\/(20\d\d\.[-\w\.]+))/i,/m?(qqbrowser|2345(?=browser|chrome|explorer))\w*[\/ ]?v?([\w\.]+)/i],[a,s],[/(lbbrowser|rekonq)/i],[a],[/ome\/([\w\.]+) \w* ?(iron) saf/i,/ome\/([\w\.]+).+qihu (360)[es]e/i],[s,a],[/((?:fban\/fbios|fb_iab\/fb4a)(?!.+fbav)|;fbav\/([\w\.]+);)/i],[[a,Ii],s,[e,X]],[/(Klarna)\/([\w\.]+)/i,/(kakao(?:talk|story))[\/ ]([\w\.]+)/i,/(naver)\(.*?(\d+\.[\w\.]+).*\)/i,/(daum)apps[\/ ]([\w\.]+)/i,/safari (line)\/([\w\.]+)/i,/\b(line)\/([\w\.]+)\/iab/i,/(alipay)client\/([\w\.]+)/i,/(twitter)(?:and| f.+e\/([\w\.]+))/i,/(instagram|snapchat)[\/ ]([-\w\.]+)/i],[a,s,[e,X]],[/\bgsa\/([\w\.]+) .*safari\//i],[s,[a,"GSA"],[e,X]],[/musical_ly(?:.+app_?version\/|_)([\w\.]+)/i],[s,[a,"TikTok"],[e,X]],[/\[(linkedin)app\]/i],[a,[e,X]],[/(chromium)[\/ ]([-\w\.]+)/i],[a,s],[/headlesschrome(?:\/([\w\.]+)| )/i],[s,[a,Xi+" Headless"]],[/ wv\).+(chrome)\/([\w\.]+)/i],[[a,Xi+" WebView"],s],[/droid.+ version\/([\w\.]+)\b.+(?:mobile safari|safari)/i],[s,[a,"Android"+ti]],[/chrome\/([\w\.]+) mobile/i],[s,[a,B+"Chrome"]],[/(chrome|omniweb|arora|[tizenoka]{5} ?browser)\/v?([\w\.]+)/i],[a,s],[/version\/([\w\.\,]+) .*mobile(?:\/\w+ | ?)safari/i],[s,[a,B+"Safari"]],[/iphone .*mobile(?:\/\w+ | ?)safari/i],[[a,B+"Safari"]],[/version\/([\w\.\,]+) .*(safari)/i],[s,a],[/webkit.+?(mobile ?safari|safari)(\/[\w\.]+)/i],[a,[s,"1"]],[/(webkit|khtml)\/([\w\.]+)/i],[a,s],[/(?:mobile|tablet);.*(firefox)\/([\w\.-]+)/i],[[a,B+ii],s],[/(navigator|netscape\d?)\/([-\w\.]+)/i],[[a,"Netscape"],s],[/(wolvic|librewolf)\/([\w\.]+)/i],[a,s],[/mobile vr; rv:([\w\.]+)\).+firefox/i],[s,[a,ii+" Reality"]],[/ekiohf.+(flow)\/([\w\.]+)/i,/(swiftfox)/i,/(icedragon|iceweasel|camino|chimera|fennec|maemo browser|minimo|conkeror)[\/ ]?([\w\.\+]+)/i,/(seamonkey|k-meleon|icecat|iceape|firebird|phoenix|palemoon|basilisk|waterfox)\/([-\w\.]+)$/i,/(firefox)\/([\w\.]+)/i,/(mozilla)\/([\w\.]+) .+rv\:.+gecko\/\d+/i,/(amaya|dillo|doris|icab|ladybird|lynx|mosaic|netsurf|obigo|polaris|w3m|(?:go|ice|up)[\. ]?browser)[-\/ ]?v?([\w\.]+)/i,/\b(links) \(([\w\.]+)/i],[a,[s,/_/g,"."]],[/(cobalt)\/([\w\.]+)/i],[a,[s,/[^\d\.]+./,j]]],cpu:[[/\b((amd|x|x86[-_]?|wow|win)64)\b/i],[[y,"amd64"]],[/(ia32(?=;))/i,/\b((i[346]|x)86)(pc)?\b/i],[[y,"ia32"]],[/\b(aarch64|arm(v?[89]e?l?|_?64))\b/i],[[y,"arm64"]],[/\b(arm(v[67])?ht?n?[fl]p?)\b/i],[[y,"armhf"]],[/( (ce|mobile); ppc;|\/[\w\.]+arm\b)/i],[[y,"arm"]],[/((ppc|powerpc)(64)?)( mac|;|\))/i],[[y,/ower/,j,T]],[/ sun4\w[;\)]/i],[[y,"sparc"]],[/\b(avr32|ia64(?=;)|68k(?=\))|\barm(?=v([1-7]|[5-7]1)l?|;|eabi)|(irix|mips|sparc)(64)?\b|pa-risc)/i],[[y,T]]],device:[[/\b(sch-i[89]0\d|shw-m380s|sm-[ptx]\w{2,4}|gt-[pn]\d{2,4}|sgh-t8[56]9|nexus 10)/i],[o,[r,Q],[e,f]],[/\b((?:s[cgp]h|gt|sm)-(?![lr])\w+|sc[g-]?[\d]+a?|galaxy nexus)/i,/samsung[- ]((?!sm-[lr])[-\w]+)/i,/sec-(sgh\w+)/i],[o,[r,Q],[e,l]],[/(?:\/|\()(ip(?:hone|od)[\w, ]*)(?:\/|;)/i],[o,[r,P],[e,l]],[/\((ipad);[-\w\),; ]+apple/i,/applecoremedia\/[\w\.]+ \((ipad)/i,/\b(ipad)\d\d?,\d\d?[;\]].+ios/i],[o,[r,P],[e,f]],[/(macintosh);/i],[o,[r,P]],[/\b(sh-?[altvz]?\d\d[a-ekm]?)/i],[o,[r,Ji],[e,l]],[/\b((?:brt|eln|hey2?|gdi|jdn)-a?[lnw]09|(?:ag[rm]3?|jdn2|kob2)-a?[lw]0[09]hn)(?: bui|\)|;)/i],[o,[r,Gi],[e,f]],[/honor([-\w ]+)[;\)]/i],[o,[r,Gi],[e,l]],[/\b((?:ag[rs][2356]?k?|bah[234]?|bg[2o]|bt[kv]|cmr|cpn|db[ry]2?|jdn2|got|kob2?k?|mon|pce|scm|sht?|[tw]gr|vrd)-[ad]?[lw][0125][09]b?|605hw|bg2-u03|(?:gem|fdr|m2|ple|t1)-[7a]0[1-4][lu]|t1-a2[13][lw]|mediapad[\w\. ]*(?= bui|\)))\b(?!.+d\/s)/i],[o,[r,$i],[e,f]],[/(?:huawei)([-\w ]+)[;\)]/i,/\b(nexus 6p|\w{2,4}e?-[atu]?[ln][\dx][012359c][adn]?)\b(?!.+d\/s)/i],[o,[r,$i],[e,l]],[/oid[^\)]+; (2[\dbc]{4}(182|283|rp\w{2})[cgl]|m2105k81a?c)(?: bui|\))/i,/\b((?:red)?mi[-_ ]?pad[\w- ]*)(?: bui|\))/i],[[o,/_/g," "],[r,Ci],[e,f]],[/\b(poco[\w ]+|m2\d{3}j\d\d[a-z]{2})(?: bui|\))/i,/\b; (\w+) build\/hm\1/i,/\b(hm[-_ ]?note?[_ ]?(?:\d\w)?) bui/i,/\b(redmi[\-_ ]?(?:note|k)?[\w_ ]+)(?: bui|\))/i,/oid[^\)]+; (m?[12][0-389][01]\w{3,6}[c-y])( bui|; wv|\))/i,/\b(mi[-_ ]?(?:a\d|one|one[_ ]plus|note lte|max|cc)?[_ ]?(?:\d?\w?)[_ ]?(?:plus|se|lite|pro)?)(?: bui|\))/i,/ ([\w ]+) miui\/v?\d/i],[[o,/_/g," "],[r,Ci],[e,l]],[/; (\w+) bui.+ oppo/i,/\b(cph[12]\d{3}|p(?:af|c[al]|d\w|e[ar])[mt]\d0|x9007|a101op)\b/i],[o,[r,Ni],[e,l]],[/\b(opd2(\d{3}a?))(?: bui|\))/i],[o,[r,D,{OnePlus:["304","403","203"],"*":Ni}],[e,f]],[/(vivo (5r?|6|8l?|go|one|s|x[il]?[2-4]?)[\w\+ ]*)(?: bui|\))/i],[o,[r,"BLU"],[e,l]],[/; vivo (\w+)(?: bui|\))/i,/\b(v[12]\d{3}\w?[at])(?: bui|;)/i],[o,[r,"Vivo"],[e,l]],[/\b(rmx[1-3]\d{3})(?: bui|;|\))/i],[o,[r,"Realme"],[e,l]],[/\b(milestone|droid(?:[2-4x]| (?:bionic|x2|pro|razr))?:?( 4g)?)\b[\w ]+build\//i,/\bmot(?:orola)?[- ](\w*)/i,/((?:moto(?! 360)[\w\(\) ]+|xt\d{3,4}|nexus 6)(?= bui|\)))/i],[o,[r,xi],[e,l]],[/\b(mz60\d|xoom[2 ]{0,2}) build\//i],[o,[r,xi],[e,f]],[/((?=lg)?[vl]k\-?\d{3}) bui| 3\.[-\w; ]{10}lg?-([06cv9]{3,4})/i],[o,[r,bi],[e,f]],[/(lm(?:-?f100[nv]?|-[\w\.]+)(?= bui|\))|nexus [45])/i,/\blg[-e;\/ ]+(?!.*(?:browser|netcast|android tv|watch))(\w+)/i,/\blg-?([\d\w]+) bui/i],[o,[r,bi],[e,l]],[/(ideatab[-\w ]+|602lv|d-42a|a101lv|a2109a|a3500-hv|s[56]000|pb-6505[my]|tb-?x?\d{3,4}(?:f[cu]|xu|[av])|yt\d?-[jx]?\d+[lfmx])( bui|;|\)|\/)/i,/lenovo ?(b[68]0[08]0-?[hf]?|tab(?:[\w- ]+?)|tb[\w-]{6,7})( bui|;|\)|\/)/i],[o,[r,Wi],[e,f]],[/(nokia) (t[12][01])/i],[r,o,[e,f]],[/(?:maemo|nokia).*(n900|lumia \d+|rm-\d+)/i,/nokia[-_ ]?(([-\w\. ]*))/i],[[o,/_/g," "],[e,l],[r,"Nokia"]],[/(pixel (c|tablet))\b/i],[o,[r,R],[e,f]],[/droid.+; (pixel[\daxl ]{0,6})(?: bui|\))/i],[o,[r,R],[e,l]],[/droid.+; (a?\d[0-2]{2}so|[c-g]\d{4}|so[-gl]\w+|xq-a\w[4-7][12])(?= bui|\).+chrome\/(?![1-6]{0,1}\d\.))/i],[o,[r,Z],[e,l]],[/sony tablet [ps]/i,/\b(?:sony)?sgp\w+(?: bui|\))/i],[[o,"Xperia Tablet"],[r,Z],[e,f]],[/ (kb2005|in20[12]5|be20[12][59])\b/i,/(?:one)?(?:plus)? (a\d0\d\d)(?: b|\))/i],[o,[r,Ki],[e,l]],[/(alexa)webm/i,/(kf[a-z]{2}wi|aeo(?!bc)\w\w)( bui|\))/i,/(kf[a-z]+)( bui|\)).+silk\//i],[o,[r,wi],[e,f]],[/((?:sd|kf)[0349hijorstuw]+)( bui|\)).+silk\//i],[[o,/(.+)/g,"Fire Phone $1"],[r,wi],[e,l]],[/(playbook);[-\w\),; ]+(rim)/i],[o,r,[e,f]],[/\b((?:bb[a-f]|st[hv])100-\d)/i,/\(bb10; (\w+)/i],[o,[r,Yi],[e,l]],[/(?:\b|asus_)(transfo[prime ]{4,10} \w+|eeepc|slider \w+|nexus 7|padfone|p00[cj])/i],[o,[r,ji],[e,f]],[/ (z[bes]6[027][012][km][ls]|zenfone \d\w?)\b/i],[o,[r,ji],[e,l]],[/(nexus 9)/i],[o,[r,"HTC"],[e,f]],[/(htc)[-;_ ]{1,2}([\w ]+(?=\)| bui)|\w+)/i,/(zte)[- ]([\w ]+?)(?: bui|\/|\))/i,/(alcatel|geeksphone|nexian|panasonic(?!(?:;|\.))|sony(?!-bra))[-_ ]?([-\w]*)/i],[r,[o,/_/g," "],[e,l]],[/tcl (xess p17aa)/i,/droid [\w\.]+; ((?:8[14]9[16]|9(?:0(?:48|60|8[01])|1(?:3[27]|66)|2(?:6[69]|9[56])|466))[gqswx])(_\w(\w|\w\w))?(\)| bui)/i],[o,[r,"TCL"],[e,f]],[/droid [\w\.]+; (418(?:7d|8v)|5087z|5102l|61(?:02[dh]|25[adfh]|27[ai]|56[dh]|59k|65[ah])|a509dl|t(?:43(?:0w|1[adepqu])|50(?:6d|7[adju])|6(?:09dl|10k|12b|71[efho]|76[hjk])|7(?:66[ahju]|67[hw]|7[045][bh]|71[hk]|73o|76[ho]|79w|81[hks]?|82h|90[bhsy]|99b)|810[hs]))(_\w(\w|\w\w))?(\)| bui)/i],[o,[r,"TCL"],[e,l]],[/(itel) ((\w+))/i],[[r,T],o,[e,D,{tablet:["p10001l","w7001"],"*":"mobile"}]],[/droid.+; ([ab][1-7]-?[0178a]\d\d?)/i],[o,[r,"Acer"],[e,f]],[/droid.+; (m[1-5] note) bui/i,/\bmz-([-\w]{2,})/i],[o,[r,"Meizu"],[e,l]],[/; ((?:power )?armor(?:[\w ]{0,8}))(?: bui|\))/i],[o,[r,"Ulefone"],[e,l]],[/; (energy ?\w+)(?: bui|\))/i,/; energizer ([\w ]+)(?: bui|\))/i],[o,[r,"Energizer"],[e,l]],[/; cat (b35);/i,/; (b15q?|s22 flip|s48c|s62 pro)(?: bui|\))/i],[o,[r,"Cat"],[e,l]],[/((?:new )?andromax[\w- ]+)(?: bui|\))/i],[o,[r,"Smartfren"],[e,l]],[/droid.+; (a(?:015|06[35]|142p?))/i],[o,[r,"Nothing"],[e,l]],[/; (x67 5g|tikeasy \w+|ac[1789]\d\w+)( b|\))/i,/archos ?(5|gamepad2?|([\w ]*[t1789]|hello) ?\d+[\w ]*)( b|\))/i],[o,[r,"Archos"],[e,f]],[/archos ([\w ]+)( b|\))/i,/; (ac[3-6]\d\w{2,8})( b|\))/i],[o,[r,"Archos"],[e,l]],[/(imo) (tab \w+)/i,/(infinix) (x1101b?)/i],[r,o,[e,f]],[/(blackberry|benq|palm(?=\-)|sonyericsson|acer|asus(?! zenw)|dell|jolla|meizu|motorola|polytron|infinix|tecno|micromax|advan)[-_ ]?([-\w]*)/i,/; (blu|hmd|imo|tcl)[_ ]([\w\+ ]+?)(?: bui|\)|; r)/i,/(hp) ([\w ]+\w)/i,/(microsoft); (lumia[\w ]+)/i,/(lenovo)[-_ ]?([-\w ]+?)(?: bui|\)|\/)/i,/(oppo) ?([\w ]+) bui/i],[r,o,[e,l]],[/(kobo)\s(ereader|touch)/i,/(hp).+(touchpad(?!.+tablet)|tablet)/i,/(kindle)\/([\w\.]+)/i],[r,o,[e,f]],[/(surface duo)/i],[o,[r,_i],[e,f]],[/droid [\d\.]+; (fp\du?)(?: b|\))/i],[o,[r,"Fairphone"],[e,l]],[/((?:tegranote|shield t(?!.+d tv))[\w- ]*?)(?: b|\))/i],[o,[r,Si],[e,f]],[/(sprint) (\w+)/i],[r,o,[e,l]],[/(kin\.[onetw]{3})/i],[[o,/\./g," "],[r,_i],[e,l]],[/droid.+; ([c6]+|et5[16]|mc[239][23]x?|vc8[03]x?)\)/i],[o,[r,Ei],[e,f]],[/droid.+; (ec30|ps20|tc[2-8]\d[kx])\)/i],[o,[r,Ei],[e,l]],[/smart-tv.+(samsung)/i],[r,[e,g]],[/hbbtv.+maple;(\d+)/i],[[o,/^/,"SmartTV"],[r,Q],[e,g]],[/tcast.+(lg)e?. ([-\w]+)/i],[r,o,[e,g]],[/(nux; netcast.+smarttv|lg (netcast\.tv-201\d|android tv))/i],[[r,bi],[e,g]],[/(apple) ?tv/i],[r,[o,P+" TV"],[e,g]],[/crkey.*devicetype\/chromecast/i],[[o,q+" Third Generation"],[r,R],[e,g]],[/crkey.*devicetype\/([^/]*)/i],[[o,/^/,"Chromecast "],[r,R],[e,g]],[/fuchsia.*crkey/i],[[o,q+" Nest Hub"],[r,R],[e,g]],[/crkey/i],[[o,q],[r,R],[e,g]],[/(portaltv)/i],[o,[r,Ii],[e,g]],[/droid.+aft(\w+)( bui|\))/i],[o,[r,wi],[e,g]],[/(shield \w+ tv)/i],[o,[r,Si],[e,g]],[/\(dtv[\);].+(aquos)/i,/(aquos-tv[\w ]+)\)/i],[o,[r,Ji],[e,g]],[/(bravia[\w ]+)( bui|\))/i],[o,[r,Z],[e,g]],[/(mi(tv|box)-?\w+) bui/i],[o,[r,Ci],[e,g]],[/Hbbtv.*(technisat) (.*);/i],[r,o,[e,g]],[/\b(roku)[\dx]*[\)\/]((?:dvp-)?[\d\.]*)/i,/hbbtv\/\d+\.\d+\.\d+ +\([\w\+ ]*; *([\w\d][^;]*);([^;]*)/i],[[r,ci],[o,ci],[e,g]],[/droid.+; ([\w- ]+) (?:android tv|smart[- ]?tv)/i],[o,[e,g]],[/\b(android tv|smart[- ]?tv|opera tv|tv; rv:)\b/i],[[e,g]],[/(ouya)/i,/(nintendo) (\w+)/i],[r,o,[e,ai]],[/droid.+; (shield)( bui|\))/i],[o,[r,Si],[e,ai]],[/(playstation \w+)/i],[o,[r,Z],[e,ai]],[/\b(xbox(?: one)?(?!; xbox))[\); ]/i],[o,[r,_i],[e,ai]],[/\b(sm-[lr]\d\d[0156][fnuw]?s?|gear live)\b/i],[o,[r,Q],[e,N]],[/((pebble))app/i,/(asus|google|lg|oppo) ((pixel |zen)?watch[\w ]*)( bui|\))/i],[r,o,[e,N]],[/(ow(?:19|20)?we?[1-3]{1,3})/i],[o,[r,Ni],[e,N]],[/(watch)(?: ?os[,\/]|\d,\d\/)[\d\.]+/i],[o,[r,P],[e,N]],[/(opwwe\d{3})/i],[o,[r,Ki],[e,N]],[/(moto 360)/i],[o,[r,xi],[e,N]],[/(smartwatch 3)/i],[o,[r,Z],[e,N]],[/(g watch r)/i],[o,[r,bi],[e,N]],[/droid.+; (wt63?0{2,3})\)/i],[o,[r,Ei],[e,N]],[/droid.+; (glass) \d/i],[o,[r,R],[e,yi]],[/(pico) (4|neo3(?: link|pro)?)/i],[r,o,[e,yi]],[/(quest( \d| pro)?s?).+vr/i],[o,[r,Ii],[e,yi]],[/(tesla)(?: qtcarbrowser|\/[-\w\.]+)/i],[r,[e,si]],[/(aeobc)\b/i],[o,[r,wi],[e,si]],[/(homepod).+mac os/i],[o,[r,P],[e,si]],[/windows iot/i],[[e,si]],[/droid .+?; ([^;]+?)(?: bui|; wv\)|\) applew).+?(mobile|vr|\d) safari/i],[o,[e,D,{mobile:"Mobile",xr:"VR","*":f}]],[/\b((tablet|tab)[;\/]|focus\/\d(?!.+mobile))/i],[[e,f]],[/(phone|mobile(?:[;\/]| [ \w\/\.]*safari)|pda(?=.+windows ce))/i],[[e,l]],[/droid .+?; ([\w\. -]+)( bui|\))/i],[o,[r,"Generic"]]],engine:[[/windows.+ edge\/([\w\.]+)/i],[s,[a,Ie+"HTML"]],[/(arkweb)\/([\w\.]+)/i],[a,s],[/webkit\/537\.36.+chrome\/(?!27)([\w\.]+)/i],[s,[a,"Blink"]],[/(presto)\/([\w\.]+)/i,/(webkit|trident|netfront|netsurf|amaya|lynx|w3m|goanna|servo)\/([\w\.]+)/i,/ekioh(flow)\/([\w\.]+)/i,/(khtml|tasman|links)[\/ ]\(?([\w\.]+)/i,/(icab)[\/ ]([23]\.[\d\.]+)/i,/\b(libweb)/i],[a,s],[/ladybird\//i],[[a,"LibWeb"]],[/rv\:([\w\.]{1,9})\b.+(gecko)/i],[s,a]],os:[[/microsoft (windows) (vista|xp)/i],[a,s],[/(windows (?:phone(?: os)?|mobile|iot))[\/ ]?([\d\.\w ]*)/i],[a,[s,D,ie]],[/windows nt 6\.2; (arm)/i,/windows[\/ ]([ntce\d\. ]+\w)(?!.+xbox)/i,/(?:win(?=3|9|n)|win 9x )([nt\d\.]+)/i],[[s,D,ie],[a,Li]],[/[adehimnop]{4,7}\b(?:.*os ([\w]+) like mac|; opera)/i,/(?:ios;fbsv\/|iphone.+ios[\/ ])([\d\.]+)/i,/cfnetwork\/.+darwin/i],[[s,/_/g,"."],[a,"iOS"]],[/(mac os x) ?([\w\. ]*)/i,/(macintosh|mac_powerpc\b)(?!.+haiku)/i],[[a,"macOS"],[s,/_/g,"."]],[/android ([\d\.]+).*crkey/i],[s,[a,q+" Android"]],[/fuchsia.*crkey\/([\d\.]+)/i],[s,[a,q+" Fuchsia"]],[/crkey\/([\d\.]+).*devicetype\/smartspeaker/i],[s,[a,q+" SmartSpeaker"]],[/linux.*crkey\/([\d\.]+)/i],[s,[a,q+" Linux"]],[/crkey\/([\d\.]+)/i],[s,[a,q]],[/droid ([\w\.]+)\b.+(android[- ]x86|harmonyos)/i],[s,a],[/(ubuntu) ([\w\.]+) like android/i],[[a,/(.+)/,"$1 Touch"],s],[/(android|bada|blackberry|kaios|maemo|meego|openharmony|qnx|rim tablet os|sailfish|series40|symbian|tizen|webos)\w*[-\/\.; ]?([\d\.]*)/i],[a,s],[/\(bb(10);/i],[s,[a,Yi]],[/(?:symbian ?os|symbos|s60(?=;)|series ?60)[-\/ ]?([\w\.]*)/i],[s,[a,"Symbian"]],[/mozilla\/[\d\.]+ \((?:mobile|tablet|tv|mobile; [\w ]+); rv:.+ gecko\/([\w\.]+)/i],[s,[a,ii+" OS"]],[/web0s;.+rt(tv)/i,/\b(?:hp)?wos(?:browser)?\/([\w\.]+)/i],[s,[a,"webOS"]],[/watch(?: ?os[,\/]|\d,\d\/)([\d\.]+)/i],[s,[a,"watchOS"]],[/(cros) [\w]+(?:\)| ([\w\.]+)\b)/i],[[a,"Chrome OS"],s],[/panasonic;(viera)/i,/(netrange)mmh/i,/(nettv)\/(\d+\.[\w\.]+)/i,/(nintendo|playstation) (\w+)/i,/(xbox); +xbox ([^\);]+)/i,/(pico) .+os([\w\.]+)/i,/\b(joli|palm)\b ?(?:os)?\/?([\w\.]*)/i,/(mint)[\/\(\) ]?(\w*)/i,/(mageia|vectorlinux)[; ]/i,/([kxln]?ubuntu|debian|suse|opensuse|gentoo|arch(?= linux)|slackware|fedora|mandriva|centos|pclinuxos|red ?hat|zenwalk|linpus|raspbian|plan 9|minix|risc os|contiki|deepin|manjaro|elementary os|sabayon|linspire)(?: gnu\/linux)?(?: enterprise)?(?:[- ]linux)?(?:-gnu)?[-\/ ]?(?!chrom|package)([-\w\.]*)/i,/(hurd|linux)(?: arm\w*| x86\w*| ?)([\w\.]*)/i,/(gnu) ?([\w\.]*)/i,/\b([-frentopcghs]{0,5}bsd|dragonfly)[\/ ]?(?!amd|[ix346]{1,2}86)([\w\.]*)/i,/(haiku) (\w+)/i],[a,s],[/(sunos) ?([\w\.\d]*)/i],[[a,"Solaris"],s],[/((?:open)?solaris)[-\/ ]?([\w\.]*)/i,/(aix) ((\d)(?=\.|\)| )[\w\.])*/i,/\b(beos|os\/2|amigaos|morphos|openvms|fuchsia|hp-ux|serenityos)/i,/(unix) ?([\w\.]*)/i],[a,s]]},ui=function(){var i={init:{},isIgnore:{},isIgnoreRgx:{},toString:{}};return M.call(i.init,[[k,[a,s,ni,e]],[A,[y]],[C,[e,o,r]],[S,[a,s]],[x,[a,s]]]),M.call(i.isIgnore,[[k,[s,ni]],[S,[s]],[x,[s]]]),M.call(i.isIgnoreRgx,[[k,/ ?browser$/i],[x,/ ?os$/i]]),M.call(i.toString,[[k,[a,s]],[A,[y]],[C,[r,o]],[S,[a,s]],[x,[a,s]]]),i}(),Me=function(i,c){var d=ui.init[c],p=ui.isIgnore[c]||0,w=ui.isIgnoreRgx[c]||0,h=ui.toString[c]||0;function b(){M.call(this,d)}return b.prototype.getItem=function(){return i},b.prototype.withClientHints=function(){return L?L.getHighEntropyValues(ce).then(function(m){return i.setCH(new de(m,!1)).parseCH().get()}):i.parseCH().get()},b.prototype.withFeatureCheck=function(){return i.detectFeature().get()},c!=F&&(b.prototype.is=function(m){var u=!1;for(var v in this)if(this.hasOwnProperty(v)&&!Di(p,v)&&T(w?$(w,this[v]):this[v])==T(w?$(w,m):m)){if(u=!0,m!=H)break}else if(m==H&&u){u=!u;break}return u},b.prototype.toString=function(){var m=j;for(var u in h)typeof this[h[u]]!==H&&(m+=(m?" ":j)+this[h[u]]);return m||H}),L||(b.prototype.then=function(m){var u=this,v=function(){for(var O in u)u.hasOwnProperty(O)&&(this[O]=u[O])};v.prototype={is:b.prototype.is,toString:b.prototype.toString};var E=new v;return m(E),E}),new b};function de(i,c){if(i=i||{},M.call(this,ce),c)M.call(this,[[Hi,Ai(i[z])],[zi,Ai(i[ye])],[l,/\?1/.test(i[Ne])],[o,oi(i[Ce])],[V,oi(i[ne])],[Ri,oi(i[Ee])],[y,oi(i[_e])],[U,Ai(i[Se])],[mi,oi(i[xe])]]);else for(var d in i)this.hasOwnProperty(d)&&typeof i[d]!==H&&(this[d]=i[d])}function oe(i,c,d,p){return this.get=function(w){return w?this.data.hasOwnProperty(w)?this.data[w]:void 0:this.data},this.set=function(w,h){return this.data[w]=h,this},this.setCH=function(w){return this.uaCH=w,this},this.detectFeature=function(){if(_&&_.userAgent==this.ua)switch(this.itemType){case k:_.brave&&typeof _.brave.isBrave==pi&&this.set(a,"Brave");break;case C:!this.get(e)&&L&&L[l]&&this.set(e,l),this.get(o)=="Macintosh"&&_&&typeof _.standalone!==H&&_.maxTouchPoints&&_.maxTouchPoints>2&&this.set(o,"iPad").set(e,f);break;case x:!this.get(a)&&L&&L[V]&&this.set(a,L[V]);break;case F:var w=this.data,h=function(b){return w[b].getItem().detectFeature().get()};this.set(k,h(k)).set(A,h(A)).set(C,h(C)).set(S,h(S)).set(x,h(x))}return this},this.parseUA=function(){return this.itemType!=F&&Mi.call(this.data,this.ua,this.rgxMap),this.itemType==k&&this.set(ni,Ti(this.get(s))),this},this.parseCH=function(){var w=this.uaCH,h=this.rgxMap;switch(this.itemType){case k:case S:var b=w[zi]||w[Hi],m;if(b)for(var u in b){var v=b[u].brand||b[u],E=b[u].version;this.itemType==k&&!/not.a.brand/i.test(v)&&(!m||/chrom/i.test(m)&&v!=Qi)&&(v=D(v,{Chrome:"Google Chrome",Edge:"Microsoft Edge","Chrome WebView":"Android WebView","Chrome Headless":"HeadlessChrome","Huawei Browser":"HuaweiBrowser","MIUI Browser":"Miui Browser","Opera Mobi":"OperaMobile",Yandex:"YaBrowser"}),this.set(a,v).set(s,E).set(ni,Ti(E)),m=v),this.itemType==S&&v==Qi&&this.set(s,E)}break;case A:var O=w[y];O&&(O&&w[mi]=="64"&&(O+="64"),Mi.call(this.data,O+";",h));break;case C:if(w[l]&&this.set(e,l),w[o]&&(this.set(o,w[o]),!this.get(e)||!this.get(r))){var G={};Mi.call(G,"droid 9; "+w[o]+")",h),!this.get(e)&&G.type&&this.set(e,G.type),!this.get(r)&&G.vendor&&this.set(r,G.vendor)}if(w[U]){var di;if(typeof w[U]!="string")for(var Ui=0;!di&&Ui<w[U].length;)di=D(w[U][Ui++],ee);else di=D(w[U],ee);this.set(e,di)}break;case x:var fi=w[V];if(fi){var vi=w[Ri];fi==Li&&(vi=parseInt(Ti(vi),10)>=13?"11":"10"),this.set(a,fi).set(s,vi)}this.get(a)==Li&&w[o]=="Xbox"&&this.set(a,"Xbox").set(s,void 0);break;case F:var we=this.data,K=function(be){return we[be].getItem().setCH(w).parseCH().get()};this.set(k,K(k)).set(A,K(A)).set(C,K(C)).set(S,K(S)).set(x,K(x))}return this},M.call(this,[["itemType",i],["ua",c],["uaCH",p],["rgxMap",d],["data",Me(this,i)]]),this}function I(i,c,d){if(typeof i===Y?(hi(i,!0)?(typeof c===Y&&(d=c),c=i):(d=i,c=void 0),i=void 0):typeof i===qi&&!hi(c,!0)&&(d=c,c=void 0),d&&typeof d.append===pi){var p={};d.forEach(function(u,v){p[v]=u}),d=p}if(!(this instanceof I))return new I(i,c,d).getResult();var w=typeof i===qi?i:d&&d[Fi]?d[Fi]:_&&_.userAgent?_.userAgent:j,h=new de(d,!0),b=c?Te(te,c):te,m=function(u){return u==F?function(){return new oe(u,w,b,h).set("ua",w).set(k,this.getBrowser()).set(A,this.getCPU()).set(C,this.getDevice()).set(S,this.getEngine()).set(x,this.getOS()).get()}:function(){return new oe(u,w,b[u],h).parseUA().get()}};return M.call(this,[["getBrowser",m(k)],["getCPU",m(A)],["getDevice",m(C)],["getEngine",m(S)],["getOS",m(x)],["getResult",m(F)],["getUA",function(){return w}],["setUA",function(u){return W(u)&&(w=u.length>Oi?ci(u,Oi):u),this}]]).setUA(w),this}I.VERSION=ke,I.BROWSER=li([a,s,ni,e]),I.CPU=li([y]),I.DEVICE=li([o,r,e,ai,l,g,f,N,si]),I.ENGINE=I.OS=li([a,s]);const ri={EMAIL_SEND_OFF:0,EMAIL_SEND_HOURLY:1,EMAIL_SEND_3HOURLY:2,EMAIL_SEND_DAILY:3,EMAIL_SEND_WEEKLY:4},Oe=[{text:t("notifications","Never"),value:ri.EMAIL_SEND_OFF},{text:t("notifications","1 hour"),value:ri.EMAIL_SEND_HOURLY},{text:t("notifications","3 hours"),value:ri.EMAIL_SEND_3HOURLY},{text:t("notifications","1 day"),value:ri.EMAIL_SEND_DAILY},{text:t("notifications","1 week"),value:ri.EMAIL_SEND_WEEKLY}],re={id:null,label:t("notifications","None")},qe=new I,ae=qe.getBrowser(),se=ae.name==="Safari"||ae.name==="Mobile Safari",Le={name:"UserSettings",components:{NcCheckboxRadioSwitch:pe,NcSelect:ve,NcSettingsSection:ge},setup(){const i=Pi(he("notifications","config")),c=Pi({secondary_speaker:J.getItem("secondary_speaker")==="true",secondary_speaker_device:JSON.parse(J.getItem("secondary_speaker_device"))??re}),d=le([]);return{BATCHTIME_OPTIONS:Oe,isSafari:se,config:i,storage:c,devices:d}},methods:{async updateSettings(){try{const i=new FormData;i.append("batchSetting",this.config.setting_batchtime),i.append("soundNotification",this.config.sound_notification?"yes":"no"),i.append("soundTalk",this.config.sound_talk?"yes":"no"),await me.post(fe("apps/notifications/api/v2/settings"),i),Bi(t("notifications","Your settings have been updated."))}catch(i){ki(t("notifications","An error occurred while updating your settings.")),console.error(i)}},updateLocalSettings(){try{J.setItem("secondary_speaker",this.storage.secondary_speaker),this.storage.secondary_speaker&&this.storage.secondary_speaker_device.id?J.setItem("secondary_speaker_device",JSON.stringify(this.storage.secondary_speaker_device)):J.removeItem("secondary_speaker_device"),Bi(t("notifications","Your settings have been updated."))}catch(i){ki(t("notifications","An error occurred while updating your settings.")),console.error(i)}},async initializeDevices(){if(!(!se&&navigator?.mediaDevices?.getUserMedia&&navigator?.mediaDevices?.enumerateDevices)||this.devices.length>0)return;let i=null;try{i=await navigator.mediaDevices.getUserMedia({audio:!0}),this.devices=(await navigator.mediaDevices.enumerateDevices()??[]).filter(c=>c.kind==="audiooutput").map(c=>({id:c.deviceId,label:c.label?c.label:c.fallbackLabel})).concat([re])}catch(c){ki(t("notifications","An error occurred while updating your settings.")),console.error("Error while requesting or initializing audio devices: ",c)}finally{i&&i.getTracks().forEach(c=>c.stop())}}}};var De=function(){var i=this,c=i._self._c;return c("NcSettingsSection",{attrs:{name:i.t("notifications","Notifications")}},[c("div",{staticClass:"notification-frequency__warning"},[i.config.is_email_set?i._e():c("strong",[i._v(i._s(i.t("notifications","You need to set up your email address before you can receive notification emails.")))])]),c("p",[c("label",{staticClass:"notification-frequency__label",attrs:{for:"notification_reminder_batchtime"}},[i._v(" "+i._s(i.t("notifications","Send email reminders about unhandled notifications after:"))+" ")]),c("select",{directives:[{name:"model",rawName:"v-model",value:i.config.setting_batchtime,expression:"config.setting_batchtime"}],staticClass:"notification-frequency__select",attrs:{id:"notification_reminder_batchtime",name:"notification_reminder_batchtime"},on:{change:[function(d){var p=Array.prototype.filter.call(d.target.options,function(w){return w.selected}).map(function(w){var h="_value"in w?w._value:w.value;return h});i.$set(i.config,"setting_batchtime",d.target.multiple?p:p[0])},function(d){return i.updateSettings()}]}},i._l(i.BATCHTIME_OPTIONS,function(d){return c("option",{key:d.value,domProps:{value:d.value}},[i._v(" "+i._s(d.text)+" ")])}),0)]),c("NcCheckboxRadioSwitch",{attrs:{checked:i.config.sound_notification},on:{"update:checked":[function(d){return i.$set(i.config,"sound_notification",d)},i.updateSettings]}},[i._v(" "+i._s(i.t("notifications","Play sound when a new notification arrives"))+" ")]),c("NcCheckboxRadioSwitch",{attrs:{checked:i.config.sound_talk},on:{"update:checked":[function(d){return i.$set(i.config,"sound_talk",d)},i.updateSettings]}},[i._v(" "+i._s(i.t("notifications","Play sound when a call started (requires Nextcloud Talk)"))+" ")]),i.config.sound_talk?[c("NcCheckboxRadioSwitch",{staticClass:"additional-margin-top",attrs:{checked:i.storage.secondary_speaker,disabled:i.isSafari},on:{"update:checked":[function(d){return i.$set(i.storage,"secondary_speaker",d)},i.updateLocalSettings]}},[i._v(" "+i._s(i.t("notifications","Also repeat sound on a secondary speaker"))+" ")]),i.isSafari?c("div",{staticClass:"notification-frequency__warning"},[c("strong",[i._v(i._s(i.t("notifications","Selection of the speaker device is currently not supported by Safari")))])]):i._e(),!i.isSafari&&i.storage.secondary_speaker?c("NcSelect",{attrs:{"input-id":"device-selector-audio-output",options:i.devices,label:"label","aria-label-combobox":i.t("notifications","Select a device"),clearable:!1,placeholder:i.t("notifications","Select a device")},on:{open:i.initializeDevices,input:i.updateLocalSettings},model:{value:i.storage.secondary_speaker_device,callback:function(d){i.$set(i.storage,"secondary_speaker_device",d)},expression:"storage.secondary_speaker_device"}}):i._e()]:i._e()],2)},He=[],ze=ue(Le,De,He,!1,null,"6f3d8087");const Re=ze.exports;gi.prototype.t=t,gi.prototype.n=n,new gi({el:"#notifications-user-settings",render:i=>i(Re)});
