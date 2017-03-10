<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class Entity {
	protected $_class_vars = null;
	protected $_serviceLocator;
	protected $_em = null;
	protected $_classMetaData = null;
	protected static $_entity_id_backup;
	
	
	protected $_gridOriginalColumns = false;
    
    protected $_gridReplaceColumns = false;
	
	public function __construct(array $options = array()) {
	}
	
	/**
	 * @ORM\PrePersist
	 */
	final public function onPrePersist() {
		if (! $this->isCurrentNamespace ()) {
			$current_date_time = new \DateTime();
		    $current_user_id = $this->getCurrentUser()->getUserId();
		    if($this->hasVariable('created_at')){
		        $this->set('created_at', $current_date_time);
		    }
		    if($this->hasVariable('created_by')){
		    	$this->set('created_by', $current_user_id);
		    }
		    if($this->hasVariable('last_updated_at')){
		    	$this->set('last_updated_at', $current_date_time);
		    }
			if($this->hasVariable('last_updated_by')){
		    	$this->set('last_updated_by', $current_user_id);
		    }
		} 
	}
	
	/**
	 * @ORM\PostPersist
	 */
	final public function onPostPersist() {
		
	}
	
	/**
	 * @ORM\PreUpdate
	 */
	final public function onPreUpdate() {
		if (! $this->isCurrentNamespace ()) {
			$current_date_time = new \DateTime();
			$current_user_id = $this->getCurrentUser()->getUserId();
			if($this->hasVariable('last_updated_at')){
				$this->set('last_updated_at', $current_date_time);
			}
			if($this->hasVariable('last_updated_by')){
				$this->set('last_updated_by', $current_user_id);
			}
		}
	}
	
	/**
	 * @ORM\PreRemove
	 */
	final public function onPreRemove() {
		
	}
	/**
	 * @ORM\PostRemove
	 */
	final public function onPostRemove() {
		
	}
	
	
	final private function _setClassVars() {
		$this->_class_vars = array_keys ( get_object_vars ( $this ) );
		$this->_class_vars = array_diff ( $this->_class_vars, array_keys ( get_class_vars ( __CLASS__ ) ) );
		return $this;
	}
	
	final private function _getClassVars() {
		if ($this->_class_vars == null) {
			$this->_setClassVars ();
		}
		return $this->_class_vars;
	}
	
        public function get($var) {
		$_class_vars = $this->_getClassVars ();
		if (in_array ( $var, $_class_vars )) {
			return $this->{$var};
		}
		throw new \Exception ( "$var : No such variable declared" );
	}
	
	
        public function set($var, $value) {
		$_class_vars = $this->_getClassVars ();
		if (in_array ( $var, $_class_vars )) {
			return $this->{$var} = $value;
		}
		throw new \Exception ( "$var : No such variable declared" );
	}
	
        final public function __call($method, $arguments) {
		$type = substr ( $method, 0, 3 );
		$classMethod = substr ( $method, 3 );
		$variableName = $this->_createVariable ( $classMethod );
		$_class_vars = $this->_getClassVars ();
		if (in_array ( $variableName, $_class_vars )) {
			if ($type == "get") {
				return $this->{$variableName};
			} elseif ($type == "set") {
				$this->{$variableName} = isset ( $arguments [0] ) ? $arguments [0] : "";
				return $this;
			} else {
				throw new \Exception ( 'Invalid Method: ' . $method . '()' );
			}
		} else {
			throw new \Exception ( 'Invalid Property: ' . $variableName );
		}
	}
	
        private function _createVariable($method) {
		return substr ( strtolower ( preg_replace ( '/[A-Z]/', "_$0", $method ) ), 1 );
	}
	
        final public function getArrayCopy() {
		$modelArray = array ();
		$_class_vars = $this->_getClassVars ();
		$_object_vars = get_object_vars ( $this );
		foreach ( $_object_vars as $key => $value ) {
			if (in_array ( $key, $_class_vars )) {
				$modelArray [$key] = $value;
			}
		}
		return $modelArray;
	}
	
	
        public function hasVariable($variableName) {
		$_class_vars = $this->_getClassVars ();
		if (in_array ( $variableName, $_class_vars )) {
			return true;
		}
		return false;
	}
	
        public function getServiceLocator() {
		if ($this->_serviceLocator == null) {
			$this->_serviceLocator = \Application\Options\Option::getServiceLocator ();
		}
		return $this->_serviceLocator;
	}
	
        public function getEntityManager() {
		if ($this->_em == null) {
			$this->_em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );
		}
		return $this->_em;
	}
	
        protected function getClassMetaData() {
		if ($this->_classMetaData == null)
			$this->_classMetaData = \Application\Options\Option::getClassMetaData ( get_class ( $this ) );
		return $this->_classMetaData;
	}
	
	
        private function isCurrentNamespace() {
		return (strpos ( get_class ( $this ), __NAMESPACE__ ) !== false);
	}
	
	protected function getTableName() {
		return $this->getClassMetaData ()->getTableName ();
	}
	
        protected function getPrimaryKeyColumnName() {
		return $this->getClassMetaData ()->getSingleIdentifierColumnName ();
	}
	
	
        protected function getPrimaryKey() {
		$tablePrimaryKeyColumnName = $this->getClassMetaData ()->getSingleIdentifierFieldName ();
		$primaryKey = ( int ) $this->{$tablePrimaryKeyColumnName};
		return $primaryKey;
	}
	
	protected function getCurrentUser() {
		return \Application\Functions\CustomConstantsFunction::getCurrentUser();
	}
	
	
    public function getGridData (\Zend\Http\PhpEnvironment\Request $request, array $options = array(), $where = null, \Doctrine\ORM\QueryBuilder $select = null)
    {
        $em = $this->getEntityManager();
        
        $gridInitialData = $this->getIntialGridConditions($request, $options, $where, $select);
        
        
        $where = $gridInitialData["where"];
        
        $count = (int) $gridInitialData["count"];
        
        $offset = (int) $gridInitialData["offset"];
        
        $order = $gridInitialData["order"];
        
        $total = 0;
        $totalFiltered = 0;
        if ($select === null) {
            
            $qb = $em->createQueryBuilder(get_class($this));
            $connection = $em->getConnection();
            
        
            $qb->from($this->getTableName(), "");
            $qb->select("count(".$this->getPrimaryKeyColumnName().") as total");
            $resultArray = $connection->executeQuery($qb->getDQL())
            ->fetchAll();
            
            $total = $resultArray[0]["total"];
            
            $qb->where($where);
            
            $resultArray = $connection->executeQuery($qb->getDQL())
            ->fetchAll();
            
            $totalFiltered = $resultArray[0]["total"];
            
            $qb->select("*");
            
            $orders = explode(",", $order);
            foreach ($orders as $eachOrder) {
                $eachOrder = trim($eachOrder);
                if ($eachOrder != null) {
                    list ($orderColumn, $orderType) = explode(" ", $eachOrder);
                    $qb->addOrderBy($orderColumn, $orderType);
                }
            }
            
            $qb->setMaxResults($count);
            
            $qb->setFirstResult($offset);

            
            $sql = $qb->getDQL()." LIMIT ".$offset.",".$count;
			
            $resultArray = $connection->executeQuery($sql)
                ->fetchAll();
        
        }else{
            $connection = $em->getConnection();

            $temp = clone $select;
            $temp->select("count(".$temp->getRootAlias().".".$this->getPrimaryKeyColumnName().") as total");
            
            $resultArray = $connection->executeQuery($temp->getDQL())->fetchAll();
            
            $total = $resultArray[0]["total"];

            $temp->where($where);$select->where($where);

            $resultArray = $connection->executeQuery($temp->getDQL())
                ->fetchAll();

            $totalFiltered = isset($resultArray[0]) && isset($resultArray[0]["total"]) ? $resultArray[0]["total"] : 0;

            $orders = explode(",", $order);
            foreach ($orders as $eachOrder) {
                $eachOrder = trim($eachOrder);
                if ($eachOrder != null) {
                    list ($orderColumn, $orderType) = explode(" ", $eachOrder);
                    $select->addOrderBy($orderColumn, $orderType);
                }
            }
        
            $select->setFirstResult($offset);

            
            $select->setMaxResults($count);
            
            $sql = $select->getDQL()." LIMIT ".$offset.",".$count;
            
            $resultArray = $connection->executeQuery($sql )
                ->fetchAll();
        }
        
        $gridData = $this->filterGridResult($options,$resultArray);
        $finalGridData["sEcho"] = $request->getPost("sEcho", 1);
        $finalGridData["iTotalRecords"] = $total;
        $finalGridData["iTotalDisplayRecords"] = $totalFiltered;
        $finalGridData["aaData"] = $gridData;
        
        return $finalGridData;
    }

    public function getIntialGridConditions (\Zend\Http\PhpEnvironment\Request $request, array $options = array(), $where = null, \Doctrine\ORM\QueryBuilder $select = null)
    {
        $em = $this->getEntityManager();
        
        $originalColumns = $request->getPost('sColumns', "*");
        $originalColumns = explode(",", $originalColumns);
        $this->_gridOriginalColumns = $originalColumns;
        
        $idColumns = isset($options["column"]) && isset($options["column"]["id"]) ? $options["column"]["id"] : array();
        
        $columns = array_filter($originalColumns, function  ($value) use( $idColumns)
        {
            return ($value != "" && ! in_array($value, $idColumns));
        });
        
        $order = "";
        
        $iSortingCols = $request->getPost('iSortingCols');
        
        for ($i = 0; $i < intval($iSortingCols); $i ++) {
            if ($request->getPost("bSortable_" . $request->getPost('iSortCol_' . $i), false)) {
                $order .= $columns[$request->getPost('iSortCol_' . $i)] . " " . $request->getPost('sSortDir_' . $i) . ", ";
            }
        }
        $order = $order == "" ? null : $order;
        
        $allParams = $request->getPost()->toArray();
        $searchParams = array_filter($allParams, function  ($key) use( &$allParams)
        {
            if (strpos(key($allParams), "search_") !== false && $allParams[key($allParams)] != "") {
                next($allParams);
                return true;
            } else {
                next($allParams);
                return false;
            }
        });
        
        $gridSearchParams = array();
        if(isset($options ["column"] ["ignore_search"])) {
        	foreach($options ["column"] ["ignore_search"] as $searchKey) {
        		if(isset($searchParams["search_".$searchKey])){
	        		$gridSearchParams[$searchKey] = $searchParams["search_".$searchKey];
	        		$searchParams["search_".$searchKey] = "";
	        		unset($searchParams["search_".$searchKey]);
        		}
        	}
        }
        

        if(isset($options ["column"] ["allow_search"])) {
            foreach($options ["column"] ["allow_search"] as $searchKey) {
                if(!isset($searchParams["search_".$searchKey])){
                    $searchParams["search_".$searchKey] = "";
                    unset($searchParams["search_".$searchKey]);
                }
            }
        }
        

        $replaceColumns = false;
        if (isset($options["column"]) && isset($options["column"]["replace"])) {
            $replaceColumns = array_keys($options["column"]["replace"]);
        }
        $this->_gridReplaceColumns = $replaceColumns;
        
        $searchTypeColumns = false;
        if (isset($options["search_type"])) {
            $searchTypeColumns = array_keys($options["search_type"]);
        }
        
        if (! empty($searchParams)) {
            if ($where == "") {
                $where .= " (";
            } else {
                $where .= " AND (";
            }
            foreach ($searchParams as $searchColumn => $searchValue) {
                if (is_array($searchValue)) {
                    foreach ($searchValue as $key => $value) {
                        $searchParams[$searchColumn . "." . $key] = $value;
                    }
                    unset($searchParams[$searchColumn]);
                }
            }
            
            foreach ($searchParams as $searchColumn => $searchValue) {
                $searchColumn = substr($searchColumn, strlen("search_"));
                
                if ($replaceColumns && in_array($searchColumn, $replaceColumns)) {
                    $filterReplaceColumns = $options['column']['replace'][$searchColumn];
                    $searchArray = array_filter($filterReplaceColumns, function  ($data) use( &$filterReplaceColumns, $searchValue)
                    {
                        if (strpos(strtolower(current($filterReplaceColumns)), strtolower($searchValue)) !== false) {
                            next($filterReplaceColumns);
                            return true;
                        }
                        next($filterReplaceColumns);
                        return false;
                    });
                    if (! empty($searchArray)) {
                        $where .= "( ( ";
                        foreach ($searchArray as $key => $value) {
                            if (is_array($searchTypeColumns) && in_array($searchColumn, $searchTypeColumns)) {
                                if ($options['search_type'][$searchColumn] == "=") {
                                    $where .= $searchColumn . " = '" . $searchValue . "' AND ";
                                } else 
                                    if ($options['search_type'][$searchColumn] == "LIKE") {
                                        $where .= $searchColumn . " LIKE '%" . $searchValue . "%' AND ";
                                    }
                            } else {
                                $where .= $searchColumn . " LIKE '%" . $searchValue . "%' AND ";
                            }
                        }
                        $where = substr_replace($where, "", - 4);
                        $where .= " ) ) AND ";
                    } else {
                        $where .= $searchColumn . " LIKE '%" . $searchValue . "%' AND ";
                    }
                } else {
                    if (is_array($searchTypeColumns) && in_array($searchColumn, $searchTypeColumns)) {
                        if ($options['search_type'][$searchColumn] == "=") {
                            $where .= $searchColumn . " = '" . $searchValue . "' AND ";
                        } else 
                            if ($options['search_type'][$searchColumn] == "LIKE") {
                                $where .= $searchColumn . " LIKE '%" . $searchValue . "%' AND ";
                            }
                    } else {
                        $where .= $searchColumn . " LIKE '%" . $searchValue . "%' AND ";
                    }
                }
            }
            $where = substr_replace($where, "", - 4);
            $where .= ") ";
        }
        $where = $where == "" ? "1=1" : $where;
        
        $count = $request->getPost("iDisplayLength", 10);
        
        $offset = $request->getPost("iDisplayStart", 0);
      
        return array(
            "where" => $where,
            "count" => $count,
            "offset" => $offset,
            "order" => $order
        );
    }

    public function filterGridResult (array $options = array(), array $resultArray = array())
    {
        $originalColumns = $this->_gridOriginalColumns;
        
        $replaceColumns = $this->_gridReplaceColumns;
       
        $gridData = array();
        if ($resultArray) {
            foreach ($resultArray as $result) {
            	$record = array();
            	if(isset($options["column"]["ignore_select"])){
            		foreach ($options["column"]["ignore_select"] as $is){
            			unset($result[$is]);
            		}
            	}
                foreach ($originalColumns as $column) {
            		if(isset($options["column"]["ignore_select"]) && in_array($column, $options["column"]["ignore_select"])){
            			continue;
            		}
                    if (isset($options["column"]) && isset($options["column"]["id"]) && in_array($column, $options["column"]["id"])) {
                        $record[] = $result;
                    } else 
                        if (isset($options["column"]) && isset($options["column"]["ignore"]) && in_array($column, $options["column"]["ignore"])) {
                            $record[] = "";
                        } else {
                            $columnValue = $result[$column];
                            
                            if ($replaceColumns && in_array($column, $replaceColumns) && isset($options["column"]["replace"][$column][$columnValue])) {
                                $record[] = $options["column"]["replace"][$column][$columnValue];
                            } else {
                                $record[] = $columnValue;
                            }
                        }
                }
                $gridData[] = $record;
            }
        }
        return $gridData;
    }
}