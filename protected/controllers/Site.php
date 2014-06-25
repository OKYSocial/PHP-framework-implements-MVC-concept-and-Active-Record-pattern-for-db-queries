<?php
	/**
	* 
	*/
	class Site extends Controller
	{

		public function pageAction()
		{	
			//ПРИ ВЫБОРКЕ ВСЕГДА ВОЗВРАЩАТЬ КЛЮЧЕВОЕ ПОЛЕ ТАБЛИЦЫ +
			//РАЗОБРАТЬСЯ С СОСТАВНЫМИ КЛЮЧЕВЫМИ ПОЛЯМИ

			$model = new Articles;
			$criteria = new Criteria($model);

			$condition = array();
			if (!empty($_GET['Search']['auto']) && intval($_GET['Search']['auto'])) {
				$condition['fk_auto'] = array(intval($_GET['Search']['auto']));
			}
			if (!empty($_GET['Search']['group']) && intval($_GET['Search']['group'])) {
				$condition['fk_group'] = array(intval($_GET['Search']['group']));
			}
			if (!empty($_GET['Search']['number']) && strlen($_GET['Search']['number'])) {
				$condition['number'] = array(addslashes($_GET['Search']['number']));
			}
			$criteria->condition($condition);

			//Пагинация
			$count = $model->count($criteria);
			$pages = new Pagination($count[0]['countNum'], $_GET);
			$pages->applyLimit($criteria);
			///////////

			$data = $model->findByCriteria($criteria);

				echo "<form id='advanced' action='/site/page' method='GET'>
    				<table id='search_form'>
		                <tr>
		                    <td>
		                        Auto<br>
		                        <select name='Search[auto]'>
		                            <option>--Выберите auto--</option>";
			                        $autos = Autos::getAll();
			                        foreach ($autos as $auto) {
			                            echo "<option value='".$auto->id."' ".(isset($_GET['Search']['auto']) && $_GET['Search']['auto']==$auto->id ? "selected" : null).">".$auto->auto."</option>";
			                        }
		                        echo "</select>
		                    </td>
		                    <td>
		                        Group<br>
		                        <select name='Search[group]'>
		                            <option>--Выберите group--</option>";
			                        $groups = Groups::getAll();
			                        foreach ($groups as $group) {
			                            echo "<option value='".$group->id."' ".(isset($_GET['Search']['group']) && $_GET['Search']['group']==$group->id ? "selected" : null).">".$group->group."</option>";
			                        }
		                        echo "</select>
		                    </td>
			                <td>
			                	Number<br>
			                	<input type='text' name='Search[number]' value='".$_GET['Search']['number']."'>
			                </td>
		    			</tr> 			
		                <tr>
		                    <td colspan='3'><input type='submit' name='Search[submit]' class='submit' value='поиск'></td>
		                </tr>
		    		</table>
    			</form>";

    		if (!empty($data)) {
				echo "<table class='techtab' cellpadding='1' cellspacing='0' border='1'>";
					echo "<tr>";
						echo "<td class='head'>group</td>";
						echo "<td class='head'>auto</td>";
						echo "<td class='head'>brand</td>";
						echo "<td class='head'>number</td>";
						echo "<td class='head'>original</td>";
						echo "<td class='head'>description</td>";
						echo "<td class='head'>Аналоги</td>";
					echo "</tr>";
				$cnt=1;
				foreach ($data as $article) {
					echo "<tr ".($cnt%2==0 ? "class='even'" : "class='odd'").">";
							echo "<td>";
								foreach ($article->groups as $group) {
									echo $group->group;
								}
							echo "</td>";
							echo "<td>";
								foreach ($article->autos as $auto) {
									echo $auto->auto;
								}
							echo "</td>";
							echo "<td>";
								foreach ($article->brands as $brand) {
									echo $brand->brand;
								}
							echo "</td>";
							echo "<td>".$article->number."</td>";
							echo "<td>";
								foreach ($article->originals as $original) {
									echo $original->original;
								}
							echo "</td>";
							echo "<td>".$article->description."</td>";
							echo "<td>";
								foreach ($article->originals as $original) {
									$articles = new Articles;
									$criteria = new Criteria($articles);
									$criteria->condition(array('fk_original'=>array($original->id)));
									$analogs = $articles->findByCriteria($criteria);
									if (!empty($analogs)) {
										foreach ($analogs as $analog) {
											if ($analog->number!=$article->number) {
												echo $analog->number."<br>";
											}
										}
									}
								}
							echo "</td>";
					echo "</tr>";
					$cnt++;
				}
				echo "</table>";

				//Пагинатор (отображение)
				if (!empty($pages) && is_object($pages)) {
		            if ($pages->pageCount>1) {
		                $url = preg_replace('[.page=\d+]', '', $_SERVER['REQUEST_URI']);
		                if (strpos($url, '?')) {
		                    $ref = $url."&";
		                }
		                else{
		                    $ref = $url."?";
		                }

		                $current = $pages->curPage;
		                $prev = ($current-1>=0 ? $current-1 : null);
		                if ($current==0) {
		                    $next = ($current+2<=$pages->pageCount ? $current+2 : null);
		                }
		                else{
		                    $next = ($current+1<=$pages->pageCount ? $current+1 : null);
		                }
		                
		                $start = ($current-1>0 ? $current-1 : 1);
		                if ($current==0) {
		                    $finish = ($current+2<$pages->pageCount ? $current+2 : $pages->pageCount);
		                }
		                else{
		                   $finish = ($current+1<$pages->pageCount ? $current+1 : $pages->pageCount); 
		                }

		                echo "<ul id='pager'>";
		                    if (!empty($prev)) {
		                        echo "<li><a href='".$ref."page=".$prev."'>Пред.</a></li>";
		                    }
		                    for ($i=$start; $i<=$finish; $i++) {
		                        echo "<li class='".($current==$i || ($current==0 && $i==1) ? "active" : "unactive")."'><a href='".$ref."page=".$i."'>".$i."</a></li>";
		                    }
		                    if (!empty($next)) {
		                        echo "<li><a href='".$ref."page=".$next."'>След.</a></li>";
		                    }
		                echo "</ul>";
		            }
		        }
				///////////
			}
		}

		public function importAction()
		{
			$data = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/data/auto.xml');

			$errors = array();
			$articles = new SimpleXMLElement($data);

			if (!isset($articles->article)) {
				$errors[] = 'Неверный формат файла!';
			}
			else{
				foreach ($articles as $article) {
					$model = new Articles;
					$model->art = $article->id_article;
					$model->number = $article->number;
					$model->description = $article->descr;
					$model->groups = array('group'=>$article->group);
					$model->autos = array('auto'=>$article->auto);
					$model->brands = array('brand'=>$article->brand);
					$model->originals = array('original'=>$article->original);
					$model->save();
				}
			}
		}

	}
?>