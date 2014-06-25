<?php

	class Pagination
	{

		protected $count;
		protected $pages;
		protected $offset=0;
		protected $data;
		protected $curPage=0;

		public $query;
		public $pageSize;

		public function __construct($count, $get_arr=null, $pageSize=null, $isPaging=true)
		{
			if (!$isPaging) return false;
			
			$this->count = intval($count);

			if (!empty($pageSize) && $isPaging) {
				$this->pageSize = intval($pageSize);
			}
			else{
				$this->pageSize = 10;
			}

			if (isset($get_arr['page'])) {
				$this->curPage = intval($get_arr['page']);
			}

			if ($isPaging) { //Если пагинация включена - оффсет считается
				$this->offset = (intval($this->curPage)-1)*$this->pageSize;
			}
			if ($this->offset<0 || !$isPaging) { //Если оффсет получился отрицательным или пагинация не включена - оффсет = 0
				$this->offset = 0;
			}
				
			$this->pages = ceil($this->count/$this->pageSize);
		}

		public function __get($name)
		{
			if ($name=='pageCount') {
				return $this->pages;
			}
			if ($name=='curPage') {
				return $this->curPage;
			}
		}

		public function applyLimit($query)
		{
			if (is_object($query)) {
				if ($this->pages>1) {
					$this->query = $query;
					$this->query->limit = $this->pageSize;
					$this->query->offset = $this->offset;
				}
				else{
					$this->query = $query; //НУЖНО ЛИ?
				}
			}
			else{
				if ($this->pages>1) {
					$this->query = $query." LIMIT ".$this->offset.",".$this->pageSize."";
				}
				else{
					$this->query = $query;
				}
			}
		}

	}