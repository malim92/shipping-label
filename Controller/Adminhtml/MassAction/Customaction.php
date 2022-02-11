<?php

namespace Com2go\ShippingLabel\Controller\Adminhtml\MassAction;

use Magento\Backend\App\Action;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class CustomAction extends Action
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
      
        $orderIds = $request->getPost('selected', []);
        if (empty($orderIds)) {
            $this->getMessageManager()->addErrorMessage(__('No orders found.'));
            return $this->_redirect('sales/order/index');
        }

        //print_r(orderIds) // Selected Order Ids

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('entity_id', ['in' => $orderIds]);
        foreach ($orderCollection as $order) {
            try {
                $orderId = $order->getId();
                $pdf = new \Zend_Pdf();
                $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
                $page = $pdf->pages[0]; // this will get reference to the first page.
                $style = new \Zend_Pdf_Style();
                $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0));
                $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
                $style->setFont($font,15);
                $page->setStyle($style);
                $x = 30;
                $pageTopalign = 850; //default PDF page height
                $this->y = 850 - 100; //print table row from page top – 100px
                //Draw table header row’s From
                $style->setFont($font,16);
                $page->setStyle($style);
                $page->drawRectangle(30, $this->y + 10, $page->getWidth()-390, $this->y +82, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                $style->setFont($font,14);
                $page->setStyle($style);
                $page->drawText(__("From"), $x + 5, $this->y+65, 'UTF-8');
                $style->setFont($font,11);
                $page->setStyle($style);
                $page->drawText(__("Georgios Andreau"), $x + 5, $this->y+53, 'UTF-8');
                $page->drawText(__("AA PEGASUS NET TRADING LTD"), $x + 5, $this->y+40, 'UTF-8');
                $page->drawText(__("Alexiou Dimara 8"), $x + 5, $this->y+30, 'UTF-8');
                $page->drawText(__("4152 Kato Polemidia, Cyprus"), $x + 5, $this->y+20, 'UTF-8');
                $page->drawText(__("96693968"), $x + 5, $this->y+10, 'UTF-8');
                
                
                //Draw table header row’s Right
                $style->setFont($font,16);
                $page->setStyle($style);
                $page->drawRectangle($page->getWidth()-390, $this->y + 10, $page->getWidth()-30, $this->y +82, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                $style->setFont($font,11);
                $page->setStyle($style);
                $page->drawText(__("Importer's reference ( if any) (tax code/VAT no./ Importer Code"), $x + 195, $this->y+70, 'UTF-8');
                $page->drawText(__("Importer's Telephone/fax/e-mail (if known)"), $x + 195, $this->y+40, 'UTF-8');
                
                $line = $this->y-35;
                //$line -=10;
                $page->drawLine($x + 10, $line, $x + 550, $line );
    
                $line -=20;
                // category of item
                $style->setFont($font,8);
                $page->setStyle($style);
                $page->drawText( 'Category of item', $x + 10, $line, 'UTF-8');
                $page->drawRectangle($x + 10, $line-25, $x + 30, $line-5, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $page->drawText( 'Gift', $x + 40 , $line -20, 'UTF-8');
                $page->drawRectangle($x + 70, $line-25, $x + 90, $line-5, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $page->drawText( 'Document', $x + 100, $line -20, 'UTF-8');
                $page->drawRectangle($x + 150, $line-25, $x + 170, $line-5, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $page->drawText( 'Commercial Sample', $x + 180, $line -20, 'UTF-8');
                $page->drawRectangle($x + 250, $line-25, $x + 270, $line-5, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $page->drawText( 'Returned Goods', $x + 280, $line -20, 'UTF-8');
                $page->drawRectangle($x + 340, $line-25, $x + 360, $line-5, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $page->drawText( 'Other', $x + 370, $line -20, 'UTF-8');
                $page->drawText( 'Office of origin/ Date of posting:', $x + 420, $line-20, 'UTF-8');
                
                //comments
                //$page->drawLine($x + 10, $line-25, $x + 550, $line-55 );
                $page->drawRectangle($x + 10, $line-50, $x + 380, $line-80, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $style->setFont($font,7);
                $page->setStyle($style);
                $page->drawText( 'Comments', $x + 15, $line-60, 'UTF-8');
                
                //signature
                $style->setFont($font,5);
                $page->setStyle($style);
                $page->drawRectangle($x + 380, $line-50, $page->getWidth()-30, $line-80, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $page->drawText( 'I certify that the particulars given in the customs declaration are', $x + 385, $line-55, 'UTF-8');
                $page->drawText( 'correct and that this item does not contain any dangerous article', $x + 385, $line-65, 'UTF-8');
                $page->drawText( 'or articles prohibited ny legisltaion or by postal or customs regulations', $x + 385, $line-75, 'UTF-8');
                $style->setFont($font,10);
                $page->setStyle($style);
                $page->drawText( 'Date and sender signature', $x + 385, $line-90, 'UTF-8');
                
                //attached doc
                $style->setFont($font,8);
                $page->setStyle($style);
                $page->drawText( 'Attached Documents', $x + 10 , $line -90, 'UTF-8');
                $page->drawRectangle($x + 10, $line-100, $x + 30, $line-120, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $page->drawText( 'Licence', $x + 40 , $line -110, 'UTF-8');
                $page->drawRectangle($x + 150, $line-100, $x + 170, $line-120, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $page->drawText( 'Certificate', $x + 180, $line -110, 'UTF-8');
                $page->drawRectangle($x + 240, $line-100, $x + 260, $line-120, \Zend_Pdf_Page::SHAPE_DRAW_STROKE); // table header row
                $page->drawText( 'Invoice', $x + 270, $line -110, 'UTF-8');
                $page->setLineWidth(2.5);
                $page->drawLine($x + 10, $line-130, $x + 550, $line-130 );
                $page->setLineWidth(1);
                $page->drawLine($x + 250, $line-130, $x + 250, $line-230 );
                $style->setFont($font,12);
                $page->setStyle($style);
                $page->drawText( 'To :', $x + 260, $line -145, 'UTF-8');
                
                // customer details
                $custname = $order->getCustomerFirstname(). " ". $order->getCustomerLastname();
                $page->drawText( $custname, $x + 280, $line -145, 'UTF-8');
                $cust_address_array = $order->getShippingAddress()->getStreet();//." ".$order->getShippingAddress()->getRegion();
                //if (empty($cust_address_array[1]) || $cust_address_array[1]=null ) $cust_address_array[1]='';
                $cust_address_street = $cust_address_array[0];//." ".$cust_address_array[1];
                $page->drawText( $cust_address_street, $x + 280, $line -165, 'UTF-8');
                $custaddress = $order->getShippingAddress()->getCity(). " ".$order->getShippingAddress()->getPostcode()." ".$order->getShippingAddress()->getCountryId();
                $page->drawText( $custaddress, $x + 280, $line -185, 'UTF-8');
                
                $fileName = "Cyprus post shipping label - $orderId.pdf";
                $this->fileFactory->create(
                $fileName,
                $pdf->render(),
                \Magento\Framework\App\Filesystem\DirectoryList::POSTPDF, // this pdf will be saved in var directory with the name example.pdf
               'application/pdf'
            );
    
            } catch (\Exception $e) {
                $message = "An unknown error occurred while changing selected orders.";
                $this->getMessageManager()->addErrorMessage(__($message));
            }
        }
            return $this->_redirect('sales/order/index');
        }
}