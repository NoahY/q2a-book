<?php
        
/*              
        Plugin Name: Book
        Plugin URI: https://github.com/NoahY/q2a-book
        Plugin Update Check URI: https://github.com/NoahY/q2a-book/raw/master/qa-plugin.php
        Plugin Description: Makes boook from top questions and answers
        Plugin Version: 0.3
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

		function qa_book_plugin_createBook($return=false) {

			$book = qa_opt('book_plugin_template');
			
			// static replacements
			
			$book = str_replace('[css]',qa_opt('book_plugin_css'),$book);
			$book = str_replace('[front]',qa_opt('book_plugin_template_front'),$book);
			$book = str_replace('[back]',qa_opt('book_plugin_template_back'),$book);			
			
			// categories

			$iscats = qa_opt('book_plugin_cats');
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
			    
			$tocout = '';
			$qout = '';
			
			foreach($cats as $cat) {

				$incsql = '';
				$sortsql = '';
				
				$toc = '';
				$qhtml = '';
				
				if(qa_opt('book_plugin_sort_q') == 1)
					$sortsql='ORDER BY qs.netvotes DESC, qs.created DESC';
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
				
				$selectspec="SELECT qs.postid AS postid, BINARY qs.title AS title, BINARY qs.content AS content, qs.format AS format, BINARY ans.content AS acontent, ans.format AS aformat, ans.userid AS auserid FROM ^posts AS qs, ^posts AS ans WHERE qs.type='Q' AND ans.type='A' AND ans.parentid=qs.postid".($iscats?" AND qs.categoryid=".$cat['categoryid']." ":"").$incsql." ".$sortsql;
				
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
					foreach($qs as $q) {
						$acontent = '';
						if(!empty($q['acontent'])) {
							$viewer=qa_load_viewer($q['acontent'], $q['aformat']);
							$acontent = $viewer->get_html($q['acontent'], $q['aformat'], array());
						}
						
						$a = str_replace('[answer]',$acontent,qa_opt('book_plugin_template_answer'));
						
						$a = str_replace('[answerer]',qa_get_user_name($q['auserid']),$a);

						$as .= $a;
						
						if(qa_opt('book_plugin_req_abest')) // best answer only
							break;
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
			
			
			if($return)
				return $book;
			
		    if(file_put_contents(qa_opt('book_plugin_loc'),$book))
				return 'Book Created';
		    
		    return 'Error creating '.qa_opt('book_plugin_loc').'; check the error log.';
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
                          

