<?php
        
/*              
        Plugin Name: Book
        Plugin URI: https://github.com/NoahY/q2a-book
        Plugin Update Check URI: https://github.com/NoahY/q2a-book/raw/master/qa-plugin.php
        Plugin Description: Makes boook from top questions and answers
        Plugin Version: 0.1
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
        
        qa_register_plugin_layer('qa-book-layer.php', 'Book Layer');

        if(function_exists('qa_register_plugin_phrases')) {
          //  qa_register_plugin_overrides('qa-book-overrides.php');
          // qa_register_plugin_phrases('qa-book-lang-*.php', 'book');
        }                       

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
						'SELECT * FROM ^categories'
					)
				);	
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
				
				switch (qa_opt('book_plugin_sort')) {
					case 0:
						$sortsql='ORDER BY qs.netvotes DESC, qs.created DESC';
					break;
					
					case 1:
						$sortsql='ORDER BY ans.netvotes DESC, ans.created DESC';
					break;

					case 2:
						$sortsql='ORDER BY qs.created ASC';
					break;
					
				}

				switch (qa_opt('book_plugin_inc')) {
					case 0:
						$incsql='AND qs.selchildid=ans.postid';
					break;
					
					case 1:
						$sortsql.=', ans.netvotes DESC';
						$incsql="AND ans.netvotes IN (SELECT MAX(ans.netvotes) FROM ^posts AS qs, ^posts AS ans WHERE qs.type='Q' AND ans.type='A' AND ans.parentid=qs.postid)";
					break;
					
					case 2:
						$incsql='AND qs.netvotes >= '.(int)qa_opt('book_plugin_include_votes');
					break;
					case 3:
						$incsql='AND ans.netvotes >= '.(int)qa_opt('book_plugin_include_votes');
					break;
				}
				
				$selectspec="SELECT qs.postid AS postid, qs.title AS title, qs.content AS content, ans.content AS acontent FROM ^posts AS qs, ^posts AS ans WHERE qs.type='Q' AND ans.type='A' AND ans.parentid=qs.postid ".($iscats?"AND qs.categoryid=".$cat['categoryid']." ":"").$incsql." ".$sortsql;
				
				$qs = qa_db_read_all_assoc(
					qa_db_query_sub(
						$selectspec
					)
				);	
				
				if(empty($qs)) // no questions in this category
					continue;
				
				$q2 = array();
				foreach($qs as $q) { // dups
					$q2[$q['postid']][] = $q;
				}

				
				foreach($q2 as $qs) {

					// toc entry
					
					$toc.=str_replace('[qlink]','<a href="#question'.$q['postid'].'">'.$q['title'].'</a>',qa_opt('book_plugin_template_toc'));

					// answer html
					
					$as = '';
					foreach($qs as $q) {
						$as .= str_replace('[answer]',$q['acontent'],qa_opt('book_plugin_template_answer'));
					}
					
					// question html
					
					$oneq = str_replace('[question-title]',$q['title'],qa_opt('book_plugin_template_question'));
					$oneq = str_replace('[qanchor]','question'.$q['postid'],$oneq);
					$oneq = str_replace('[qurl]',qa_html(qa_q_request($q['postid'],$q['title'])),$oneq);
					$oneq = str_replace('[question]',$q['content'],$oneq);
					 // output with answers 
					 
					$qhtml .= str_replace('[answers]',$as,$oneq);
				}
				if($iscats) {
					$tocout .= '<li><a href="#cat'.$cat['categoryid'].'">'.$cat['title'].'</a><ul class="toc-ul">'.$toc.'</ul></li>';

					// todo fix category link
					
					$catout = str_replace('[cat-url]',qa_path_html('questions/'.qa_category_path_request($cats, $cat['categoryid'])),qa_opt('book_plugin_template_category'));
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

                        
/*                              
        Omit PHP closing tag to help avoid accidental output
*/                              
                          

