/*
 * Click2Refer Virtual Dictionary Service
 * Created by  : Dinesh Babu.T
 * Website URL : www.zingersaga.net   */
 
var bktconst_root= server_info + '/wp-content/plugins/click2refer-virtual-dictionary/'; 

var s=document.createElement('script');
s.setAttribute('id','bktscript_s');
s.setAttribute('src',bktconst_root + 'js/click2refer_wpcore.js');
document.getElementsByTagName('body')[0].insertBefore(s,document.getElementsByTagName('body')[0].firstChild);

var bkt_css = document.createElement('link');
bkt_css.setAttribute('rel','stylesheet');
bkt_css.setAttribute('type','text/css');
bkt_css.setAttribute('href', bktconst_root + 'css/click2refer_styles.css'); 
document.getElementsByTagName('head')[0].insertBefore(bkt_css, document.getElementsByTagName('head')[0].firstChild);

var bkt_cache= document.createElement("div");
bkt_cache.style.display = "none";
bkt_cache.innerHTML = "<img src='" + bktconst_root + "images/lock.png'><img src='" + bktconst_root + "images/plus.png'><img src='" + bktconst_root + "images/loading.gif'>"; 

document.getElementsByTagName('body')[0].insertBefore( bkt_cache, document.getElementsByTagName('body')[0].firstChild);