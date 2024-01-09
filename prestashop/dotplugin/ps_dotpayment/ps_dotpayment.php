<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */


use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_Dotpayment extends PaymentModule
{
    const FLAG_DISPLAY_PAYMENT_INVITE = 'DOT_PAYMENT_INVITE';

    protected $_html = '';
    protected $_postErrors = [];

    public $details;
    public $daemon;
    public $address;
    public $extra_mail_vars;
    /**
     * @var int
     */
    public $is_eu_compatible;
    /**
     * @var false|int
     */
    public $reservation_days;

    public function __construct()
    {
        $this->name = 'ps_dotpayment';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];
        $this->author = 'PrestaShop';
        $this->controllers = ['payment', 'validation'];
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

/*
        $config = Configuration::getMultiple(['DOT_DETAILS', 'DOTONER', 'DOT_ADDRESS', 'DOT_RESERVATION_DAYS']);
        if (!empty($config['DOTONER'])) {
            $this->oner = $config['DOToNER'];
        }
        if (!empty($config['DOT_DETAILS'])) {
            $this->details = $config['DOT_DETAILS'];
        }
        if (!empty($config['DOT_ADDRESS'])) {
            $this->address = $config['DOT_ADDRESS'];
        }
        if (!empty($config['DOT_RESERVATION_DAYS'])) {
            $this->reservation_days = $config['DOT_RESERVATION_DAYS'];
        }
*/

        $config = Configuration::getMultiple(['DOT_DAEMON']);
        if (!empty($config['DOT_DAEMON'])) $this->daemon = $config['DOT_DAEMON'];


        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('DOT payment', [], 'Modules.Dotpayment.Admin');
        $this->description = $this->trans('Accept DOT payments by displaying your account details during the checkout.', [], 'Modules.Dotpayment.Admin');
        $this->confirmUninstall = $this->trans('Are you sure about removing these details?', [], 'Modules.Dotpayment.Admin');

//        if ((!isset($this->oner) || !isset($this->details) || !isset($this->address)) && $this->active) {
//            $this->warning = $this->trans('Account oner and account details must be configured before using this module.', [], 'Modules.Dotpayment.Admin');
//        }

        if ((!isset($this->daemon)) && $this->active) {
            $this->warning = $this->trans('Daemon must be configured before using this module.', [], 'Modules.Dotpayment.Admin');
        }

        if (!count(Currency::checkPaymentCurrencies($this->id)) && $this->active) {
            $this->warning = $this->trans('No currency has been set for this module.', [], 'Modules.Dotpayment.Admin');
        }

        $this->extra_mail_vars = [
            '{dot_daemon}' => $this->daemon,
//            '{dot_details}' => nl2br($this->details ?: ''),
//            '{dot_address}' => nl2br($this->address ?: ''),
        ];



/*
$e=explode(' ',"PS_OS_CHEQUE PS_OS_PAYMENT PS_OS_PREPARATION PS_OS_SHIPPING PS_OS_DELIVERED PS_OS_CANCELED PS_OS_REFUND PS_OS_ERROR PS_OS_OUTOFSTOCK PS_OS_BANKWIRE PS_OS_PAYPAL PS_OS_WS_PAYMENT");
$s=""; foreach($e as $l) $s.="<br>$l = ".Configuration::get($l);
die($s
// "PS_OS_BANKWIRE". Configuration::get('PS_OS_BANKWIRE')
);

PS_OS_CHEQUE = 1
PS_OS_PAYMENT = 2
PS_OS_PREPARATION = 3
PS_OS_SHIPPING = 4
PS_OS_DELIVERED = 5
PS_OS_CANCELED = 6
PS_OS_REFUND = 7
PS_OS_ERROR = 8
PS_OS_OUTOFSTOCK = 9
PS_OS_BANKWIRE = 10
PS_OS_PAYPAL =
PS_OS_WS_PAYMENT = 11
*/


    }

    public function install()
    {
        Configuration::updateValue(self::FLAG_DISPLAY_PAYMENT_INVITE, true);
        if (!parent::install()
            || !$this->registerHook('displayPaymentReturn')
            || !$this->registerHook('paymentOptions')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if ( !Configuration::deleteByName('DOT_CUSTOM_TEXT')
//                || !Configuration::deleteByName('DOT_DETAILS')
                || !Configuration::deleteByName('DOT_DAEMON')
//                || !Configuration::deleteByName('DOT_ADDRESS')
//                || !Configuration::deleteByName('DOT_RESERVATION_DAYS')
                || !Configuration::deleteByName(self::FLAG_DISPLAY_PAYMENT_INVITE)
                || !parent::uninstall()) {
            return false;
        }

        return true;
    }

    protected function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue(
                self::FLAG_DISPLAY_PAYMENT_INVITE,
                Tools::getValue(self::FLAG_DISPLAY_PAYMENT_INVITE)
            );

            if (!Tools::getValue('DOT_DAEMON')) {
                $this->_postErrors[] = $this->trans(
                    'Url daemon is required.',
                    [],
                    'Modules.Dotpayment.Admin'
                );
            }

        }
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('DOT_DAEMON', Tools::getValue('DOT_DAEMON'));

            $custom_text = [];
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                if (Tools::getIsset('DOT_CUSTOM_TEXT_' . $lang['id_lang'])) {
                    $custom_text[$lang['id_lang']] = Tools::getValue('DOT_CUSTOM_TEXT_' . $lang['id_lang']);
                }
            }
            Configuration::updateValue('DOT_CUSTOM_TEXT', $custom_text);
        }
        $this->_html .= $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Global'));
    }

    protected function _displayDot()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }

        $this->_html .= $this->_displayDot();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) return [];

        $cart = $params['cart'];
        if (false === Validate::isLoadedObject($cart) || false === $this->checkCurrency($cart)) return [];

        $this->smarty->assign(
            $this->getTemplateVarInfos()
        );

