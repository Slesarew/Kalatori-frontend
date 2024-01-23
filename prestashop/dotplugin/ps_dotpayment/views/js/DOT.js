DOT={
    path: false,
    // mainrand: '', // '?rand='+Math.random(),
    mainjs: false,
    wss: '', // 'wss://node-shave.zymologia.fi',
    mul: 0, // 1000000000000,

    h: function(s){
        return (''+s).replace(/\&/g,'&'+'amp;').replace(/\</g,'&'+'lt;').replace(/\>/g,'&'+'gt;').replace(/\'/g,'&'+'#039;').replace(/\"/g,'&'+'#034;'); // '
    },

    dom: function(e) { return (typeof(e)=='object' ? e : document.getElementById(e) ); },

    'alert': function(s){
	var w=document.getElementById('dotpay_console');
	if(s=='clear') w.innerHTML='';
	else w.innerHTML+=s+'<br>';
    },

    Ealert: function(s){
	DOT.alert("<div class='alert alert-danger' id='dotpay_console_test'>"+s+"</div>");
    },

    Talert: function(s){
	console.log(s);
	var w=document.getElementById('dotpay_console_test');
	if(s=='clear') w.innerHTML='';
	else w.innerHTML+=s+'<br>';
    },

    f_save: function(k,v){ try { return window.localStorage.setItem(k,v); } catch(e) { return ''; } },
    f_read: function(k){ try { return window.localStorage.getItem(k); } catch(e) { return ''; }},
    f_del: function(k){ try { return window.localStorage.removeItem(k); } catch(e) { return ''; }},

// ============== presta ==============
cx: {},
presta_run: function(cx) {
    DOT.cx=cx;
    DOT.dom('WalletID_load').style.display='none';
    DOT.dom('WalletID').style.display='block';
    DOT.init(cx.wpath);

    var e=document.querySelector('FORM[action*="'+cx.module_name+'"]');
    if(!e) return DOT.alert("Design error");

// LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL
	DOT.button_on();
// LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL

	// да блять согласен
	document.querySelectorAll("INPUT[type='checkbox'][name*='conditions_to_approve']").forEach(function(e){
	    e.parentNode.style.border='1px dotted red';
	    e.setAttribute('checked',true);
	});

    e.onsubmit=function(x) {
	DOT.button_on();
	DOT.alert('clear');
	var acc; DOT.dom("WalletID").querySelectorAll("INPUT").forEach(function(x){ if(x.checked) acc=x.value; });
	if(acc == 'false'|| acc=='') {
	    DOT.alert('Please select account');
	    return false;
	}
	cx.acc=acc;

	DOT.alert('Account: '+DOT.h(acc)+"<br>Total: "+DOT.h(cx.total)+"<br>id: "+DOT.h(cx.id)
// +"<br>shop_id: "+DOT.h(cx.shop_id)
// +"<br>products: "+DOT.h(cx.products)
);


// return true;

	return DOT.presta_submit(cx);
	// return true;
	// return false;
    };

//    document.querySelectorAll('FORM').forEach(function(q){ q.style.border='10px solid orange'; });
//    var w=e.closest("FORM");
//    w.style.border='10px solid green';
//    w=w.querySelector('BUTTON[type="submit"]');
//    DOT.alert(w.tagName+' : '+w.id+' '+w.className+' = '+w.action);
//    w.disabled=false;
//    w.style.border='10px solid red';

},


button_on: function() {
	// да блять разрешить
	document.querySelectorAll("BUTTON[type='submit'][disabled]").forEach(function(e){
	    e.classList.remove("disabled");
	    e.disabled=null;
	    e.style.border='1px dashed red';
	});
},

presta_submit: function(cx) { if(!cx) cx=DOT.cx;
    DOT.alert('clear');
    DOT.AJAX(
	cx.wpath.replace(/\/views$/g,'/')+'ajax.php',
	async function(s0) { var s=''+s0; s=s.replace(/^\s+/g,'').replace(/\s+$/g,'');
	    var w=s.split('{');

	    if(w.length>1 && w[0]!='') {
		DOT.Talert("PHP WARNING: "+DOT.h(w[0]));
		s=s.substring(w[0].length);
	    }

	    try { var json=JSON.parse(s); } catch(e) {
		DOT.alert("Json error: ["+DOT.h(s0)+"]");
		DOT.button_on();
		return;
	    }
	    // =================
	    if (json.error) {
                if (json.error.warning) DOT.alert('warning: '+json.error.warning);

                if(typeof(json['error'])=='object') {
                    for (i in json.error) DOT.Ealert('error: '+i+' = '+json.error[i]);
                } else {
                    DOT.Ealert('error: '+json.error +(json.error_message ? ' '+json.error_message : '') );
                }
		DOT.button_on();
		return;
            } else {
                if(json.redirect) { location = json.redirect; }
                if( json.daemon_result == 'Waiting' && json.daemon_pay_account && 1*json.price
		) {
		    if(json.daemon_wss) DOT.wss = json.daemon_wss.replace(/\:\d+$/g,'');
		    if(json.daemon_mul) DOT.mul = 1*json.daemon_mul;
                    json.my_account = cx.acc;
		    json.pay_account = json.daemon_pay_account;
		    if(!DOT.accounts || !DOT.accounts.length ) {
			DOT.alert("You have no wallets extentions. Please sent transaction manually to address: "+json.pay_account);
		    } else {
                	DOT.pay(json);
		    }
		} else {
		    var s='';
		    for(var i in json) s+=i+' = ['+json[i]+"]\n";
		    DOT.alert('ERROR OPT:\n\n '+s);
		}
            }
	    // =================
	},
	//{order_id:cx.is,price:cx.total}
	JSON.stringify({order_id:cx.id,price:cx.total.replace(/^.*?([0-9\.]+).*?$/g,'$1')})
    );
    return false;
  },

















//====================================================================================================

progress: {
    total: 30000,
    now: 0,
    timeout: 100,
    id: 0,
    run: function(x, fn_timeout) {
	    if(x===0) DOT.progress.now=0;

	    if(! DOT.progress.id) {
		DOT.progress.id=setInterval(DOT.progress.run,DOT.progress.timeout);
	    }

	    DOT.progress.now+=DOT.progress.timeout;
	    if(DOT.progress.now > DOT.progress.total) {
		    clearInterval(DOT.progress.id);
		    return;
		    if(fn_ftimeout) fn_timeout();
		    else DOT.dom('dotpay_progress').innerHTML='Error: timeout';
		    return;
	    }

	    var prc=(Math.floor(100*DOT.progress.now/DOT.progress.total))

	    if(!DOT.dom('dotpay_progress')) {
		var d = document.createElement("div");
		d.id = 'dotpay_progress';
		d.style.position = 'fixed';
		d.style.left = '0px';
		d.style.bottom = '0px';
		d.style.padding = '3px 3px 1px 3px';
		d.style.width = '100%';
		document.body.appendChild(d);
	    }

	    if(prc>50) return;

	    var st = "text-align: -moz-right;"
		+"text-align: right;"
		+"width:100%;border:1px solid #666;"
		+"background:linear-gradient(to right, green 0%, red 100%);";

	    DOT.dom('dotpay_progress').innerHTML=
		"<div style='"+st+"'>"
		    +"<div style='width:"+(100-prc)+"%;height:10px;background-color:white;'></div>"
		+"</div>";
    },
    stop: function() {
    	clearInterval(DOT.progress.id);
	var q=DOT.dom('dotpay_progress'); if(q) document.body.removeChild(q);
	// DOT.dom('dotpay_progress').innerHTML='';
    },
},

AJAX: function(url,opt,s) {
  if(!opt) opt={}; else if(typeof(opt)=='function') opt={callback:opt};
  var async=(opt.async!==undefined?opt.async:true);
  try{
    if(!async && !opt.callback) opt.callback=function(){};
    var xhr=new XMLHttpRequest();

    xhr.onreadystatechange=function(){
    try{
      if(this.readyState==4) {
        if(this.status==200 && this.responseText!=null) {
            if(this.callback) this.callback(this.responseText,url,s);
            else eval(this.responseText);
        } else if(this.status==500) {
            if(this.onerror) this.onerror(this.responseText,url,s);
            else if(opt.callback) opt.callback(false,url,s);
        }
      }
     } catch(e){ DOT.alert('Error Ajax: '+DOT.h(this.responseText)); }
    };

    for(var i in opt) xhr[i]=opt[i];

    if(opt.error) xhr.onerror=opt.error;
    if(opt.timeout) xhr.timeout=opt.timeout;
    if(opt.ontimeout) xhr.ontimeout=opt.ontimeout;

    xhr.open((opt.method?opt.method:(s?'POST':'GET')),url,async);

    if(s) {
        if(typeof(s)=='object' && !(s instanceof FormData) ) {
          var formData = new FormData();
          for(var i in s) formData.append(i,s[i]);
          var k=0; Array.from(formData.entries(),([key,D])=>(k+=(typeof(D)==='string'?D.length:D.size)));
          xhr.send(formData);
        } else xhr.send(s);
    } else xhr.send();

    if(!async) return ( (xhr.status == 200 && xhr.readyState == 4)?xhr.responseText:false );

  } catch(e) { if(!async) return false; }
},





    payWithPolkadot: async function(json,SENDER, price, destination, wss) {
	if(!wss) wss=DOT.wss;
	const provider = new polkadotApi.WsProvider(wss); // 'wss://rpc.polkadot.io'
	const api = await polkadotApi.ApiPromise.create({ provider });
        api.query.system.account( destination ).then((e) => { DOT.Talert('balance#1 start = '+ e.data.free ); });
	const injector = await polkadotExtensionDapp.web3FromAddress(SENDER);
	const transferExtrinsic = api.tx.balances.transfer(destination, price);
	transferExtrinsic.signAndSend(SENDER, { signer: injector.signer }, ({ status }) => {
            if(!DOT.progress.id) DOT.progress.run(0,
		    function(){
			DOT.alert('Error: timeout');
			setTimeout(DOT.progress.stop,800);
		    }); // start progressbar
	    DOT.Talert('status='+status.type);
	    if (status.isInBlock || status.type == 'InBlock') {
		DOT.Talert(`status:isInBlock Completed at block hash #${status.asInBlock.toString()}`);
	        api.query.system.account( destination ).then((e) => { DOT.Talert('balance isInBlock = '+ e.data.free ); });
	    } else if (status.isFinalized || status.type == 'Finalized') {
		DOT.Talert('status:Finalized');
	        api.query.system.account( destination ).then((e) => { DOT.Talert('balance Finalized = '+ e.data.free ); });
	        DOT.progress.stop();
		if(api) api.disconnect();
		DOT.presta_submit();
	    } else {
		DOT.Talert(`status: ${status.type}`);
	    }
	}).catch((error) => {
            DOT.progress.stop(); // stop progressbar
	    DOT.Talert('transaction failed'+error);
	    DOT.Ealert(error);
	    if(api) api.disconnect();
	    DOT.button_on();
        });
    },

    pay: async function(json) {
        await LLOADS.LOADS_promice([
            DOT.mainjs+'bundle-polkadot-types.js',
            DOT.mainjs+'bundle-polkadot-api.js',
        ],1);
	DOT.alert("Pay account: "+json.pay_account+"<br>Total: "+json.price*DOT.mul);
	DOT.payWithPolkadot(json, json.my_account, json.price*DOT.mul, json.pay_account);
    },

    et: 0,
    init: async function(x){
	this.path=x;
	this.mainjs=x+'/js/';

        // if( (''+document.location.href).match(/lleo/) ) document.querySelectorAll(".lleotest").forEach((e)=>{e.style.display='block'});

	// load JS
	await LLOADS.LOADS_promice([
	 DOT.mainjs+'bundle-polkadot-util.js',
	 DOT.mainjs+'bundle-polkadot-util-crypto.js',
	 DOT.mainjs+'bundle-polkadot-extension-dapp.js',
	],1);

     try {
	// connect Wallets
        var wallets=await polkadotExtensionDapp.web3Enable('dotpay');
	DOT.wallets=wallets;

	var r={'manual':[
		"<label style='display:block;text-align:left;'><input style='margin-right: 5px;' name='dot_addr' type='radio' value='QR'>QR-code</label>",
	]};
        if( !wallets.length ) {
	    if(!DOT.et) DOT.alert("<b>Wallets not found</b>"
		    +"<br>You can use Wallet extention "
		    +(this.navigator()=='firefox'
			? "<a href='https://addons.mozilla.org/en-US/firefox/addon/polkadot-js-extension/'>polkadot{.js} for Firefox</a>"
			: (this.navigator()=='chrome'
			    ? "<a href='https://chrome.google.com/webstore/detail/polkadot%7Bjs%7D-extension/mopnmbcafieddcagagdcbnhejhlodfdd'>polkadot{.js} for Chrome</a>"
			    : "<a href='https://github.com/polkadot-js/extension'>polkadot{.js}</a>"
			  )
		    )
		    +" or <a href='https://www.subwallet.app/'>Subwallet</a>"
		    +"<br>Also you can make DOT-payment manually using QR-code"
	    );
	} else {
	    var accounts = await polkadotExtensionDapp.web3Accounts({ss58Format:0});
	    DOT.accounts=accounts;
	    var deff = DOT.f_read('WalletID');
	    for(var l of accounts) {
		    var wal = l.meta.source.replace(/\-js$/,'');
		    if(!r[wal]) r[wal]=[];
		    r[wal].push("<label style='display:block;text-align:left;'>"
		     +"<input name='dot_addr' type='radio' value='"+DOT.h(l.address)+"'"
		     +(deff == l.address ? ' checked' : '')
		     +">&nbsp;&nbsp;"+DOT.h(l.meta.name)
		     +"<div>"+DOT.h(l.address)+"</div>"
		    +"</label>");
	    }
	}

        var op=''; for(var wal in r) {
	    op += (wal==''? r[wal].join('') : "<div style='margin-left:10%;'>"+DOT.h(wal)+"</div>" + r[wal].join('') );
	} DOT.dom('WalletID').innerHTML=op;

	// Onchang -: save to LocalStorage
	DOT.dom('WalletID').querySelectorAll("INPUT").forEach(function(ee){ ee.onchange=DOT.save_addr; });

	DOT.dom('dotpay_wallet_finded').innerHTML="(found "+accounts.length+" accounts"+
	    (wallets.length > 1 ? " in "+wallets.length+" wallets)":"");

	// Load identicons
	var d=document,s=d.createElement('script');
	s.src=DOT.mainjs+'identicon.js?'+Math.random();
	s.type='module';
	d.head.append(s);

     } catch(ee) {
	    if(!DOT.et) { DOT.et=0; /*DOT.Talert('Wallets error');*/ }
	    if(++DOT.et < 60) setTimeout(wallet_start,1000);
     }

    },

    navigator: function(){ // get Browser' name
        var ua=navigator.userAgent, tem;
        var M=ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
        if(/trident/i.test(M[1])){
	    tem= /\brv[ :]+(\d+)/g.exec(ua) || [];
            return 'IE '+(tem[1] || '');
	}
        if(M[1]==='Chrome'){
	    tem= ua.match(/\b(OPR|Edge)\/(\d+)/);
    	    if(tem!= null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
	}
	M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
	if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
	return M[0].toLowerCase();
    },


    save_addr: function(x) { DOT.f_save('WalletID',this.value); },

    identicon_init: function() {
	DOT.dom('WalletID').querySelectorAll('LABEL').forEach(function(p){
	    var adr=p.querySelector('DIV');
	    if(!adr) return;
	    adr=adr.innerHTML;
	    var h=p.offsetHeight+'px';
    	    p.innerHTML="<div style='display:inline-block; width:"+p.offsetHeight+"px;height:"+p.offsetHeight+"px;'>"
		+window.identicon(adr)
		+"</div>&nbsp;<div style='display:inline-block'>"+p.innerHTML+"</div>";
	    p.querySelector('INPUT').onchange=DOT.save_addr;
	});
    },
};
