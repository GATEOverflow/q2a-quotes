<?php
/*
   Question2Answer by Gideon Greenspan and contributors
   http://www.question2answer.org/

   File: qa-plugin/example-page/qa-example-page.php
   Description: Page module class for example page plugin


   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   More about this license: http://www.question2answer.org/license.php
 */

class qa_quote_page
{
	private $directory;
	private $urltoroot;
	private $new;

	public function load_module($directory, $urltoroot)
	{
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}


	public function suggest_requests() // for display in admin interface
	{
		return array(
				array(
					'title' => qa_lang_html('quote_page/page_title'),
					'request' => 'quote-plugin-page',
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				     ),
			    );
	}

	public function init_queries($tableslc)
	{
		$queries = array();
		$tablename=qa_db_add_table_prefix('quotes');
		$tablenameq=qa_db_add_table_prefix('options');
		if(!in_array($tablename, $tableslc)) {
			$new = true;
			$queries[] = "CREATE TABLE IF NOT EXISTS `".$tablename."` (
				`quoteid` int(10) unsigned auto_increment primary key,
				`quote` varchar(3072),
				`author` varchar(100)
					)
					";
		}
		$events = qa_db_read_one_value(qa_db_query_raw("show events where name like 'quoteevent'"), true);
		if(!$events){

			$queries[] = "CREATE EVENT quoteevent
				ON SCHEDULE EVERY 1 DAY
				DO
				BEGIN
				UPDATE ".$tablenameq." set content = (select quoteid from $tablename order by rand() limit 1) where title like 'quoteod';
			END ";
		}

	}
	public function match_request($request)
	{
		return $request == 'quote-plugin-page';
	}


	public function process_request($request)
	{
		$qa_content=qa_content_prepare();
		$ok = null;
		$qa_content['title']=qa_lang_html('quote_page/page_title');
		if(qa_clicked('okthen'))
		{
			$insert = "insert into ^quotes (quote, author) values ($,$)";
			qa_db_query_sub($insert, qa_post_text('quote'), qa_post_text('author'));
			$ok = "Quote Saved";
		}

		$qa_content['form']=array(
				'tags' => 'method="post" action="'.qa_self_html().'"',

				'style' => 'wide',
				'ok' => ($ok && !isset($error)) ? $ok : null,

				'title' => qa_lang_html('quote_page/form_title'),

				'fields' => array(
					'request' => array(
						'label' => qa_lang_html('quote_page/quote'),
						'tags' => 'name="quote"',
						'type' => 'textarea',
						'rows' => 20,
						'value' => '',
						),
					'author' => array(
						'label' => qa_lang_html('quote_page/author'),
						'tags' => 'name="author"',
						'type' => 'text',
						'value' => '',
						),

					),

				'buttons' => array(
						'ok' => array(
							'tags' => 'name="okthen"',
							'label' => 'Submit',
							'value' => '1',
							),
						),

				'hidden' => array(
						'hiddenfield' => '1',
						),
				);


		return $qa_content;
	}
}
