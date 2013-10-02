<?php

namespace AdminModule;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CategoryRepository extends NestedTreeRepository{
	
	public function getTreeForSelect($order = NULL, $where = NULL){
		$qb = $this->_em->createQueryBuilder();
		
		if($order){
			foreach($order as $o){
				$qb->addOrderBy('l.' . $o['by'], $o['dir']);
			}
		}
		
		if($where){
			foreach($where as $w){
				$qb->andWhere('l.' . $w);
			}
		}
		
		$tree = $qb->select('l')->from("WebCMS\EshopModule\Doctrine\Category", 'l')->getQuery()->getResult();

		$array = array();
		foreach($tree as $node){
			
			$array[$node->getId()] = str_repeat("-", $node->getLevel()) . $node->getTitle();
		}

		return $array;
	}	
}