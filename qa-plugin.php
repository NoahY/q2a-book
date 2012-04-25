<?php
        
/*              
        Plugin Name: Book
        Plugin URI: https://github.com/NoahY/q2a-book
        Plugin Update Check URI: https://github.com/NoahY/q2a-book/raw/master/qa-plugin.php
        Plugin Description: Makes boook from top questions and answers
        Plugin Version: 0.7
        Plugin Date: 2012-03-05
        Plugin Author: NoahY
        Plugin Author URI:                              
        Plugin License: GPLv2                           
        Plugin Minimum Question2Answer Version: 1.5
*/                      
                        
                        
        if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
                        header('Location: ../../');
                        exit;   
        }               

        qa_register_plugin_module('module', 'qa-book-admin.php', 'qa_book_admin', 'Book Export');
        
        //qa_register_plugin_layer('qa-book-layer.php', 'Book Layer');

        qa_register_plugin_overrides('qa-book-overrides.php');

		qa_register_plugin_module('widget', 'qa-book-widget.php', 'qa_book_widget', 'Book Widget');
		
		qa_register_plugin_phrases('qa-book-lang-*.php', 'book');


		function qa_book_plugin_createBook($return=false) {

			$book = qa_opt('book_plugin_template');
			
			// static replacements
			
			$book = str_replace('[css]',qa_opt('book_plugin_css'),$book);
			$book = str_replace('[front]',qa_opt('book_plugin_template_front'),$book);
			$book = str_replace('[back]',qa_opt('book_plugin_template_back'),$book);			

			$iscats = qa_opt('book_plugin_cats');

			// categories

			if($iscats) {
			    $cats = qa_db_read_all_assoc(
					qa_db_query_sub(
						'SELECT * FROM ^categories'.(qa_opt('book_plugin_catex')?' WHERE categoryid NOT IN ('.qa_opt('book_plugin_catex').')':'')
					)
				);	
				$navcats = array();
				foreach($cats as $cat)
					$navcats[$cat['categoryid']] = $cat;
			}
			else
			    $cats = array(false);

			// intro
			
			$intro = qa_lang('book/intro');
			
			$intro = str_replace('[sort_questions]',qa_lang('book/'.(qa_opt('book_plugin_sort_q') == 0?'sort_upvotes':'sort_date')),$intro);
			$intro = str_replace('[sort_categories]',$iscats?qa_lang('book/sort_categories'):'',$intro);
			$intro = str_replace('[restrict_questions]',qa_opt('book_plugin_req_qv')?qa_lang_sub('book/restrict_q_x_votes',qa_opt('book_plugin_req_qv_no')):qa_lang('book/all_questions'),$intro);
			
			$rq = array();
			
			if(qa_opt('book_plugin_req_sel'))
				$rq[] = qa_lang('book/restrict_selected');
			if(qa_opt('book_plugin_req_abest'))
				$rq[] = qa_lang('book/restrict_best_a');
			if(qa_opt('book_plugin_req_av'))
				$rq[] = qa_lang_sub('book/restrict_a_x_votes',qa_opt('book_plugin_req_av_no'));

			if(empty($rq))
				$intro = str_replace('[restrict_answers]','',$intro);
			else {
				$rqs = qa_lang('book/restrict_answers_clause_'.count($rq));
				foreach($rq as $i => $v) 
					$rqs = str_replace('('.($i+1).')',$v,$rqs);
				$intro = str_replace('[restrict_answers]',$rqs,$intro);
			}
			
			$book = str_replace('[intro]',$intro,$book);

			    
			$tocout = '';
			$qout = '';
			
			foreach($cats as $cat) {

				$incsql = '';
				$sortsql = '';
				
				$toc = '';
				$qhtml = '';
				
				if(qa_opt('book_plugin_sort_q') == 0)
					$sortsql='ORDER BY qs.netvotes DESC, qs.created ASC';
				else
					$sortsql='ORDER BY qs.created ASC';

				if(qa_opt('book_plugin_req_sel'))
					$incsql .= ' AND qs.selchildid=ans.postid';
					
				if(qa_opt('book_plugin_req_abest'))
					$sortsql.=', ans.netvotes DESC'; // get all, limit later with break
					
				if(qa_opt('book_plugin_req_qv'))
					$incsql .= ' AND qs.netvotes >= '.(int)qa_opt('book_plugin_req_qv_no');

				if(qa_opt('book_plugin_req_av'))
					$incsql .= ' AND ans.netvotes >= '.(int)qa_opt('book_plugin_req_av_no');
				
				$selectspec="SELECT qs.postid AS postid, BINARY qs.title AS title, BINARY qs.content AS content, qs.format AS format, qs.netvotes AS netvotes, BINARY ans.content AS acontent, ans.format AS aformat, ans.userid AS auserid, ans.netvotes AS anetvotes FROM ^posts AS qs, ^posts AS ans WHERE qs.type='Q' AND ans.type='A' AND ans.parentid=qs.postid".($iscats?" AND qs.categoryid=".$cat['categoryid']." ":"").$incsql." ".$sortsql;
				
				$qs = qa_db_read_all_assoc(
					qa_db_query_sub(
						$selectspec
					)
				);	
				
				if(empty($qs)) // no questions in this category
					continue;
				
				$q2 = array();
				foreach($qs as $q) { // group by questions
					$q2['q'.$q['postid']][] = $q;
				}

				
				foreach($q2 as $qs) {
					
					// toc entry
					
					$toc.=str_replace('[qlink]','<a href="#question'.$qs[0]['postid'].'">'.$qs[0]['title'].'</a>',qa_opt('book_plugin_template_toc'));

					// answer html
					
					$as = '';
					$nv = false;
					foreach($qs as $idx => $q) {
						if(qa_opt('book_plugin_req_abest') && qa_opt('book_plugin_req_abest_max') && $idx >= qa_opt('book_plugin_req_abest_max'))
							break;
						if($nv !== false && qa_opt('book_plugin_req_abest') && $nv != $q['anetvotes']) // best answers only
							break;
						$acontent = '';
						if(!empty($q['acontent'])) {
							$viewer=qa_load_viewer($q['acontent'], $q['aformat']);
							$acontent = $viewer->get_html($q['acontent'], $q['aformat'], array());
						}
						
						$a = str_replace('[answer]',$acontent,qa_opt('book_plugin_template_answer'));
						
						$a = str_replace('[answerer]',qa_get_user_name($q['auserid']),$a);

						$as .= $a;
						
						$nv = $q['anetvotes'];
					}
					
					// question html
					
					$qcontent = '';
					if(!empty($q['content'])) {
						$viewer=qa_load_viewer($q['content'], $q['format']);
						$qcontent = $viewer->get_html($q['content'], $q['format'], array());
					}
					
					$oneq = str_replace('[question-title]',$q['title'],qa_opt('book_plugin_template_question'));
					$oneq = str_replace('[qanchor]','question'.$q['postid'],$oneq);
					$oneq = str_replace('[qurl]',qa_html(qa_q_request($q['postid'],$q['title'])),$oneq);
					$oneq = str_replace('[question]',$qcontent,$oneq);
					 // output with answers 
					 
					$qhtml .= str_replace('[answers]',$as,$oneq);
				}
				if($iscats) {
					$tocout .= '<li><a href="#cat'.$cat['categoryid'].'" class="toc-cat">'.$cat['title'].'</a><ul class="toc-ul">'.$toc.'</ul></li>';

					// todo fix category link
					
					$catout = str_replace('[cat-url]',qa_path_html('questions/'.qa_category_path_request($navcats, $cat['categoryid'])),qa_opt('book_plugin_template_category'));
					$catout = str_replace('[cat-anchor]','cat'.$cat['categoryid'],$catout);
					$catout = str_replace('[cat-title]',$cat['title'],$catout);
					$catout = str_replace('[questions]',$qhtml,$catout);
					$qout .= $catout;
				}
				else {
					$tocout .= '<ul class="toc-ul">'.$toc.'</ul>';
					$catout = str_replace('[questions]',$qhtml,qa_opt('book_plugin_template_questions'));
					$qout .= $catout;
				}
			}	
			if($iscats)
				$tocout = '<ul class="toc-ul">'.$tocout.'</ul>';
				
			// add toc and questions
			
			$book = str_replace('[toc]',$tocout,$book);
			$book = str_replace('[categories]',$qout,$book);
			
			// misc subs
			
			$book = str_replace('[site-title]',qa_opt('site_title'),$book);
			$book = str_replace('[site-url]',qa_opt('site_url'),$book);
			$book = str_replace('[date]',date('M j, Y'),$book);
			
			qa_opt('book_plugin_refresh_last',time());
			
			error_log('Q2A Book Created on '.date('M j, Y \a\t H\:i\:s'));
			
			if($return)
				return $book;
			
			file_put_contents(qa_opt('book_plugin_loc'),$book);
			
			if(qa_opt('book_plugin_pdf'))
				qa_book_plugin_create_pdf();


			return 'Book Created';
		    
		    //return 'Error creating '.qa_opt('book_plugin_loc').'; check the error log.';
		}
		
		function qa_book_plugin_create_pdf($return=false) {
				
			include 'wkhtmltopdf.php';

			//echo $html;

			$pdf = new WKPDF();

			$pdf->render_q2a();
			
			if($return)
				$pdf->output(WKPDF::$PDF_DOWNLOAD,'book.pdf'); 
			else
				$pdf->output(WKPDF::$PDF_SAVEFILE,qa_opt('book_plugin_loc_pdf')); 

			error_log('Q2A PDF Book Created on '.date('M j, Y \a\t H\:i\:s'));
		}
		function qa_get_user_name($uid) {

			$handles = qa_userids_to_handles(array($uid));
			$handle = $handles[$uid];

			if(QA_FINAL_EXTERNAL_USERS) {
				$user_info = get_userdata($uid);
				if ($user_info->display_name)
					$name = $user_info->display_name;
			}
			else {
				$name = qa_db_read_one_value(
					qa_db_query_sub(
						'SELECT title AS name FROM ^userprofile '.
						'WHERE userid=# AND title=$',
						$uid, 'name'
					),
					true
				);
			}
			if(!@$name)
				$name = $handle;

			return strlen($handle) ? ('<A HREF="'.qa_path_html('user/'.$handle).
				'" CLASS="qa-user-link">'.qa_html($name).'</A>') : 'Anonymous';
		}

                        
/*                              
        Omit PHP closing tag to help avoid accidental output
*/                              
                          