//        if (empty($params['order'])) { die("NP ");  }
//        $order = $params['order'];
//	    die("<pre>".print_r($params['cart'],1));

/*
        if ($order->getOrderPaymentCollection()->count()) {
            $orderPayment = $order->getOrderPaymentCollection()->getFirst();
            $transaction = $orderPayment->transaction_id;
	    die("<pre>".print_r($order,1));
        } else 
*/
// die("NOT");

        $newOption = new PaymentOption();
        $newOption ->setModuleName($this->name)
                ->setLogo(_MODULE_DIR_ . '/ps_dotpayment/views/img/polkadot.png')
                ->setCallToActionText($this->trans('Pay by DOT', [], 'Modules.Dotpayment.Shop'))
                ->setAction( $this->context->link->getModuleLink($this->name, 'validation', [], true) )
                ->setAdditionalInformation($this->fetch('module:ps_dotpayment/views/templates/front/dotpay.tpl'))
//		->setForm($this->context->smarty->fetch('module:ps_dotpayment/views/templates/DOT.tpl'))
/*
		->setInputs([
            'token' => [
                'name' => 'token',
                'type' => 'text',
                'value' => '[5cbfniD+gEV<59lYbG/,3VmHiE<U46;#G9*#NP#X.FA§]sb%ZG?5Q{xQ4#VM|7',
            ],
        ])
*/
	;
        return [ $newOption ];
    }


    public function hookDisplayPaymentReturn($params)
    {

//     $this->context->controller->addJS([   $this->module->getPathUri() . 'QQQQQQQQQQQ.js'   ]);
// die('######################');

        if (!$this->active || !Configuration::get(self::FLAG_DISPLAY_PAYMENT_INVITE)) {
            return;
        }

        $dotDaemon = $this->daemon;
        if (!$dotDaemon) {
            $dotDaemon = '___________';
        }

        $totalToPaid = $params['order']->getOrdersTotalPaid() - $params['order']->getTotalPaid();
        $this->smarty->assign([
            'shop_name' => $this->context->shop->name,
            'total' => $this->context->getCurrentLocale()->formatPrice(
                $totalToPaid,
                (new Currency($params['order']->id_currency))->iso_code
            ),
            'dotDetails' => $dotDetails,
            'dotAddress' => $dotAddress,
            'dotDaemon' => $dotDaemon,
            'status' => 'ok',
            'reference' => $params['order']->reference,
            'contact_url' => $this->context->link->getPageLink('contact', true),
        ]);

        return $this->fetch('module:ps_dotpayment/views/templates/hook/payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Account details', [], 'Modules.Dotpayment.Admin'),
                    'icon' => 'icon-envelope',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('MAccount oner', [], 'Modules.Dotpayment.Admin'),
                        'name' => 'DOT_DAEMON',
                        'required' => true,
                    ],

