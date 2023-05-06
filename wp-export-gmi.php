<?php
/**
 * WordPress export to gmi
 *
 *
 * @package caiofior
 */

/** Make sure that the WordPress bootstrap has run before continuing. */
require __DIR__ . '/wp-load.php';
$args = array(
'post_type'=> 'post',
'orderby'    => 'ID',
'post_status' => 'publish',
'order'    => 'ASC',
'posts_per_page' => -1 // this will retrive all the post that is published 
);
$result = new WP_Query( $args );
$dir = __DIR__.'/gmi';
if (!is_dir($dir)) {
    mkdir($dir);
}
$newUrl = '.';
$posts=array();
$breaks = array('<br />','<br>','<br/>');
$siteUrl = 'http://nemus.florae.it/';
if ( $result-> have_posts() )  {
   while ( $result->have_posts() ) { 
      $result->the_post();
      $posts[get_the_ID()]=array(
          'title'=>get_the_title()
      );
      echo  get_the_ID()."\t".get_the_title().PHP_EOL;
      $content = '# '.get_the_title()."\r\n";
      $postContent = get_the_content();
      if(preg_match('/(<img[^>]+>)/i', $postContent, $imgs)) {
          foreach ($imgs as $img) {
                if(preg_match('/src="([^"]*)"/',$img,$urls)) {
                    $url= $urls[1];
                    $orFile = __DIR__.'/'.str_replace($siteUrl, '', $url);
                    $destDir = $dir;
                    $dirs = explode('/',str_replace($siteUrl, '', $url));
                    $destFileName = array_pop($dirs);
                    $relUrl = '';
                    foreach($dirs as $dirName) {
                        $destDir .= '/'.$dirName;
                        if ($relUrl != '') {
                            $relUrl .= '/';
                        }
                        $relUrl .= $dirName;
                        if (!is_dir($destDir)) {
                            mkdir($destDir);
                        }
                    }
                    copy($orFile, $destDir.'/'.$destFileName);
                    $postContent = str_replace($img, "\r\n=> ".$relUrl.'/'.$destFileName, $postContent."\r\n");
                }
          }
      }
      $postContent = str_ireplace($breaks, "\r\n",$postContent);
      $postContent = strip_tags($postContent);
      $postContent = rtrim($postContent);
      $postContent = preg_replace( "/[\r\n]{2}/", "\r\n",$postContent);
      $content .= $postContent;
      file_put_contents($dir.'/'.get_the_ID().'_'.get_the_title().'.gmi',$content );
   }
}
