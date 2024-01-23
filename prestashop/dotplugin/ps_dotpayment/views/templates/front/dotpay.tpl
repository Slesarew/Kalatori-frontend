<section>
  <p>Select your DOT-account <span id='dotpay_wallet_finded'></span>

  <div class="form-group">
    <div id="WalletID_load" style='display:none'><img src='{$module_host nofilter}/img/ajaxm.gif'> <font color='green'>loading...</font></div>

    <div style='padding-left:30px;' id="WalletID" class="form-control">
	<label style='display:block;text-align:left;'><input style='margin-right: 5px;' name='dot_addr' type='radio' value='QR'>QR-code</label>
	<div><input type='button' value='Open my Wallets' onclick='dot_onselect()'></div>
    </div>

  </div>

<div id='dotpay_console'></div>
<div id='dotpay_console_test'></div>
</section>

<script>

if(typeof(LLOADS)=='undefined') {
  LLOADS={
    f_read: function(k){ try { return window.localStorage.getItem(k); } catch(e) { return ''; }},
    f_save: function(k,v){ try { return window.localStorage.setItem(k,v); } catch(e) { return ''; } },
    LOADES: {},
    LOADS: function(u,f,err,sync) { if(typeof(u)=='string') u=[u];
        var randome='?random='+Math.random();
        var s;
        for(var i of u) { if(LLOADS.LOADES[i]) continue;
         if(/\.css($|\?.+?$)/.test(i)) {
            s=document.createElement('link');
            s.type='text/css';
            s.rel='stylesheet';
            s.href=i+randome;
            s.media='screen';
         } else {
            s=document.createElement('script');
            s.type='text/javascript';
            s.src=i+randome;
            s.defer=true;
         }
         s.setAttribute('orign',i);
         if(sync) s.async=false;
         s.onerror=( typeof(err)=='function' ? err : function(e){ alert('File not found: '+e.src); } );
         s.onload=function(e){ e=e.target;
	    LLOADS.LOADES[e.getAttribute('orign')]=1;
            var k=1; for(var i of u){
		if(!LLOADS.LOADES[i]){ k=0; break; }
	    }
            if(k){ if(f) f(e.src); }
         };
         document.getElementsByTagName('head').item(0).appendChild(s);
        }
        if(!s) { if(f) f(1); }
    },
    LOADS_sync: function(u,f,err) { LLOADS.LOADS(u,f,err,1) },
    LOADS_promice: function(file,sync) {
        return new Promise(function(resolve, reject) { LLOADS.LOADS(file,resolve,reject,sync); });
    },
  };

  wallet_start=function() {
	var wpath="{$module_host nofilter}";
	LLOADS.LOADS([ wpath+'/js/DOT.js' ], function(){
	    DOT.presta_run({
		wpath:wpath,
		total:"{$total nofilter}",
		module_name:"{$module_name nofilter}",
		id:"{$id nofilter}",
		shop_id:"{$shop_id nofilter}",
		products:"{$products nofilter}",
	    });
	});
  };

  dot_onselect=function(e) {
    if(typeof(DOT)=='undefined') {
	document.getElementById('WalletID').style.display='none';
	document.getElementById('WalletID_load').style.display='block';
	wallet_start();
    } // else DOT.f_save(e.id,e.value);
  };
}

try {
    var ps = ''+LLOADS.f_read("pay_select");
    var p=document.getElementById(ps); p.focus(); p.click();
    p=p.querySelector("INPUT#"+ps.replace(/\-container/g,'')); p.focus(); p.click();

    document.querySelectorAll('DIV[id^="payment-option-"]').forEach(function(q){
        if(q.id.indexOf('-container')<0) return;
        q.onclick=function(e){ var x=this;
            if(x.tagName!='DIV'||x.id.indexOf('-container')<0) x=x.closest('DIV[id^="payment-option-"]'); if(!x) return;
            LLOADS.f_save('pay_select',x.id);
        };
    });

} catch(x) { }

try { if((''+LLOADS.f_read("WalletID")).length) dot_onselect(document.getElementById('WalletID')); } catch(x){}

</script>
