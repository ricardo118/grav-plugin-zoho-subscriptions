<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Page;
use Grav\Common\Filesystem\Folder;
use Grav\Common\Utils;
use RocketTheme\Toolbox\Event\Event;

require __DIR__ . '/vendor/autoload.php';

use Zoho\Subscription\Api\Customer;
use Zoho\Subscription\Api\Invoice;
use Zoho\Subscription\Api\Card;
use \Doctrine\Common\Cache\FilesystemCache;
use Zoho\Subscription\Api\Subscription;
/**
 * Class ClientPortalPlugin
 * @package Grav\Plugin
 */
class ZohoSubscriptionsPlugin extends Plugin {

    public $cache;
    public $cacheRoute = 'cache://zoho/';

    public $org;
    public $token;
    public $cid;

    public $customers; // Customer Constructor
    public $customer; // Single Customer
    public $invoices; // Invoice Constructor
    public $cards;
    public $subscriptions;

    public $cardList;

    public $unPaidInvoices = [];
    public $paidInvoices = [];

    public $lastPaidInvoice;
    public $invoicePdf;

    protected $invoiceDownloadRoute = '/admin/zoho_portal/invoice/download';
    protected $payNowRoute = '/admin/zoho_portal/pay_now';
    protected $editCardRoute = '/admin/zoho_portal/edit_card';

