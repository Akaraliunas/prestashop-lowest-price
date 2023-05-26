<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class LowestCombinationPrice extends Module
{
    public function __construct()
    {
        $this->name = 'lowestcombinationprice';
        $this->tab = 'pricing_promotion';
        $this->version = '1.0.0';
        $this->author = 'Aivaras Karaliūnas';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Lowest Price', [], 'Modules.Lowestcombinationprice.Admin');
        $this->description = $this->trans('Display the lowest price for product with combinations.', [], 'Modules.Lowestcombinationprice.Admin');
    }

    public function install()
    {
        if (
            !parent::install() ||
            !$this->registerHook('displayProductPriceBlock') ||
            !$this->registerHook('displayHeader')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * Function modifies product with combinations price display
     *
     * @param array $params
     * @return void
     */
    public function hookDisplayProductPriceBlock($params)
    {
        $pages = ['index', 'category'];
        $productId = (int) $params['product']['id_product'];
        $product = new Product($productId);
        $combinations = $product->getAttributeCombinations(
            $this->context->language->id
        );

        if (
            count($combinations) > 0
            && (in_array($this->context->controller->php_self, $pages))
        ) {
            $lowestPrice = $this->getLowestCombinationPrice($product, $combinations);

            if ($lowestPrice && $params['type'] == 'custom_price') {
                $this->context->smarty->assign('lowest_price', $lowestPrice);
                $this->context->smarty->assign('product', $params['product']);

                return $this->fetch('module:lowestcombinationprice/views/templates/front/custom_price.tpl');
            }
        }

        if ($params['type'] == 'old_price' && !$combinations) {
            $this->context->smarty->assign('product', $params['product']);

            return $this->fetch('module:lowestcombinationprice/views/templates/front/price.tpl');
        }
    }

    /**
     * Inject css file in all pages
     *
     * @return void
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->registerStylesheet('modules-lowestcombinationprice', 'modules/' . $this->name . '/views/css/lowestcombinationprice.css');
    }

    /**
     * Returns lowest price from a given product combinations
     *
     * @param Product $product
     * @param array $combinations
     *
     * @return float|null
     */
    public function getLowestCombinationPrice($product, $combinations)
    {
        $lowestPrice = null;

        foreach ($combinations as $combination) {
            $combinationId = $combination['id_product_attribute'];
            $combinationPrice = $product->getPrice($tax = true, $combinationId, 2, null, false, true);

            if ($lowestPrice === null || $combinationPrice < $lowestPrice) {
                $lowestPrice = $combinationPrice;
            }
        }

        return $lowestPrice;
    }
}
