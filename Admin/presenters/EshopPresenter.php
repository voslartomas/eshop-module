<?php

namespace AdminModule\EshopModule;

/**
 * Description of PagePresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class EshopPresenter extends \AdminModule\BasePresenter
{
    private $repository;

    private $page;

    protected function startup()
    {
        parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\PageModule\Entity\Page');
    }

    protected function beforeRender()
    {
        parent::beforeRender();
    }

    public function actionDefault($idPage)
    {
    }

    public function renderDefault($idPage)
    {
        $this->reloadContent();

        $query = $this->em->createQuery('SELECT COUNT(u.id) FROM WebCMS\EshopModule\Doctrine\Category u WHERE u.language = ?1')->setParameter(1, $this->state->language);
        $count = $query->getSingleScalarResult();

        $countOf['categories'] = $count - 1;

        $query = $this->em->createQuery('SELECT COUNT(u.id) FROM WebCMS\EshopModule\Doctrine\Product u WHERE u.language = ?1')->setParameter(1, $this->state->language);
        $count = $query->getSingleScalarResult();

        $countOf['products'] = $count;

        $orders = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Order')->findBy(array(
            'language' => $this->state->language,
        ));

        $totalPrice = 0;
        foreach ($orders as $order) {
            $totalPrice += $order->getPriceTotal();
        }

        $countOf['orders'] = count($orders);
        $countOf['ordersTotalPrice'] = $totalPrice;

        $this->template->active = 'statistics';
        $this->template->counts = $countOf;
        $this->template->idPage = $idPage;
    }

    public function actionRts($idPage)
    {
    }

    public function renderRts($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
        $this->template->active = 'rts';
    }
}