    public static function getSubscribedEvents() {

        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onFormProcessed' => ['onFormProcessed', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onPageInitialized' => ['onPageInitialized', 0],
            'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0]
        ];

    }

    public function onPluginsInitialized() {

        if ($this->isAdmin()) {

            if ($this->initialize()) { // try to initialize

                $this->getCustomer();

                if ($this->isClientPortal()) {
                    $this->enable([
                        'onAssetsInitialized' => ['onAssetsInitialized', 0]
                    ]);

                    $this->getInvoices();
                    $this->getSubscription();
                    $this->getCustomerBankDetails();

                    $this->invoiceDownload();
                    $this->payNowButton();
                    $this->editCard();
                }

                $this->enable([
                    'onAdminMenu' => ['onAdminMenu', 0],
                ]);
            }

        }
    }

    public function onAssetsInitialized() {

        $plugins = $this->grav['plugins'];
        if (is_null($plugins->get('squidmin')) || !$this->config->get('plugins')['squidmin']['enabled']){
            $this->grav['assets']->addCss('user/plugins/zoho-subscriptions/css/style_default.css', 1);
        }
        $this->grav['assets']->addCss('user/plugins/zoho-subscriptions/css/style.css');
        $this->grav['assets']->addJs('user/plugins/zoho-subscriptions/js/zoho-subscriptions.js');
    }

    public function onTwigTemplatePaths()
    {
        $lockdown = $this->config->get('plugins.zoho-subscriptions.lockdown');

        if ($lockdown) {
            $twig = $this->grav['twig'];
            $twig->twig_paths[] = __DIR__ . '/templates';
        }
    }

    public function onAdminTwigTemplatePaths($event) {

        $event['paths'] = array_merge($event['paths'], [__DIR__ . '/admin/templates']);
        return $event;
    }

    public function onAdminMenu() {

        $this->grav['twig']->plugins_hooked_nav['Portal'] = ['route' => 'zoho_portal', 'icon' => ' fa-th', 'authorize' => 'admin.zoho_subscriptions'];
    }

    public function onPageInitialized() {

        $lockdown = $this->config->get('plugins.zoho-subscriptions.lockdown');
        if (!$this->isAdmin() and $lockdown) {
            $this->setUnauthorizedPage();
        }
    }

    public function setUnauthorizedPage()
    {

        $route = '/lockdown';

        /** @var Pages $pages */
        $pages = $this->grav['pages'];
        $page = $pages->dispatch($route);

        if (!$page) {
            $page = new Page;
            $page->init(new \SplFileInfo(__DIR__ . '/pages/lockdown.md'));
            $page->template('lockdown');
            $page->slug(basename($route));

            $pages->addPage($page, $route);
        }

        unset($this->grav['page']);
        $this->grav['page'] = $page;
    }
    /**
     *  START OF CUSTOM FUNCTIONS
     * @param Event $event
     */
    public function onFormProcessed(Event $event) {

        $action = $event['action'];
        $post = !empty($_POST) ? $_POST : [];
        $messages = $this->grav['messages'];

        switch ($action) {
            case 'edit_details':
                try {
                    $this->customers->updateCustomer($this->cid, $post['data']);
                    foreach($post['data'] as $field => $value) {
                        $this->grav['twig']->twig_vars['customer']->$field = $value;
                    }
                    $messages->add($this->grav['language']->translate('PLUGIN_ZOHO_SUBSCRIPTIONS.SUCCESS'), 'info');
                } catch (\Exception $e) {
                    $messages->add($this->grav['language']->translate('PLUGIN_ZOHO_SUBSCRIPTIONS.FAILED'), 'info');
                }
                break;
        }
    }

    public static function getMyDefault($field) {

        if (!isset(Grav::instance()['twig']->twig_vars['customer']->$field)) {
            return;
        }
        return Grav::instance()['twig']->twig_vars['customer']->$field;
    }

    public static function getMyDefaultAddress($field) {

        if (!isset(Grav::instance()['twig']->twig_vars['customer']->billing_address[$field])) {
            return;
        }

        return Grav::instance()['twig']->twig_vars['customer']->billing_address[$field];
    }

    private function isClientPortal() {

        $path=$this->grav['uri'];

        if (strpos($path, 'zoho_portal') !== false) {

            return true;
        }
    }

    public function initialize()
    {

        $this->cid = $this->config->get('plugins.zoho-subscriptions.customerid');
        $this->org = $this->config->get('plugins.zoho-subscriptions.org_id');
        $this->token = $this->config->get('plugins.zoho-subscriptions.auth_token');

        if (is_null($this->cid) || is_null($this->org) || is_null($this->token)) {
            $this->grav['messages']->add('Zoho Subscriptions Plugin not configured <a href="'.$this->config->get('plugins.admin.route').'/plugins/zoho-subscriptions'.'" class="button button-small secondary">Configure Now</a>', 'error');
            return false;
        }

        $this->setCache();

        $this->customers = new Customer($this->token,$this->org,$this->cache);
        $this->invoices = new Invoice($this->token,$this->org,$this->cache);
        $this->cards = new Card($this->token,$this->org,$this->cache);
        $this->subscriptions = new Subscription($this->token,$this->org,$this->cache);

        return true;
    }

    private function setCache() {

        $cache_dir   = $this->grav['locator']->findResource('cache://zoho', true, true);
        $this->cache = new FilesystemCache($cache_dir);

    }

    private function deleteCache() {

        Folder::delete($this->cacheRoute);

    }

    private function getCustomer() {

        $this->customer = (object) $this->customers->getCustomerById($this->cid);
        $this->grav['twig']->twig_vars['customer'] = $this->customer;

        if ($this->customer->outstanding > 0) {
            $c = $this->customer;
            $inv = end($this->getInvoices()['overdue']);
            $diff = $this->diff($inv['due_date'], 'days');
            $this->grav['messages']->add(sprintf($this->grav['language']->translate('PLUGIN_ZOHO_SUBSCRIPTIONS.OUTSTANDING_MESSAGE'), $inv['currency_symbol'].$c->outstanding, $diff, 7-$diff ), 'error');
        }

    }

    private function diff($date1, $length, $date2 = null) {

        if (is_null($date2)) {
            $date2 = date("Y-m-d");
        }

        $d1 = new \DateTime($date1);
        $d2 = new \DateTime($date2);

        return $d1->diff($d2)->{$length};
    }

    private function getInvoices() {
        $twig = $this->grav['twig'];
        $api = $this->invoices->listInvoicesByCustomer($this->cid, 'All');
        $invoices = array();

        foreach ($api as $invoice) {
            if (!isset($invoices[$invoice['status']])) {
                $invoices[$invoice['status']] = [];
            }
            array_push($invoices[$invoice['status']], $invoice);
        }

        $twig->twig_vars['invoices'] = (object) $invoices;
        $twig->twig_vars['invoice'] = $this->invoices->getInvoice($invoices['paid'][0]['invoice_id']);

        return $invoices;
    }

    private function getCustomerBankDetails() {

        $this->cardList = $this->cards->getCardListByCustomerId($this->cid);
        $this->grav['twig']->twig_vars['cards'] = $this->cardList;
    }

    private function getSubscription() {

        $list = $this->subscriptions->listSubscriptionsByCustomer($this->cid);
        $this->grav['twig']->twig_vars['subscriptions'] = $list;

        foreach($list as $subscription) {

            if ($this->diff($subscription['next_billing_at'], 'days') <= 7) {
                $this->grav['messages']->add(sprintf($this->grav['language']->translate('PLUGIN_ZOHO_SUBSCRIPTIONS.RENEWAL_UPCOMING'), $subscription['plan_name'], $this->diff($subscription['next_billing_at'], 'days')), 'info');
            }
        }
    }

    private function invoiceDownload() {

        $uri = $this->grav['uri'];

        if (strpos($uri->path(), $this->invoiceDownloadRoute) === false) {

            return;
        }

        $this->invoices->getInvoicePdf($uri->param('id'));
    }
    private function editCard() {

        $uri = $this->grav['uri'];

        $post = !empty($_POST) ? $_POST : [];
        $messages = $this->grav['messages'];

        if (strpos($uri->path(), $this->editCardRoute) === false) {

            return;
        }

        $this->cards->updateCardDetails($this->cid, $this->cardList[0]['card_id'], $post['data']);
        foreach($post['data'] as $field => $value) {
            $this->grav['twig']->twig_vars['customer']->$field = $value;
        }
        $this->deleteCache();

    }

    private function payNowButton() {

        $uri = $this->grav['uri'];

        if (strpos($uri->path(), $this->payNowRoute) === false) {

            return;
        }

        $cardId = (object) array('card_id' => $this->cardList[0]['card_id']);
        foreach ($this->unPaidInvoices as $unpaidInvoice) {
            $this->cards->chargeCard($unpaidInvoice['invoice_id'], $cardId);
        }
        $this->deleteCache();
        $messages = $this->grav['messages'];
        $messages->add($this->grav['language']->translate('PLUGIN_ZOHO_SUBSCRIPTIONS.PAYMENT_SUCCESS'), 'info');
        $this->grav->redirect('/admin/zoho_portal');
    }

}
