./cpmkdir.php ./admin-controller-polkadot.php     copy/admin/controller/extension/payment/polkadot.php
./cpmkdir.php ./admin-language-polkadot.php       copy/admin/language/en-gb/extension/payment/polkadot.php
./cpmkdir.php ./admin-model-polkadot.php          copy/admin/model/extension/payment/polkadot.php
./cpmkdir.php ./admin-template.twig               copy/admin/view/template/extension/payment/polkadot.twig
./cpmkdir.php ./catalog-controller.php            copy/catalog/controller/extension/payment/polkadot.php
./cpmkdir.php ./catalog-language.php              copy/catalog/language/en-gb/extension/payment/polkadot.php
./cpmkdir.php ./catalog-model.php                 copy/catalog/model/extension/payment/polkadot.php
./cpmkdir.php ./catalog-template.twig             copy/catalog/view/theme/default/template/extension/payment/polkadot.twig
./cpmkdir.php ./DOT.js                            copy/catalog/view/javascript/polkadot/DOT.js
exit



ln -s ../admin/controller/extension/payment/polkadot.php                       ./admin-controller-polkadot.php
ln -s ../admin/language/en-gb/extension/payment/polkadot.php                   ./admin-language-polkadot.php
ln -s ../admin/model/extension/payment/polkadot.php                            ./admin-model-polkadot.php
ln -s ../admin/view/template/extension/payment/polkadot.twig                   ./admin-template.twig

ln -s ../catalog/controller/extension/payment/polkadot.php                     ./catalog-controller.php
ln -s ../catalog/language/en-gb/extension/payment/polkadot.php                 ./catalog-language.php
ln -s ../catalog/model/extension/payment/polkadot.php                          ./catalog-model.php
ln -s ../catalog/view/theme/default/template/extension/payment/polkadot.twig   ./catalog-template.twig

ln -s ../catalog/view/javascript/polkadot/DOT.js                               ./DOT.js

exit


ln -s ../catalog/view/theme/default/image/polkadot/*                                          other/image/ajaxm.gif
ln -s ../catalog/view/theme/default/image/polkadot/*                                          other/image/Alzymologist_.png
ln -s ../catalog/view/theme/default/image/polkadot/*                                          other/image/polkadot.webp

















other/js/bundle-polkadot-api.js	ln -s ../upload/catalog/view/javascript/polkadot/*
other/js/bundle-polkadot-extension-dapp.js   ln -s ../upload/catalog/view/javascript/polkadot/*
other/js/bundle-polkadot-keyring.js      ln -s ../upload/catalog/view/javascript/polkadot/*
other/js/bundle-polkadot-types.js      ln -s ../upload/catalog/view/javascript/polkadot/*
other/js/bundle-polkadot-util-crypto.js>   ln -s ../upload/catalog/view/javascript/polkadot/*
other/js/bundle-polkadot-util.js      ln -s ../upload/catalog/view/javascript/polkadot/*
