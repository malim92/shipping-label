<?php

namespace Com2go\ShippingLabel\Controller\Adminhtml\MassAction;

use Magento\Backend\App\Action;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Com2golabel extends Action
{
    const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * ChangeColor constructor.
     * @param Action\Context $context
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();
        $region = '';
        $orderIds = $request->getPost('selected', []);
        if (empty($orderIds)) {
            $this->getMessageManager()->addErrorMessage(__('No orders found.'));
            return $this->_redirect('sales/order/index');
        }

        //print_r(orderIds) // Selected Order Ids

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('entity_id', ['in' => $orderIds]);
        
        $style = new \Zend_Pdf_Style();
        $x = 10;
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,15);
        $pdf = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $pageCounter = 0;
        $page = $pdf->pages[$pageCounter]; // this will get reference to the first page.
        
        $pageTopalign = 850; //default PDF page height
        $this->y = 850 - 100; //print table row from page top – 100px
        $this->x = 2;
        //Draw table header row’s From
        $block = 120;
        $halfWidth = $page->getWidth()/2; //297.5
        $orderNumber = count ($orderCollection);
        $counter = 1;
        foreach ($orderCollection as $order) {
            try {
                //switch to the right side of the page when orders are more than 8
                if ($counter % 7 == 0 ) {
                    $this->x = $page->getWidth()/2;
                    $this->y = 850 - 100;
                }
                //add new page every 16 orders
                if ($counter % 14 == 0 ) {
                   $pageCounter++;
                   $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
                   $page = $pdf->pages[$pageCounter];
                   $this->y = 850 - 100; //print table row from page top – 100px
                   $this->x = 2;
                }
                $page->setStyle($style);
                $page->drawRectangle($this->x, $this->y-20 , $this->x+297.5, $this->y +100, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                $style->setFont($font,14);
                $page->setStyle($style);
                $page->drawText(__("To : "), $this->x + 5, $this->y+60, 'UTF-8');
                //$line = $this->y-35;
                // customer details
                if (null !== $order->getShippingAddress()->getRegion()) 
                    $region = $order->getShippingAddress()->getRegion();
                    
                $custname = $order->getCustomerFirstname(). " ". $order->getCustomerLastname();
                $page->drawText( $custname, $this->x + 30, $this->y+60, 'UTF-8');
                $cust_address_array = $order->getShippingAddress()->getStreet();//." ".$order->getShippingAddress()->getRegion();
                if (isset($cust_address_array[1])){
                    $cust_address_street = $cust_address_array[0];
                    $cust_second_street = $cust_address_array[1];
                    $page->drawText( $cust_second_street, $this->x + 30, $this->y+20, 'UTF-8');
                }
                else $cust_address_street = $cust_address_array[0];
                //$cust_address_street = $cust_address_array[0];//." ".$cust_address_array[1];
                
                $page->drawText( $cust_address_street, $this->x + 30, $this->y+35, 'UTF-8');
                $custaddress = $order->getShippingAddress()->getCity(). " ".$region." ".$order->getShippingAddress()->getPostcode();
                $countryaddress = $order->getShippingAddress()->getCountryId();
                if (!isset($cust_address_array[1])) {
                    $page->drawText( $custaddress, $this->x + 30, $this->y+15, 'UTF-8');
                    $page->drawText( $countryaddress, $this->x + 30, $this->y, 'UTF-8');
                }
                else {
                    $page->drawText( $custaddress, $this->x + 30, $this->y+5, 'UTF-8');
                    $page->drawText( $countryaddress, $this->x + 30, $this->y-10, 'UTF-8');
                }
                //$page->drawText( $custaddress, $this->x + 25, $this->y+25, 'UTF-8');
                
                //$page->drawText( $countryaddress, $this->x + 25, $this->y+10, 'UTF-8');

                $this->y -= $block;
                $counter++;
    
            } catch (\Exception $e) {
                $message = "An unknown error occurred while changing selected orders.";
                $this->getMessageManager()->addErrorMessage(__($message));
            }
            
        }
        $fileName = "shipping labels.pdf";
        $this->fileFactory->create( $fileName, $pdf->render(), \Magento\Framework\App\Filesystem\DirectoryList::LABELPDF, // this pdf will be saved in var directory with the name example.pdf
       'application/pdf'
            );
        
        return $this->_redirect('https://pegasusdev.com2go.co/pdf/shippinglabel/');
        
    }
}