/*
function identicon_init(e) {

    DOT.dom('WalletID').querySelectorAll('LABEL').forEach(function(p){
	if(p.classList.contains('identicon_done')) return;
	var adr=p.getAttribute('identicon'); // attribute identicon="5GbgpMNkENaf4rZmioBtPmkb7UZb4YSozVPdWp9fa2CtdKdZ"
	if(!adr||adr=='') { // if no attribute identicon, try to find address in classList
    	    adr=false;
    	    for(var c of p.classList) if(c.length==48 || c.startsWith('0x') || 1*c) { adr=c; break; }
	}
	if(adr==false) adr=p.innerHTML; // if no address in classList, take address in innerHTML
	p.innerHTML="<div style='display:inline-block; width:"+p.offsetHeight+"px;height:"+p.offsetHeight+"px'>"+window.identicon(adr)+"</div>&nbsp;"+p.innerHTML;
	p.classList.add('identicon_done');
    });



    if(!e) e=document;
    // add identicon to all DIV with class "identicon"
    e.querySelectorAll('DIV[identicon]').forEach(function(p){
	if(p.classList.contains('identicon_done')) return;
	var adr=p.getAttribute('identicon'); // attribute identicon="5GbgpMNkENaf4rZmioBtPmkb7UZb4YSozVPdWp9fa2CtdKdZ"
	if(!adr||adr=='') { // if no attribute identicon, try to find address in classList
    	    adr=false;
    	    for(var c of p.classList) if(c.length==48 || c.startsWith('0x') || 1*c) { adr=c; break; }
	}
	if(adr==false) adr=p.innerHTML; // if no address in classList, take address in innerHTML
	p.innerHTML="<div style='display:inline-block; width:"+p.offsetHeight+"px;height:"+p.offsetHeight+"px'>"+window.identicon(adr)+"</div>&nbsp;"+p.innerHTML;
	p.classList.add('identicon_done');
    });


    e.querySelectorAll('TD[identicon]').forEach(function(p){
	if(p.classList.contains('identicon_done')) return;
	var adr=p.getAttribute('identicon'); // attribute identicon="5GbgpMNkENaf4rZmioBtPmkb7UZb4YSozVPdWp9fa2CtdKdZ"
	if(!adr||adr=='') { // if no attribute identicon, try to find address in classList
    	    adr=false;
    	    for(var c of p.classList) if(c.length==48 || c.startsWith('0x') || 1*c) { adr=c; break; }
	}
	if(adr==false) adr=p.innerHTML; // if no address in classList, take address in innerHTML
	p.innerHTML="<div style='display:inline-block; width:"+p.offsetHeight+"px;height:"+p.offsetHeight+"px'>"+window.identicon(adr)+"</div>&nbsp;"+p.innerHTML;
	p.classList.add('identicon_done');
    });


    // add identicon to radio buttons (in blocks with class "identicon")
    e.querySelectorAll(".identicon input[type='radio']").forEach(function(p){
	if(p.classList.contains('identicon_done')) return;
	var c=p.value;
	var next=p.nextSibling;
	if(next) {
    	    if(!next.innerHTML) {
        	// create div inline instead of text node
        	var div = document.createElement('div');
        	div.innerHTML = next.nodeValue;
        	div.style.display = 'inline-block';
        	next.parentNode.insertBefore(div, next);
        	next.parentNode.removeChild(next);
        	next = div;
    	    }
    	    var cc = next.innerHTML;
    	    if(c.length!=48 && !c.startsWith('0x') && !(1*c)) c=cc;
    	    next.innerHTML = "<div style='display:inline-block; width:"+next.offsetHeight+"px;height:"+next.offsetHeight+"px'>"+window.identicon(c)+"</div>&nbsp;"+cc;
	}
	p.classList.add('identicon_done');
    });
}
*/

import init, {identicon} from '/modules/ps_dotpayment/views/js/wasm.js';
async function run() { await init('/modules/ps_dotpayment/views/js/wasm.wasm'); DOT.identicon_init(); } run();
window.identicon=identicon;
