<section>
  <p>Select your DOT-account <span id='dotpay_wallet_finded'></span>

  <div class="form-group">
    <div id="WalletID_load" style='display:none'><img src='{$module_host nofilter}/img/ajaxm.gif'> <font color='green'>loading...</div>
    <select style='UUmax-width:100%' id="WalletID" name="WalletID" onchange="dot_onselect(this)" onclick="dot_onselect(this)" class="form-control" required>
	<option value="">{l s='Choose your account or QR' mod='ps_dotpayment'}</option>
        <option value='QR'>QR-code</option>
    </select>
  </div>

<div id='dotpay_console'></div>
<div id='dotpay_console_test'></div>
</section>

<script>

if(typeof(LLOADS)=='undefined') {
  LLOADS={
    f_read: function(k){ try { return window.localStorage.getItem(k); } catch(e) { return ''; }},
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

  wallet_start=function(){
	var path="{$module_host nofilter}";
	LLOADS.LOADS([ path+'/js/DOT.js' ], function(){
	    document.getElementById('WalletID_load').style.display='none';
	    document.getElementById('WalletID').style.display='block';
	    DOT.init(path);

	    document.querySelectorAll('FORM[action*="{$module_name nofilter}"]').forEach(function(e){
		DOT.alert('DDD: '+e.tagName+' : '+e.id+' '+e.className+' = '+e.action);
		e.onsubmit=function(x) {
		    if(DOT.dom("WalletID").value == 'false') return false;
		    alert(DOT.dom("WalletID").value);
		    // return true;
		    return false;
		};
	    });

	});
  };

  dot_onselect=function(e){
    if(typeof(DOT)=='undefined') {
	document.getElementById('WalletID').style.display='none';
	document.getElementById('WalletID_load').style.display='block';
	wallet_start();
    } else DOT.f_save(e.id,e.value);
  };
}

if((''+LLOADS.f_read("WalletID")).length) dot_onselect(document.getElementById('WalletID'));

</script>
