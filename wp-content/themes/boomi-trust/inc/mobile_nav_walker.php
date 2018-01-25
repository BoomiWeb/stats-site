<?php
/**
 * boomi_salesMobileNavWalker class.
 *
 * @extends Walker_Nav_Menu
 */
class boomi_salesMobileNavWalker extends Walker_Nav_Menu {

	private $curItem;
	private $start_lvl_counter=0;

	// Displays start of a level. E.g '<ul>'
	// @see Walker::start_lvl()
	function start_lvl(&$output, $depth=0, $args=array()) {
		$this->start_lvl_counter++;
		$output .= "\n<div id=\"collapse".$this->curItem->ID."\" class=\"panel-collapse collapse\" role=\"tabpanel\" aria-labelledby=\"heading".$this->curItem->ID."\">\n";
		$output .= "\n<div class=\"panel-body\">\n";
		$output .= "\n<div class=\"panel-group\" id=\"accordian".$this->start_lvl_counter."\">\n";
	}

	// Displays end of a level. E.g '</ul>'
	// @see Walker::end_lvl()
	function end_lvl(&$output, $depth=0, $args=array()) {
		$output .= "</div><!-- .panel-group -->\n";
		$output .= "</div><!-- .panel-body -->\n";
		$output.="</div><!-- .panel-collapse -->\n";

		if ($depth==0)
			$output.="</div><!-- .panel -->\n";
	}

	// Displays start of an element. E.g '<li> Item Name'
	// @see Walker::start_el()
	function start_el(&$output, $item, $depth=0, $args=array(), $id=0) {
	$link=null;
		$this->curItem=$item;

		if ($depth!=0) :

			if ($args->walker->has_children) :
				$it_class='';
				$it_data_toggle='';
				$it_data_parent='';
				$it_href=$item->url;
				$it_aria_expanded='';

				if ($item->url=='' || $item->url=='#') :
					$it_class='collapsed';
					$it_data_toggle='collapse';
					$it_data_parent='#accordion';
					$it_href='#collapse'.$item->ID;
					$it_aria_expanded='false';
				endif;

				$link.='<div class="item-title">';
					$link.='<a class="'.$it_class.'" data-toggle="'.$it_data_toggle.'" data-parent="'.$it_data_parent.'" href="'.$it_href.'" aria-expanded="'.$it_aria_expanded.'">'.esc_attr($item->title).'</a>';
				$link.='</div>';
				$link.='<div class="item-icon">';
					$link.='<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$item->ID.'" aria-expanded="false" aria-controls="collapse'.$item->ID.'">';
						$link.='<i class="glyphicon glyphicon-plus"></i>';
					$link.='</a>';
				$link.='</div>';
			else :
				$link.='<a class="item-title" data-toggle="" data-parent="" href="'.$item->url.'" aria-expanded="">';
					$link.='<span>'.esc_attr($item->title).'</span>';
				$link.='</a>';
			endif;

/*
			if ($args->walker->has_children) :
				$link='<a class="collapsed" data-toggle="collapse" data-parent="#accordion'.$this->start_lvl_counter.'" data-href="'.$item->url.'" href="#collapse'.$item->ID.'" aria-expanded="false" aria-controls="collapse'.$item->ID.'">';
			else :
				$link='<a class="" data-toggle="" data-parent="" href="'.$item->url.'" aria-expanded="">';
			endif;
*/

			$output.='<div class="panel">';
				$output.='<div class="panel-heading">';
					$output.='<h4 class="panel-title">';
						$output.=$link;
/*
							$output.='<span class="item-title">'.esc_attr($item->title).'</span>';

							if ($args->walker->has_children)
								$output.='<i class="glyphicon glyphicon-plus"></i>';

						$output.='</a>';
*/
					$output.='</h4>';
				$output.='</div><!-- .panel-heading -->';
			$output.='</div><!-- .panel -->';

		else :

			if ($args->walker->has_children) :
				$it_class='';
				$it_data_toggle='';
				$it_data_parent='';
				$it_href=$item->url;
				$it_aria_expanded='';

				if ($item->url=='' || $item->url=='#') :
					$it_class='collapsed';
					$it_data_toggle='collapse';
					$it_data_parent='#accordion';
					$it_href='#collapse'.$item->ID;
					$it_aria_expanded='false';
				endif;

				$link.='<div class="item-title">';
					$link.='<a class="'.$it_class.'" data-toggle="'.$it_data_toggle.'" data-parent="'.$it_data_parent.'" href="'.$it_href.'" aria-expanded="'.$it_aria_expanded.'">'.esc_attr($item->title).'</a>';
				$link.='</div>';
				$link.='<div class="item-icon">';
					$link.='<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$item->ID.'" aria-expanded="false" aria-controls="collapse'.$item->ID.'">';
						$link.='<i class="glyphicon glyphicon-plus"></i>';
					$link.='</a>';
				$link.='</div>';
			else :
				$link.='<a class="item-title" data-toggle="" data-parent="" href="'.$item->url.'" aria-expanded="">';
					$link.='<span>'.esc_attr($item->title).'</span>';
				$link.='</a>';
			endif;

			$item_classes=implode(' ',$item->classes);

			$output.='<div class="panel panel-default">';
				$output.='<div class="panel-heading '.$item_classes.'" role="tab">';
					$output.='<h4 class="panel-title">';
						$output.=$link;
					$output.='</h4>';
				$output.='</div><!-- .panel-heading -->';

			if (!$args->walker->has_children)
				$output.='</div><!-- .panel -->';

		endif;
	}

	// Displays end of an element. E.g '</li>'
	// @see Walker::end_el()
	function end_el(&$output, $item, $depth=0, $args=array()) {
		//$output .= "</div><!-- end_el -->\n";
	}

}
?>