/*
                    [
                        'type' => 'text',
                        'label' => $this->trans('HAccount oner', [], 'Modules.Dotpayment.Admin'),
                        'name' => 'HDOTONER',
                        'required' => true,
                    ],


                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Account details', [], 'Modules.Dotpayment.Admin'),
                        'name' => 'DOT_DETAILS',
                        'desc' => $this->trans('Such as bank branch, IBAN number, BIC, etc.', [], 'Modules.Dotpayment.Admin'),
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Bank address', [], 'Modules.Dotpayment.Admin'),
                        'name' => 'DOT_ADDRESS',
                        'required' => true,
                    ],
*/
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];
        $fields_form_customization = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Customization', [], 'Modules.Dotpayment.Admin'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Reservation period', [], 'Modules.Dotpayment.Admin'),
                        'desc' => $this->trans('Number of days the items remain reserved', [], 'Modules.Dotpayment.Admin'),
                        'name' => 'DOT_RESERVATION_DAYS',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Information to the customer', [], 'Modules.Dotpayment.Admin'),
                        'name' => 'DOT_CUSTOM_TEXT',
                        'desc' => $this->trans('Information on the bank transfer (processing time, starting of the shipping...)', [], 'Modules.Dotpayment.Admin'),
                        'lang' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Display the invitation to pay in the order confirmation page', [], 'Modules.Dotpayment.Admin'),
                        'name' => self::FLAG_DISPLAY_PAYMENT_INVITE,
                        'is_bool' => true,
                        'hint' => $this->trans('Your country\'s legislation may require you to send the invitation to pay by email only. Disabling the option will hide the invitation on the confirmation page.', [], 'Modules.Dotpayment.Admin'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->trans('Yes', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->trans('No', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure='
            . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form, $fields_form_customization]);
    }

    public function getConfigFieldsValues()
    {
        $custom_text = [];
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $custom_text[$lang['id_lang']] = Tools::getValue(
                'DOT_CUSTOM_TEXT_' . $lang['id_lang'],
                Configuration::get('DOT_CUSTOM_TEXT', $lang['id_lang'])
            );
        }

        return [
            'DOT_DAEMON' => Tools::getValue('DOT_DAEMON', $this->details),
            'DOT_CUSTOM_TEXT' => $custom_text,
            self::FLAG_DISPLAY_PAYMENT_INVITE => Tools::getValue(
                self::FLAG_DISPLAY_PAYMENT_INVITE,
                Configuration::get(self::FLAG_DISPLAY_PAYMENT_INVITE)
            ),
        ];
    }

    public function getTemplateVarInfos()
    {
        $cart = $this->context->cart;

// die("<pre>".print_r($cart,1));


        $total = sprintf(
            $this->trans('%1$s (tax incl.)', [], 'Modules.Dotpayment.Shop'),
            $this->context->getCurrentLocale()->formatPrice($cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code)
        );

        $dotDaemon = $this->daemon;
        if (!$dotDaemon) {
            $dotDaemon = '___________';
        }

        $dotCustomText = Tools::nl2br(Configuration::get('DOT_CUSTOM_TEXT', $this->context->language->id));
        if (empty($dotCustomText)) {
            $dotCustomText = '';
        }

        return [
	    'module_name' => $this->name,
	    'module_host' => $this->_path . "views",
	    'id' => $cart->id,
	    'shop_id' => $cart->shop_id,
//	    'products' => sizeof($cart->'_products:protected'),
//	    'products' => sizeof($cart->_products),
            'total' => $total,
            'dotDaemon' => $dotDaemon,
            'dotCustomText' => $dotCustomText,
        ];
    }
}
