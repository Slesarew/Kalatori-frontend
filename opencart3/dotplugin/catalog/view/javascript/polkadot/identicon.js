import init, {identicon} from '/catalog/view/javascript/polkadot/identicon-wasm.js';
async function run() { await init(); DOT.identicon_init(); } run();
window.identicon=identicon;
