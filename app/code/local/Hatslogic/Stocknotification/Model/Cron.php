<?php
class Hatslogic_Stocknotification_Model_Cron extends Mage_Core_Model_Abstract
{
    public function sendoutofstocknotification()
    {
        $_productCollection = Mage::getModel('catalog/product')
                        ->getCollection()
                                                ->addAttributeToFilter('type_id', 'simple')
                        ->addAttributeToSort('created_at', 'DESC')
                        ->addAttributeToSelect('*')
                        ->load();

        $products = '<table border="1" style="padding: 0;margin:0;width: 100%;text-align: center;">';
        $products .= '<thead><th>Sl No</th><th>Product</th><th>SKU</th><th>Stock</th></thead>';
        $products .= '<tbody>';
        $i = 1;
        $qty_of_items = Mage::getStoreConfig('stocknotification/general/qty_of_items') ? Mage::getStoreConfig('stocknotification/general/qty_of_items') : 0;
         
        foreach ($_productCollection as $_product) {
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
            $qty = $stock->getQty();
            if (!$_product->hasOptions() && $qty <= $qty_of_items) {
                $products .= '<tr><td>'.$i.'</td><td style="text-align:left;padding-left:10px !important">'.$_product->getName().'</td><td style="text-align:left;padding-left:10px !important">'.$_product->getSku().'</td><td>'.round($qty).'</td></tr>';
                $i++;
            }
        }
        $products .= '</tbody><table>';
        $emailTemplate  = Mage::getModel('core/email_template');
        $emailTemplate->loadDefault('custom_outofstock_tpl');
        $emailTemplate->setTemplateSubject('Out of Stock Products List');

        // Get General email address (Admin->Configuration->General->Store Email Addresses)
        $salesData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
        $salesData['name'] = Mage::getStoreConfig('trans_email/ident_general/name');

        // Get Customer Support email address (Admin->Configuration->General->Store Email Addresses)
        $fromemail = Mage::getStoreConfig('trans_email/ident_support/email');
        $fromname = Mage::getStoreConfig('trans_email/ident_support/name');

        $emailTemplate->setSenderName($fromname);
        $emailTemplate->setSenderEmail($fromemail);

        $emailTemplateVariables['products']  = $products;
        //$emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        if ($emailTemplate->send($salesData['email'], $salesData['name'], $emailTemplateVariables)) {
            $i= $i-1;
            echo "A list of ". $i .' low stock product is success fully Send'.'</br>';
            echo "========================PRODUCTS=========================="."</br>";
            echo $products;
        }
    }
}
