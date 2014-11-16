<?php

namespace WebCMS\EshopModule;

/**
 * Description of Page
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Eshop extends \WebCMS\Module
{
    protected $name = 'Eshop';

    protected $author = 'Tomáš Voslař';

    protected $presenters = array(
        array(
            'name' => 'Eshop',
            'frontend' => true,
            'parameters' => false,
            ),
        array(
            'name' => 'Categories',
            'frontend' => true,
            'parameters' => true,
            ),
        array(
            'name' => 'Products',
            'frontend' => false,
            ),
        array(
            'name' => 'Cart',
            'frontend' => true,
            'parameters' => false,
            ),
        array(
            'name' => 'Settings',
            'frontend' => false,
            ),
        array(
            'name' => 'Parameters',
            'frontend' => false,
            ),
        array(
            'name' => 'RestApi',
            'frontend' => true,
            'parameters' => true,
            ),
    );

    protected $searchable = true;

    protected $params = array(

    );

    public function __construct()
    {
        $this->addBox('Shopping cart', 'Cart', 'cartBox', 'Eshop');
        $this->addBox('Categories list box', 'Categories', 'listBox', 'Eshop');
        $this->addBox('Categories list box 2', 'Categories', 'listBox2', 'Eshop');
    }

    public function search(\Doctrine\ORM\EntityManager $em, $phrase, \WebCMS\Entity\Language $language)
    {
        $qb = $em->createQueryBuilder();

        $query = $qb->select('p')
            ->from('WebCMS\EshopModule\Doctrine\Product', 'p')
            ->andwhere('p.title LIKE :word')
            ->andWhere('p.language = :language')
            ->setParameter('word', '%'.$phrase.'%')
            ->setParameter('language', $language)
            ->groupBy('p.id')
            ->getQuery();

        $pages = $query->getResult();

        $categoriesPage = $em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Eshop',
            'presenter' => 'Categories'
        ));

        $results = array();
        foreach ($pages as $r) {
            $category = $r->getCategories()[0];
            $url = ($language->getDefaultFrontend() ? '' : $language->getAbbr().'/').$categoriesPage->getSlug().'/'.$category->getSlug().'/'.$r->getSlug();

            $result = new \WebCMS\SearchModule\SearchResult();
            $result->setTitle($r->getTitle().' '.\WebCMS\Helpers\SystemHelper::price($r->getPriceWithVat()));
            $result->setUrl($url);
            $result->setPerex(substr(strip_tags($r->getDescription()), 0, 300));
            $result->setRate($query->getHint($phrase));

            $results[] = $result;
        }

        return $results;
    }
}
