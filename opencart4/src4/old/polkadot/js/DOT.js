DOT={
//    price_mul: 10000,
//    price_mul: 1000000000000,
    price_mul: 10000000000,

    path: false,
    mainrand: '?rand='+Math.random(),
    mainjs: false,
    wss: 'wss://node-shave.zymologia.fi',

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
	var w=document.getElementById('dotpay_console_test');
	if(s=='clear') w.innerHTML='';
	else w.innerHTML+=s+'<br>';
    },


    f_save: function(k,v){ try { return window.localStorage.setItem(k,v); } catch(e) { return ''; } },
    f_read: function(k){ try { return window.localStorage.getItem(k); } catch(e) { return ''; }},
    f_del: function(k){ try { return window.localStorage.removeItem(k); } catch(e) { return ''; }},



//====================================================================================================

progress: {
    total: 30000,
    now: 0,
    timeout: 100,
    id: 0,
    run: function(x) {
	    if(x===0) DOT.progress.now=0;
	    if(x===-1) {
	    	clearInterval(DOT.progress.id);
		DOT.dom('dotpay_progress').innerHTML='';
		return;
	    }

	    if(! DOT.progress.id) {
		DOT.progress.id=setInterval(DOT.progress.run,DOT.progress.timeout);
	    }

	    DOT.progress.now+=DOT.progress.timeout;
	    if(DOT.progress.now > DOT.progress.total) {
		    clearInterval(DOT.progress.id);
		    DOT.dom('dotpay_progress').innerHTML='Error: timeout';
		    return;
	    }

	    var prc=(Math.floor(100*DOT.progress.now/DOT.progress.total))

	    DOT.dom('dotpay_progress').innerHTML=
		"<div style='width:100%;border:1px solid #666;'>"
		+"<div style='width:"+prc+"%;height:3px;background-color:red;'></div>";
    },

    stop: function() {
    },
},


