<?php

namespace FrontendModule\EshopModule;

/**
 * This presenter handle all actions in shopping cart.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class CartPresenter extends BasePresenter
{
    /* \Nette\Http\SessionSection */

    private $eshopSession;

    /* \WebCMS\EshopModule\Doctrine\Order */
    private $order;

    /* Repository */
    private $productRepository;

    protected function startup()
    {
        parent::startup();

        $this->productRepository = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Product');

        $this->eshopSession = $this->session->getSection('eshop'.$this->language->getId());

        if (!$this->eshopSession->offsetExists('order')) {
            $this->order = new \WebCMS\EshopModule\Doctrine\Order();
            $this->saveOrderState();
        } else {
            $this->order = $this->eshopSession->order;
        }

        $this->template->order = $this->order;
    }

    public function actionDefault($id)
    {
        if (array_key_exists('itemId', $_REQUEST)) {
            if (!array_key_exists('variant', $_REQUEST)) {
                $variant = 0;
            } else {
                $variant = $_REQUEST['variant'];
            }

            $this->addCartItem($_REQUEST['itemId'], $_REQUEST['quantity'], $variant);
        }
    }

    public function renderDefault($id)
    {
        $this->template->payments = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Payment')->findBy(array(
        'language' => $this->language,
    ));
        $this->template->shippings = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Shipping')->findBy(array(
        'language' => $this->language,
    ));
        $this->template->id = $id;
    }

    public function createComponentCartForm()
    {
        $form = $this->createForm();

        $form->getElementPrototype()->action = $this->link(':Frontend:Eshop:Cart:default', array(
        'id' => $this->actualPage->getId(),
        'path' => $this->actualPage->getPath(),
        'abbr' => $this->abbr,
        'do' => 'cartForm-submit',
    ));

        $form->addGroup('Delivery');
        $form->addText('firstname', 'Firstname')->setRequired('Please fill in the firstname.')->setAttribute('class', 'form-control');
        $form->addText('lastname', 'Lastname')->setRequired('Please fill in the lastname.')->setAttribute('class', 'form-control');
        $form->addText('email', 'Email')->setRequired('Please fill in the email.')->setAttribute('class', 'form-control')->addRule(\Nette\Forms\Form::EMAIL, 'This is not correct email address.');
        $form->addText('phone', 'Phone')->setRequired('Please fill in the phone.')->setAttribute('class', 'form-control');
        $form->addText('street', 'Street')->setRequired('Please fill in the street.')->setAttribute('class', 'form-control');
        $form->addText('city', 'City')->setRequired('Please fill in the city.')->setAttribute('class', 'form-control');
        $form->addText('postcode', 'Postcode')->setRequired('Please fill in the postcode.')->setAttribute('class', 'form-control');

    // invoice data
    $form->addGroup('Invoice');
        $form->addText('invoiceCompany', 'Company')->setAttribute('class', 'form-control');
        $form->addText('invoiceNo', 'No.')->setAttribute('class', 'form-control');
        $form->addText('invoiceVatNo', 'Tax No.')->setAttribute('class', 'form-control');
        $form->addText('invoiceStreet', 'Street')->setAttribute('class', 'form-control');
        $form->addText('invoiceCity', 'City')->setAttribute('class', 'form-control');
        $form->addText('invoicePostcode', 'Postcode')->setAttribute('class', 'form-control');

        $form->addSubmit('send', 'Send order')->setAttribute('class', 'btn btn-primary btn-lg');

        $form->onSuccess[] = callback($this, 'cartFormSubmitted');

    // account-module integration
    if ($user = $this->getAccount()) {
        $form->setDefaults($user->toArray(true));
    }

        $form->setDefaults($this->order->toArray(true));

        return $form;
    }

    /**
     * Account module integration method.
     */
    private function getAccount()
    {
        $accountSession = $this->session->getSection('accountModule');
        if ($accountSession->offsetExists('user')) {
            $user = $accountSession->user;

            if ($user->getId()) {
                return $user;
            }
        }

        return NULL;
    }

    public function cartFormSubmitted($form)
    {
        $values = $form->getValues();

        $this->order->setFirstname($values->firstname);
        $this->order->setLastname($values->lastname);
        $this->order->setEmail($values->email);
        $this->order->setPhone($values->phone);
        $this->order->setStreet($values->street);
        $this->order->setCity($values->city);
        $this->order->setPostCode($values->postcode);

        $this->order->setInvoiceCompany($values->invoiceCompany);
        $this->order->setInvoiceNo($values->invoiceNo);
        $this->order->setInvoiceVatNo($values->invoiceVatNo);
        $this->order->setInvoiceStreet($values->invoiceStreet);
        $this->order->setInvoiceCity($values->invoiceCity);
        $this->order->setInvoicePostcode($values->invoicePostcode);

        if (array_key_exists('payment', $_POST)) {
            $this->order->setPayment($_POST['payment']);
        } else {
            $this->flashMessage('Please pick payment.', 'info');
        }

        if (array_key_exists('shipping', $_POST)) {
            $this->order->setShipping($_POST['shipping']);
        } else {
            $this->flashMessage('Please pick shipping.', 'info');
        }

        $this->order->getPriceTotal(); // TODO no tohle je trochu blbe volat ne? alespon globalni funkci pro vsechny ceny

    $this->saveOrderState();

        if ($this->requiredFilled($values)) {
            // account module integration
        if ($user = $this->getAccount()) {
            $user = $this->em->getRepository('WebCMS\AccountModule\Doctrine\Account')->find($user->getId());

            $this->order->setAccount($user);
        }

            $this->order->setLanguage($this->language);

        // set default order state
        $status = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\OrderState')->findOneBy(array(
        'language' => $this->language,
        'default' => TRUE,
        ));

            $this->order->setStatus($status);

        // add shipping and payment as order items
        $p = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Payment')->find($this->order->getPayment());

            $payment = new \WebCMS\EshopModule\Doctrine\OrderItem();
            $payment->setName($p->getTitle());
            $payment->setPrice($p->getPrice());
            $payment->setVat($p->getVat());
            $payment->setQuantity(1);

            $s = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Shipping')->find($this->order->getShipping());

            $shipping = new \WebCMS\EshopModule\Doctrine\OrderItem();
            $shipping->setName($s->getTitle());
            $shipping->setPrice($s->getPrice());
            $shipping->setVat($s->getVat());
            $shipping->setQuantity(1);

            $this->order->addPayment($payment);
            $this->order->addShipping($shipping);

        // set product and variant entity
        foreach ($this->order->getItems() as $item) {
            if ($item->getItemId()) {
                $product = $this->productRepository->find($item->getItemId());
                $item->setProduct($product);
            }

            if ($item->getVariant()) {
                $product = $this->productRepository->find($item->getVariant());
                $item->setProductVariant($product);
            }
        }

        // send info email with summary
        $this->sendSummaryEmail($values);

            $this->em->persist($this->order);
            $this->em->flush();
            $this->order = new \WebCMS\EshopModule\Doctrine\Order();
            $this->saveOrderState();

            $this->flashMessage('Order has been sent. On given email has been sent email with summary.', 'success');
        } else {
            $this->flashMessage('Please fill all required data.', 'danger');
        }

        $this->redirectThis();
    }

    public function actionSetPayment($idPayment)
    {
        // add payment as order items
    $p = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Payment')->find($idPayment);

        $payment = new \WebCMS\EshopModule\Doctrine\OrderItem();
        $payment->setName($p->getTitle());
        $payment->setPrice($p->getPrice());
        $payment->setVat($p->getVat());
        $payment->setQuantity(1);

        $this->order->addPayment($payment);

        $this->order->setPayment($idPayment);
        $this->saveOrderState();

        $this->flashMessage('Payment has been setted.', 'success');

        $this->redirectThis();
    }

    public function actionSetShipping($idShipping)
    {
        // add shipping as order item
    $p = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Shipping')->find($idShipping);

        $shipping = new \WebCMS\EshopModule\Doctrine\OrderItem();
        $shipping->setName($p->getTitle());
        $shipping->setPrice($p->getPrice());
        $shipping->setVat($p->getVat());
        $shipping->setQuantity(1);

        $this->order->addShipping($shipping);

        $this->order->setShipping($idShipping);
        $this->saveOrderState();

        $this->flashMessage('Shipping has been setted.', 'success');

        $this->redirectThis();
    }

    private function requiredFilled($values)
    {
        $payment = $this->order->getPayment();
        $shipping = $this->order->getShipping();

        if (!empty($values->firstname) &&
        !empty($values->lastname) &&
        !empty($values->email) &&
        !empty($values->phone) &&
        !empty($values->street) &&
        !empty($values->city) &&
        !empty($values->postcode) &&
        !empty($payment) &&
        !empty($shipping)
    ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function sendSummaryEmail($values)
    {
        // order items
    $items = '';
        foreach ($this->order->getItems() as $item) {
            $items .= $item->getName().' '.$item->getQuantity().' x '.\WebCMS\Helpers\SystemHelper::price($item->getPriceWithVat()).' = '.\WebCMS\Helpers\SystemHelper::price($item->getPriceTotalWithVat()).'<br />';
        }

    // get account URL
    $moduleAccount = $this->em->getRepository('WebCMS\Entity\Module')->findOneBy(array(
        'name' => 'Account',
    ));

        $page = $this->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
        'module' => $moduleAccount,
    ));

        $accountUrl = $this->link('//default', array(
        'path' => $page->getPath(),
        'abbr' => $this->abbr,
    ));

    // email
    $email = new \Nette\Mail\Message();
        $email->addTo($values->email);
        $email->addBcc($this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC)->getValue());
        $email->setFrom($this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC)->getValue());
        $email->setSubject($this->translation['Order was created.']);
        $email->setHtmlBody($this->settings->get('Order saved email', 'eshopModule')->getValue(false, array(
        '[FIRSTNAME]',
        '[LASTNAME]',
        '[EMAIL]',
        '[PHONE]',
        '[STREET]',
        '[CITY]',
        '[POSTCODE]',
        '[TOTAL_PRICE]',
        '[TOTAL_PRICE_WITH_VAT]',
        '[ORDER_ITEMS]',
        '[ACCOUNT_URL]',
        ), array(
        $values->firstname,
        $values->lastname,
        $values->email,
        $values->phone,
        $values->street,
        $values->city,
        $values->postcode,
        \WebCMS\Helpers\SystemHelper::price($this->order->getPriceTotal()),
        \WebCMS\Helpers\SystemHelper::price($this->order->getPriceTotalWithVat()),
        $items,
        $accountUrl,
        )
    ));

        $email->send();
    }

    /**
     * Saves order into Session.
     */
    private function saveOrderState()
    {
        $this->eshopSession->order = $this->order;
    }

    /**
     * Remove item from shopping cart.
     * @param type $itemId
     */
    public function actionDeleteCartItem($itemId, $variant)
    {
        foreach ($this->order->getItems() as $item) {
            if ($itemId === $item->getItemId() && $variant == $item->getVariant()) {
                $this->order->removeItem($item);
            }
        }

        $this->flashMessage('Item has been removed from cart.', 'success');
        $this->redirectThis();
    }

    public function actionSetQuantity($itemId, $quantity, $variant)
    {
        if ($quantity > 0) {
            foreach ($this->order->getItems() as $item) {
                if ($itemId === $item->getItemId() && $variant === $item->getVariant()) {
                    $item->setQuantity($quantity);
                }
            }

            $this->flashMessage('New quantity for item has been set.', 'success');
        } else {
            $this->flashMessage('Quantity must be greater then zero.', 'danger');
        }
        $this->redirectThis();
    }

    /**
     * Add item into shopping cart.
     * @param type $itemId
     * @param type $quantity
     */
    private function addCartItem($itemId, $quantity, $variant)
    {
        if (!$quantity) {
            $quantity = 1;
        }

        $error = false;
        if ($variant === 'error') {
            $snippets = array(
        'snippet--flashMessages' => '<div class="alert alert-danger fade in">'.$this->translation['Please choose product variant.'].'<a href="#" class="close" data-dismiss="alert">×</a></div>',
        );

            $error = true;
        }

        if (!$error) {
            if (!$this->existsInCart($itemId, $variant)) {
                $product = $this->productRepository->find($itemId);

                $variantEntity = $this->productRepository->find($variant);

                $item = new \WebCMS\EshopModule\Doctrine\OrderItem();
                $item->setItemId($itemId);
                $item->setName($product->getTitle());
                $item->setQuantity($quantity);
                $item->setPrice($product->getPrice());
                $item->setVariant($variant);
                $item->setVat($product->getVat());

                if (is_object($variantEntity)) {
                    $item->setName($variantEntity->getTitle());
                    $item->setPrice($variantEntity->getPrice());
                }

                $this->order->addItem($item);

                if ($this->isAjax()) {
                    $snippets = array(
            'snippet--flashMessages' => '<div class="alert alert-success fade in">'.$this->translation['Item has been added to the shopping cart.'].'<a href="#" class="close" data-dismiss="alert">×</a></div>',
            );
                } else {
                    $this->flashMessage('Item has been added to the shopping cart.', 'success');
                }
            } else {
                if ($this->isAjax()) {
                    $snippets = array(
            'snippet--flashMessages' => '<div class="alert alert-danger fade in">'.$this->translation['This item has been already added.'].'<a href="#" class="close" data-dismiss="alert">×</a></div>',
            );
                } else {
                    $this->flashMessage('This item has been already added.', 'danger');
                }
            }
        }

        if ($this->isAjax()) {
            $box = $this->cartBox($this, $this->actualPage, true);
            $snippets['snippet--boxCart'] = $box->__toString();

            $this->payload->snippets = $snippets;
        } else {
            $this->redirectThis();
        }
    }

    /**
     * Checks whether item exists in shopping cart.
     * @param  type    $itemId
     * @return boolean
     */
    private function existsInCart($itemId, $variant)
    {
        foreach ($this->order->getItems() as $item) {
            if ($itemId === $item->getItemId() && $variant === $item->getVariant()) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Redirect to self.
     */
    private function redirectThis()
    {
        $this->redirect(':Frontend:Eshop:Cart:default', array(
        'id' => $this->actualPage->getId(),
        'path' => $this->actualPage->getPath(),
        'abbr' => $this->abbr,
    ));
    }

    /**
     * @param CartPresenter $context
     */
    public function cartBox($context, $fromPage, $initAnimation = false)
    {
        $eshopSession = $context->session->getSection('eshop'.$context->language->getId());
        $order = $eshopSession->order;

        $template = $context->createTemplate();
        $template->initAnimation = $initAnimation;
        $template->setFile('../app/templates/eshop-module/boxes/cartBox.latte');
        $template->link = $context->link(':Frontend:Eshop:Cart:default', array(
	        'id' => $fromPage->getId(),
	        'path' => $fromPage->getPath(),
	        'abbr' => $context->abbr,
	    ));
        $template->items = ($order instanceof \WebCMS\EshopModule\Doctrine\Order ? $order->getItems() : array());

        if (is_object($order)) {
            if ($order->getQuantityTotal() > 0) {
                $template->priceTotal = $order->getPriceTotal();
                $template->priceTotalWithVat = $order->getPriceTotalWithVat();

                $count = 0;
                foreach ($order->getItems() as $item) {
                    if ($item->getType() !== \WebCMS\EshopModule\Doctrine\OrderItem::PAYMENT && $item->getType() !== \WebCMS\EshopModule\Doctrine\OrderItem::SHIPPING) {
                        $count += $item->getQuantity();
                    }
                }

                $template->itemsCount = $count;
            } else {
                $template->priceTotal = 0;
                $template->priceTotalWithVat = 0;
                $template->itemsCount = 0;
            }
        } else {
            $template->priceTotal = 0;
            $template->priceTotalWithVat = 0;
            $template->itemsCount = 0;
        }

        return $template;
    }
}
