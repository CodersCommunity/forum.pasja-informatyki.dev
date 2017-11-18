<?php

class tips_widget_page
{
	function match_request($request)
	{
		if ($request == 'tips')
			return true;
		
		return false;
	}
	
	function suggest_requests()
	{
		return [['title'   => 'Porady nie od parady',
		          'request' => 'tips',
		          'nav'     => null]];
	}
	
	function process_request($request)
	{
		if (!qa_opt('tips-enable'))
		{
			return include QA_INCLUDE_DIR.'qa-page-not-found.php';
		}
		
		$qa_content = qa_content_prepare();
		
		$qa_content['title'] = 'Porady nie od parady';
		
		$tips_list = explode('!NEW!', qa_opt('tips-widget-content'));
		$tips_list_content = "\t<ul>\n";
		foreach ($tips_list as $element)
		{
			$tips_list_content .= "\t\t<li>".$element."</li>\n";
		}
		$tips_list_content .= "\t</ul>";
		
		$qa_content['custom'] = str_replace('!TIPS!', $tips_list_content, qa_opt('tips-page-content'));
		
		return $qa_content;
	}
}