// AJAX from ESP8266 v3
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


    submit_test: async function(lang) {
	DOT.Talert('language='+lang);
        // await LLOADS.LOADS_promice([ DOT.mainjs+'dot.js' ],1); dot.go();
    },


    submit: function(lang) {

    DOT.alert('clear');

	if( document.getElementById('WalletID').value =='false') {
	    DOT.alert('Please select account or QR');
	    return;
	}

    $('#button-confirm').button('loading');

    DOT.AJAX(
	'index.php?route=extension/polkadot/payment/polkadot.confirm&language='+lang, // en-gb', // {{ language }}',
	async function(s0) { var s=s0; s=s.replace(/^\s+/g,'').replace(/\s+$/g,'');
	    var w=s.split('{');

	    if(w.length>1 && w[0]!='') {
		DOT.Talert("PHP WARNING: "+DOT.h(w[0]));
		s=s.substring(w[0].length);
	    }

	    try { var json=JSON.parse(s); } catch(e) {
		DOT.alert("Json error: ["+DOT.h(s0)+"]");
	        $('#button-confirm').button('reset');
		return;
	    }
	    // =================
	    if (json.error) {
                if (json.error.warning) DOT.alert('warning: '+json.error.warning);

                if(typeof(json['error'])=='object') {
                    for (i in json.error) DOT.Ealert('error: '+i+' = '+json.error[i]);
                } else {
                    DOT.Ealert('error: '+json.error
                        +(json.error_message ? ' '+json.error_message : '')
                    );
                }
                $('#button-confirm').button('reset');
		return;
            } else {

                if(json.redirect) { location = json.redirect; }

                if( json.daemon_result == 'Waiting' && json.daemon_pay_account && 1*json.price
		) {

		    if(json.daemon_wss) DOT.wss = json.daemon_wss.replace(/\:\d+$/g,''); // "wss://westend-rpc.polkadot.io:443"

                    json.my_account = document.getElementById('WalletID').value;
		    var a=json.daemon_pay_account;

		    // if Westend Forman
		    if( a.length != 66 || a.replace(/0x[0-9a-f]+/gi,'') != '') {
                	a = a.replace(/^0x/g,'');
	        	await LLOADS.LOADS_promice([ DOT.mainjs+'bundle-polkadot-keyring.js' ],1);
			try{ a = polkadotUtil.u8aToHex(polkadotKeyring.decodeAddress(a)); } catch(err) { alert('west2acc() error'); }
		    }
		    json.pay_account=a;

		    if(!DOT.accounts || !DOT.accounts.length ) {
			DOT.alert("You have no wallets extentions. Please sent transaction manually to address: "+json.pay_account);
			$('#button-confirm').button('reset');
		    } else {
                	DOT.pay(json);
		    }
		} else {
		    var s='';
		    for(var i in json) s+=i+' = ['+json[i]+"]\n";
		    DOT.alert('ERROR OPT:\n\n '+s);
	            $('#button-confirm').button('reset');
		}
            }
	    // =================
	},
	$('#form-polkadot').serialize()
    );

    return false;

  },

    payWithPolkadot: async function(json,SENDER, price, destination, wss) {
	if(!wss) wss=DOT.wss;

DOT.Talert('pay start #1');

	const provider = new polkadotApi.WsProvider(wss); // 'wss://rpc.polkadot.io'
	const api = await polkadotApi.ApiPromise.create({ provider });
        api.query.system.account( destination ).then((e) => { DOT.Talert('balance#1 start = '+ e.data.free ); });

	const injector = await polkadotExtensionDapp.web3FromAddress(SENDER);

DOT.Talert('pay start #2');

	const transferExtrinsic = api.tx.balances.transfer(destination, price);

DOT.Talert('pay start #3');

	transferExtrinsic.signAndSend(SENDER, { signer: injector.signer }, ({ status }) => {

	console.log('status');

            if(!DOT.progress.id) DOT.progress.run(0); // start progressbar

	    DOT.Talert('status='+status.type); // +' / '+Math.floor(DOT.progress.now/1000) );

	    if (status.isInBlock || status.type == 'InBlock') {
		console.log(`status:isInBlock Completed at block hash #${status.asInBlock.toString()}`);
		DOT.Talert(`status:isInBlock Completed at block hash #${status.asInBlock.toString()}`);

	        api.query.system.account( destination ).then((e) => { DOT.Talert('balance isInBlock = '+ e.data.free ); });

	    } else if (status.isFinalized || status.type == 'Finalized') {
		console.log('status:Finalized');
		DOT.Talert('status:Finalized');

	        api.query.system.account( destination ).then((e) => { DOT.Talert('balance Finalized = '+ e.data.free ); });

	        DOT.progress.run(-1); // stop progressbar
		if(api) api.disconnect();
		DOT.submit();
	    // } else if (status.type == 'Ready' || status.type == 'InBlock') {
	    } else {
	        // DOT.progress.run(-1); // stop progressbar
		console.log(`status: ${status.type}`);
		DOT.Talert(`status: ${status.type}`);
	    }
	}).catch((error) => {
            DOT.progress.run(-1); // stop progressbar
	    console.log('transaction failed'+error);
	    DOT.Ealert(error);
	    if(api) api.disconnect();
	    $('#button-confirm').button('reset');
        })  // .finally(() => {	    api.disconnect();	})
    ;
    },

    pay: async function(json) {
        await LLOADS.LOADS_promice([
            DOT.mainjs+'bundle-polkadot-types.js',
            DOT.mainjs+'bundle-polkadot-api.js',
        ],1);

	DOT.alert(// 'DOT.pay:\nFrom: '+json.my_account
	"Pay account: "+json.pay_account
	+"<br>Total: "+json.price*DOT.price_mul);

	DOT.payWithPolkadot(json, json.my_account, json.price*DOT.price_mul, json.pay_account);
    },


    init: async function(x){


    // убираем отладочный мусор
//     if(DOT.f_read('lleo')!='da') 
    if( !(''+document.location.href).match(/lleo/) ) 
    document.querySelectorAll(".lleotest").forEach((e)=>{e.style.display='none'});

//    DOT.f_save('lleo','da');
//    f_read: function(k){ try { return window.localStorage.getItem(k); } catch(e) { return ''; }},


	this.path=x;
	this.mainjs=x+'/js/';

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

	// DOT.alert('Wallets: '+wallets.length);

	var r={'manual':["<option value='false'>Select account</option>","<option value='QR'>QR-code</option>"]};
        if( !wallets.length ) {
	    DOT.alert("<b>Wallets not found</b>"
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
	    var accounts = await polkadotExtensionDapp.web3Accounts(
{ss58Format:0}
);

/*
// use the default as setup on init
// 5CSbZ7wG456oty4WoiX6a1J88VUbrCXLhrKVJ9q95BsYH4TZ
console.log('Substrate generic', pair.address);

// adjust the default ss58Format for Kusama
// CxDDSH8gS7jecsxaRL9Txf8H5kqesLXAEAEgp76Yz632J9M
keyring.setSS58Format(2);
console.log('Kusama', pair.address);

// adjust the default ss58Format for Polkadot
// 1NthTCKurNHLW52mMa6iA8Gz7UFYW5UnM3yTSpVdGu4Th7h
keyring.setSS58Format(0);
console.log('Polkadot', pair.address);
*/











	    DOT.accounts=accounts;
	//    DOT.alert('Accounts: '+accounts.length);
	    var def = DOT.f_read('WalletID');
	    for(var l of accounts) {
		    var wal = l.meta.source.replace(/\-js$/,'');
		    if(!r[wal]) r[wal]=[];
		    r[wal].push("<option value='"+DOT.h(l.address)+"'"
		    +(def == l.address ? ' selected=""' : '')
		    +">"+DOT.h(l.address+" "+l.meta.name)+"</option>");
	    }
	}

        var options=''; for(var wal in r) {
	    options += (wal==''? r[wal].join('') : "<optgroup label='"+DOT.h(wal)+"'>" + r[wal].join('') + "</optgroup>" );
	}

        document.getElementById('WalletID').innerHTML=options;
	document.getElementById('dotpay_wallet_finded').innerHTML="Found "+accounts.length+" accounts"+
	    (wallets.length > 1 ? " in "+wallets.length+" wallets":"");

} catch(ee) {
	    DOT.Talert('Wallets error');

	    if(!DOT.et) DOT.et=0;
	    if(++DOT.et < 10) setTimeout(wallet_start,1000); // эта йобаная сука внешний кошелек еще может в первый момент не сработать
}

    },

    navigator: function(){
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
};
